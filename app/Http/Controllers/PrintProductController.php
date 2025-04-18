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
        if ($request->has('configuration_id')) {
            $configuration = PrintConfiguration::findOrFail($request->configuration_id);
            // Vérifier que l'utilisateur est propriétaire de la configuration ou est admin
            if ($configuration->user_id !== auth()->id() && !auth()->user()->is_admin) {
                abort(403);
            }
        }

        // Vérifier l'abonnement du propriétaire de la configuration
        $owner = $configuration ? $configuration->user : auth()->user();
        
        // Log pour déboguer
        \Log::info('Vérification abonnement', [
            'user_id' => $owner->id,
            'is_admin' => auth()->user()->is_admin,
            'configuration_id' => $configuration ? $configuration->id : null,
            'configuration_owner_id' => $configuration ? $configuration->user_id : null
        ]);

        // Vérifier l'abonnement de manière plus détaillée
        $subscription = $owner->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        $hasSubscription = (bool) $subscription;

        // Log supplémentaire pour voir le résultat
        \Log::info('Résultat vérification abonnement', [
            'has_subscription' => $hasSubscription,
            'subscription_status' => $subscription ? $subscription->stripe_status : null
        ]);

        // Déterminer l'étape actuelle
        $step = $request->input('step', 1);

        return view('products.configure', [
            'paperTypes' => array_keys(self::PAPER_PRICES),
            'formats' => array_keys(self::FORMAT_PRICES),
            'configuration' => $configuration,
            'hasSubscription' => $hasSubscription,
            'targetUserId' => auth()->id(),
            'step' => $step
        ]);
    }

    public function calculate(Request $request)
    {
        \Log::info('Début du calcul du prix', $request->all());

        $request->validate([
            'pages' => 'required|integer|min:1',
            'print_type' => 'required|in:noir_blanc,couleur',
            'binding_type' => 'required|in:agrafage,spirale,dos_colle,sans_reliure',
            'delivery_type' => 'required|in:retrait_magasin,livraison_standard,livraison_express',
            'paper_type' => 'required|in:standard,recycle,premium,photo',
            'format' => 'required|in:A4,A3,A5',
            'target_user_id' => 'required|exists:users,id'
        ]);

        $targetUser = User::findOrFail($request->target_user_id);
        $hasSubscription = $targetUser->hasActiveSubscription() || $targetUser->isAdmin();

        // Calcul du prix de base par page
        $basePagePrice = self::PRICE_PER_PAGE[$request->print_type];
        \Log::info('Prix de base par page:', ['print_type' => $request->print_type, 'price' => $basePagePrice]);
        
        // Ajout du prix du papier spécial
        $basePagePrice += self::PAPER_PRICES[$request->paper_type];
        \Log::info('Prix après ajout du papier:', ['paper_type' => $request->paper_type, 'price' => $basePagePrice]);
        
        // Ajout du prix du format
        $basePagePrice += self::FORMAT_PRICES[$request->format];
        \Log::info('Prix après ajout du format:', ['format' => $request->format, 'price' => $basePagePrice]);

        // Calcul du prix total des pages
        $pagePrice = $basePagePrice * $request->pages;
        \Log::info('Prix total des pages:', ['pages' => $request->pages, 'price' => $pagePrice]);

        // Application des réductions par quantité
        $quantityDiscount = 0;
        foreach (self::QUANTITY_DISCOUNTS as $threshold => $discountRate) {
            if ($request->pages >= $threshold) {
                $quantityDiscount = $discountRate;
            }
        }
        \Log::info('Réduction quantité appliquée:', ['discount' => $quantityDiscount]);

        // Prix après réduction quantité
        $pagePrice = $pagePrice * (1 - $quantityDiscount);
        \Log::info('Prix après réduction quantité:', ['price' => $pagePrice]);

        // Ajout du prix de la reliure
        $bindingPrice = self::BINDING_PRICES[$request->binding_type];
        \Log::info('Prix de la reliure:', ['binding_type' => $request->binding_type, 'price' => $bindingPrice]);

        // Ajout du prix de la livraison
        $deliveryPrice = self::DELIVERY_PRICES[$request->delivery_type];
        \Log::info('Prix de la livraison:', ['delivery_type' => $request->delivery_type, 'price' => $deliveryPrice]);

        // Prix total
        $totalPrice = $pagePrice + $bindingPrice + $deliveryPrice;
        \Log::info('Prix total avant réduction abonnement:', ['price' => $totalPrice]);

        // Appliquer la réduction d'abonnement uniquement si is_subscription est true dans la requête
        $subscriptionDiscount = 0;
        if ($hasSubscription && $request->input('is_subscription') === 'true') {
            $subscriptionDiscount = 0.15; // 15% de réduction
            $totalPrice = $totalPrice * 0.85;
            \Log::info('Réduction abonnement appliquée:', ['price' => $totalPrice]);
        }

        // S'assurer que le prix n'est jamais inférieur à 0
        $totalPrice = max(0, $totalPrice);
        \Log::info('Prix final:', ['price' => $totalPrice]);

        return response()->json([
            'price' => $totalPrice,
            'details' => [
                'pages' => $basePagePrice * $request->pages, // Prix total avant remise
                'binding' => $bindingPrice,
                'delivery' => $deliveryPrice,
                'quantity_discount' => $quantityDiscount * 100 . '%',
                'quantity_discount_amount' => number_format(($basePagePrice * $request->pages) * $quantityDiscount, 2, '.', '') . ' €',
                'subscription_discount' => $subscriptionDiscount * 100 . '%',
                'subscription_discount_amount' => $subscriptionDiscount > 0 ? number_format(($pagePrice + $bindingPrice + $deliveryPrice) * $subscriptionDiscount, 2, '.', '') . ' €' : '0.00 €',
                'price_per_page' => $basePagePrice,
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

    private function gen_dossier_id()
    {
        $parts = [];
        for ($i = 0; $i < 5; $i++) {
            $part = '';
            for ($j = 0; $j < 2; $j++) {
                $part .= rand(0, 9);
            }
            $parts[] = $part;
        }
        return implode('-', $parts);
    }

    public function store(Request $request)
    {
        try {
            // Récupérer l'utilisateur cible
            $targetUser = User::findOrFail($request->target_user_id);
            
            // Valider les données
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'total_price' => 'required|string',
                'is_subscription' => 'boolean',
                'pages' => 'required|integer|min:1',
                'print_type' => 'required|in:noir_blanc,couleur',
                'binding_type' => 'required|in:agrafage,spirale,dos_colle,sans_reliure',
                'delivery_type' => 'required|in:retrait_magasin,livraison_standard,livraison_express',
                'paper_type' => 'required|in:standard,recycle,premium,photo',
                'format' => 'required|in:A4,A3,A5',
                'configuration_id' => 'nullable|exists:print_configurations,id',
                'target_user_id' => 'required|exists:users,id'
            ]);

            // Nettoyer et convertir le prix
            $price = str_replace(['€', ' ', '.'], '', $validated['total_price']);
            $price = str_replace(',', '.', $price);
            $totalPrice = (float) $price;

            // Si c'est un abonnement, appliquer la réduction de 15%
            if ($validated['is_subscription'] ?? false) {
                $totalPrice = $totalPrice * 0.85;
            }

            // Si une configuration_id est fournie, mettre à jour la configuration existante
            if ($request->has('configuration_id')) {
                $configuration = PrintConfiguration::findOrFail($request->configuration_id);
                
                // Vérifier que l'utilisateur est autorisé à modifier cette configuration
                if ($configuration->user_id !== $targetUser->id) {
                    abort(403, 'Vous n\'êtes pas autorisé à modifier cette configuration.');
                }

                $configuration->update([
                    'total_price' => $totalPrice / 100,
                    'is_subscription' => $validated['is_subscription'] ?? false,
                    'pages' => $validated['pages'],
                    'status' => 'pending',
                    'print_type' => $validated['print_type'],
                    'binding_type' => $validated['binding_type'],
                    'delivery_type' => $validated['delivery_type'],
                    'paper_type' => $validated['paper_type'],
                    'format' => $validated['format']
                ]);

                return redirect()->route('payment.form', ['configuration' => $configuration->id]);
            }

            // Créer une nouvelle configuration
            $configuration = $targetUser->printConfigurations()->create([
                'name' => $validated['name'],
                'total_price' => $totalPrice / 100,
                'is_subscription' => $validated['is_subscription'] ?? false,
                'pages' => $validated['pages'],
                'status' => 'pending',
                'id_dossier' => $this->gen_dossier_id(),
                'print_type' => $validated['print_type'],
                'binding_type' => $validated['binding_type'],
                'delivery_type' => $validated['delivery_type'],
                'paper_type' => $validated['paper_type'],
                'format' => $validated['format']
            ]);

            \Log::info('Configuration créée', [
                'user_id' => $targetUser->id,
                'configuration_id' => $configuration->id,
                'total_price' => $totalPrice,
                'is_subscription' => $validated['is_subscription'] ?? false,
                'original_price' => $price,
                'discount_applied' => ($validated['is_subscription'] ?? false) ? '15%' : '0%'
            ]);

            return redirect()->route('payment.form', ['configuration' => $configuration->id]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création/mise à jour de la configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la création/mise à jour de la configuration.')
                ->withInput();
        }
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
        try {
            $user = auth()->user();
            $configurations = $user->printConfigurations()
                ->with(['files', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Vérifier le statut des paiements et des abonnements
            foreach ($configurations as $configuration) {
                // Si c'est un abonnement, vérifier son statut
                if ($configuration->is_subscription && $configuration->subscription_id) {
                    try {
                        $subscription = \Stripe\Subscription::retrieve($configuration->subscription_id);
                        $configuration->subscription_status = $subscription->status;
                        $configuration->subscription_end_date = \Carbon\Carbon::createFromTimestamp($subscription->current_period_end);
                        
                        // Mettre à jour le statut dans la base de données
                        $configuration->update([
                            'subscription_status' => $subscription->status,
                            'subscription_end_date' => $configuration->subscription_end_date,
                            'is_paid' => true // S'assurer que le paiement est marqué comme effectué
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors de la vérification de l\'abonnement', [
                            'configuration_id' => $configuration->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            return view('dashboard', [
                'configurations' => $configurations
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans le dashboard', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors du chargement de votre tableau de bord.');
        }
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

        //ID UNIQUE
        

       // dd($id_dossier);

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
            'step' => 4,

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
