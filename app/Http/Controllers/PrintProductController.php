<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintConfiguration;
use App\Models\User;
use App\Notifications\NewOrderValidated;
use App\Enums\PrintConfigurationStatus;

class PrintProductController extends Controller
{
    // Prix de base par page
    private const PRICE_PER_PAGE = [
        'noir_blanc' => 0.10,
        'couleur' => 0.50,
    ];

    // Prix des reliures
    private const BINDING_PRICES = [
        'agrafage' => 2.00,
        'spirale' => 5.00,
        'dos_colle' => 7.00,
        'sans_reliure' => 0.00,
    ];

    // Prix des types de dépôt
    private const DELIVERY_PRICES = [
        'retrait_magasin' => 0.00,
        'livraison_standard' => 5.00,
        'livraison_express' => 15.00,
    ];

    // Prix des types de papier
    private const PAPER_PRICES = [
        'standard' => 0.00,
        'recycle' => 0.02,
        'premium' => 0.05,
        'photo' => 0.10,
    ];

    // Prix des formats
    private const FORMAT_PRICES = [
        'A4' => 0.00,
        'A3' => 0.50,
        'A5' => -0.05,
    ];

    // Seuils de réduction
    private const QUANTITY_DISCOUNTS = [
        100 => 0.05,  // 5% de réduction à partir de 100 pages
        500 => 0.10,  // 10% de réduction à partir de 500 pages
        1000 => 0.15, // 15% de réduction à partir de 1000 pages
    ];

    public function configure(Request $request)
    {
        $configuration = null;
        if ($request->has('config')) {
            $configuration = PrintConfiguration::findOrFail($request->config);
            // Vérifier que l'utilisateur est propriétaire de la configuration
            if ($configuration->user_id !== auth()->id()) {
                abort(403);
            }
        }

        return view('products.configure', [
            'paperTypes' => array_keys(self::PAPER_PRICES),
            'formats' => array_keys(self::FORMAT_PRICES),
            'configuration' => $configuration
        ]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'pages' => 'required|integer|min:1',
            'print_type' => 'required|in:noir_blanc,couleur',
            'binding_type' => 'required|in:agrafage,spirale,dos_colle,sans_reliure',
            'delivery_type' => 'required|in:retrait_magasin,livraison_standard,livraison_express',
            'paper_type' => 'required|in:standard,recycle,premium,photo',
            'format' => 'required|in:A4,A3,A5',
        ]);

        // Calcul du prix de base par page
        $basePagePrice = self::PRICE_PER_PAGE[$request->print_type];
        
        // Ajout du prix du papier spécial
        $basePagePrice += self::PAPER_PRICES[$request->paper_type];
        
        // Ajout du prix du format
        $basePagePrice += self::FORMAT_PRICES[$request->format];

        // Calcul du prix total des pages
        $pagePrice = $basePagePrice * $request->pages;

        // Application des réductions par quantité
        $discount = 0;
        foreach (self::QUANTITY_DISCOUNTS as $threshold => $discountRate) {
            if ($request->pages >= $threshold) {
                $discount = $discountRate;
            }
        }

        // Prix après réduction
        $pagePrice = $pagePrice * (1 - $discount);

        // Ajout du prix de la reliure
        $bindingPrice = self::BINDING_PRICES[$request->binding_type];

        // Ajout du prix de la livraison
        $deliveryPrice = self::DELIVERY_PRICES[$request->delivery_type];

        // Prix total
        $totalPrice = $pagePrice + $bindingPrice + $deliveryPrice;

        return response()->json([
            'price' => number_format($totalPrice, 2),
            'details' => [
                'pages' => number_format($pagePrice, 2),
                'binding' => number_format($bindingPrice, 2),
                'delivery' => number_format($deliveryPrice, 2),
                'discount_applied' => $discount * 100 . '%',
                'price_per_page' => number_format($basePagePrice, 2),
            ]
        ]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'pages' => 'required|integer|min:1',
            'print_type' => 'required|in:noir_blanc,couleur',
            'binding_type' => 'required|in:agrafage,spirale,dos_colle,sans_reliure',
            'delivery_type' => 'required|in:retrait_magasin,livraison_standard,livraison_express',
            'paper_type' => 'required|in:standard,recycle,premium,photo',
            'format' => 'required|in:A4,A3,A5',
        ]);

        // TODO: Sauvegarder dans la session ou la base de données
        session()->push('cart', [
            'pages' => $request->pages,
            'print_type' => $request->print_type,
            'binding_type' => $request->binding_type,
            'delivery_type' => $request->delivery_type,
            'paper_type' => $request->paper_type,
            'format' => $request->format,
            'configuration_name' => $request->configuration_name,
        ]);

        return redirect()->back()->with('success', 'Produit ajouté au panier avec succès');
    }

    public function saveConfiguration(Request $request)
    {
        $request->validate([
            'configuration_name' => 'required|string|max:255',
            'pages' => 'required|integer|min:1',
            'print_type' => 'required|in:noir_blanc,couleur',
            'binding_type' => 'required|in:agrafage,spirale,dos_colle,sans_reliure',
            'delivery_type' => 'required|in:retrait_magasin,livraison_standard,livraison_express',
            'paper_type' => 'required|in:standard,recycle,premium,photo',
            'format' => 'required|in:A4,A3,A5',
        ]);

        // Calculer le prix total
        $basePagePrice = self::PRICE_PER_PAGE[$request->print_type];
        $basePagePrice += self::PAPER_PRICES[$request->paper_type];
        $basePagePrice += self::FORMAT_PRICES[$request->format];
        
        $pagePrice = $basePagePrice * $request->pages;
        
        // Application des réductions
        $discount = 0;
        foreach (self::QUANTITY_DISCOUNTS as $threshold => $discountRate) {
            if ($request->pages >= $threshold) {
                $discount = $discountRate;
            }
        }
        
        $pagePrice = $pagePrice * (1 - $discount);
        $bindingPrice = self::BINDING_PRICES[$request->binding_type];
        $deliveryPrice = self::DELIVERY_PRICES[$request->delivery_type];
        $totalPrice = $pagePrice + $bindingPrice + $deliveryPrice;

        // Vérifier si une configuration avec ce nom existe déjà pour cet utilisateur
        $existingConfig = PrintConfiguration::where('user_id', auth()->id())
            ->where('name', $request->configuration_name)
            ->first();

        if ($existingConfig) {
            // Mettre à jour la configuration existante
            $existingConfig->update([
                'pages' => $request->pages,
                'print_type' => $request->print_type,
                'binding_type' => $request->binding_type,
                'delivery_type' => $request->delivery_type,
                'paper_type' => $request->paper_type,
                'format' => $request->format,
                'total_price' => $totalPrice
            ]);

            $configuration = $existingConfig;
        } else {
            // Créer une nouvelle configuration
            $configuration = PrintConfiguration::create([
                'user_id' => auth()->id(),
                'name' => $request->configuration_name,
                'pages' => $request->pages,
                'print_type' => $request->print_type,
                'binding_type' => $request->binding_type,
                'delivery_type' => $request->delivery_type,
                'paper_type' => $request->paper_type,
                'format' => $request->format,
                'total_price' => $totalPrice
            ]);
        }

        return response()->json([
            'message' => $existingConfig ? 'Configuration mise à jour avec succès' : 'Configuration sauvegardée avec succès',
            'configuration' => $configuration
        ]);
    }

    public function deleteConfiguration(PrintConfiguration $configuration)
    {
        // Vérifier que l'utilisateur est propriétaire de la configuration
        if ($configuration->user_id !== auth()->id()) {
            abort(403);
        }

        $configuration->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Configuration supprimée avec succès');
    }

    public function dashboard()
    {
        $configurations = auth()->user()->printConfigurations()
            ->with('files')  // Chargement des fichiers associés
            ->latest()      // Les plus récents en premier
            ->get();

        return view('dashboard', [
            'configurations' => $configurations
        ]);
    }

    public function showFiles(PrintConfiguration $configuration)
    {
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cette configuration.');
        }

        return view('configurations.files', [
            'configuration' => $configuration,
            'isValidated' => $configuration->status === PrintConfigurationStatus::VALIDATED->value
        ]);
    }

    public function showCabinetInfo(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette configuration.');
        }

        // Vérifier si les fichiers ont été validés
        if ($configuration->status !== PrintConfigurationStatus::VALIDATED) {
            return redirect()->route('configurations.files', $configuration)
                ->with('error', 'Vous devez d\'abord valider vos fichiers.');
        }

        // Charger les informations du cabinet si elles existent
        $cabinetInfo = $configuration->cabinetInfo;

        return view('configurations.cabinet-info', compact('configuration', 'cabinetInfo'));
    }

    public function saveCabinetInfo(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette configuration.');
        }

        // Valider les données
        $validated = $request->validate([
            'cabinet_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L\'adresse email n\'est pas valide.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.'
        ]);

        // Créer ou mettre à jour les informations du cabinet
        $configuration->cabinetInfo()->updateOrCreate(
            ['print_configuration_id' => $configuration->id],
            $validated
        );

        // Mettre à jour le statut et incrémenter l'étape
        $configuration->update([
            'status' => PrintConfigurationStatus::INFO_COMPLETED->value,
            'step' => 3
        ]);

        return redirect()->route('configurations.print-options', $configuration)
            ->with('success', 'Les informations du cabinet ont été enregistrées avec succès.');
    }

    public function showTribunalInfo(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette configuration.');
        }

        // Vérifier si les options d'impression ont été configurées
        if ($configuration->status !== 'options_completed') {
            return redirect()->route('configurations.print-options', $configuration)
                ->with('error', 'Vous devez d\'abord configurer les options d\'impression.');
        }

        // Charger les informations du tribunal si elles existent
        $tribunalInfo = $configuration->tribunalInfo;

        return view('configurations.tribunal-info', compact('configuration', 'tribunalInfo'));
    }

    public function saveTribunalInfo(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette configuration.');
        }

        // Valider les données
        $validated = $request->validate([
            'tribunal_name' => 'required|string|max:255',
            'chamber' => 'nullable|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
        ], [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L\'adresse email n\'est pas valide.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.'
        ]);

        // Créer ou mettre à jour les informations du tribunal
        $configuration->tribunalInfo()->updateOrCreate(
            ['print_configuration_id' => $configuration->id],
            $validated
        );

        // Mettre à jour le statut et incrémenter l'étape
        $configuration->update([
            'status' => PrintConfigurationStatus::DELIVERY_COMPLETED->value,
            'step' => 4
        ]);

        return redirect()->route('configurations.summary', $configuration)
            ->with('success', 'Les informations du tribunal ont été enregistrées avec succès.');
    }

    public function showSummary(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette configuration.');
        }

        // Vérifier si toutes les étapes précédentes ont été complétées
        if ($configuration->status !== 'delivery_completed') {
            return redirect()->route('configurations.tribunal-info', $configuration)
                ->with('error', 'Vous devez d\'abord compléter toutes les étapes précédentes.');
        }

        return view('configurations.summary', compact('configuration'));
    }

    public function validateOrder(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette configuration.');
        }

        // Mettre à jour le statut et incrémenter l'étape
        $configuration->update([
            'status' => PrintConfigurationStatus::READY_FOR_PAYMENT->value,
            'step' => 5
        ]);

        // Notifier tous les administrateurs
        User::where('is_admin', 1)->get()->each(function ($admin) use ($configuration) {
            $admin->notify(new NewOrderValidated($configuration));
        });

        return redirect()->route('configurations.payment', $configuration)
            ->with('success', 'Votre commande a été validée. Vous pouvez maintenant procéder au paiement.');
    }

    public function showPrintOptions(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette configuration.');
        }

        // Vérifier si les informations du cabinet ont été renseignées
        if ($configuration->status !== PrintConfigurationStatus::INFO_COMPLETED->value) {
            return redirect()->route('configurations.cabinet-info', $configuration)
                ->with('error', 'Vous devez d\'abord renseigner les informations du cabinet.');
        }

        return view('configurations.print-options', [
            'configuration' => $configuration,
            'paperTypes' => array_keys(self::PAPER_PRICES),
            'formats' => array_keys(self::FORMAT_PRICES),
            'bindingTypes' => array_keys(self::BINDING_PRICES),
            'deliveryTypes' => array_keys(self::DELIVERY_PRICES),
        ]);
    }

    public function savePrintOptions(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette configuration.');
        }

        // Valider les données
        $validated = $request->validate([
            'print_type' => 'required|in:noir_blanc,couleur',
            'binding_type' => 'required|in:' . implode(',', array_keys(self::BINDING_PRICES)),
            'delivery_type' => 'required|in:' . implode(',', array_keys(self::DELIVERY_PRICES)),
            'paper_type' => 'required|in:' . implode(',', array_keys(self::PAPER_PRICES)),
            'format' => 'required|in:' . implode(',', array_keys(self::FORMAT_PRICES)),
        ]);

        // Mettre à jour les options d'impression
        $configuration->update($validated + [
            'status' => PrintConfigurationStatus::OPTIONS_COMPLETED->value,
            'step' => 4
        ]);

        return redirect()->route('configurations.tribunal-info', $configuration)
            ->with('success', 'Les options d\'impression ont été enregistrées avec succès.');
    }

    public function showPayment(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette configuration.');
        }

        // Vérifier si la commande est prête pour le paiement
        if ($configuration->status !== PrintConfigurationStatus::READY_FOR_PAYMENT->value) {
            return redirect()->route('configurations.summary', $configuration)
                ->with('error', 'Vous devez d\'abord valider votre commande.');
        }

        return view('configurations.payment', compact('configuration'));
    }

    public function showCabinetCreate(PrintConfiguration $configuration)
    {
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        if ($configuration->status !== 'validated') {
            return redirect()->route('dossier.files', $configuration)
                ->with('error', 'Vous devez d\'abord valider vos fichiers.');
        }

        return view('configurations.cabinet-create', compact('configuration'));
    }
}
