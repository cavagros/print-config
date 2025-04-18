@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Configuration d'impression</h1>

            <form id="configurationForm" action="{{ route('products.save-configuration') }}" method="POST" class="space-y-8">
                @csrf
                <input type="hidden" name="target_user_id" value="{{ $targetUserId }}">

                <!-- Informations de base -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de base</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom de la configuration</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $configuration->name ?? '') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pages" class="block text-sm font-medium text-gray-700">Nombre de pages</label>
                            <input type="number" name="pages" id="pages" value="{{ old('pages', $configuration->pages ?? '') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('pages')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Options d'impression -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Options d'impression</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="print_type" class="block text-sm font-medium text-gray-700">Type d'impression</label>
                            <select name="print_type" id="print_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="noir_blanc" {{ old('print_type', $configuration->print_type ?? '') == 'noir_blanc' ? 'selected' : '' }}>Noir et blanc</option>
                                <option value="couleur" {{ old('print_type', $configuration->print_type ?? '') == 'couleur' ? 'selected' : '' }}>Couleur</option>
                            </select>
                            @error('print_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="paper_type" class="block text-sm font-medium text-gray-700">Type de papier</label>
                            <select name="paper_type" id="paper_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($paperTypes as $type)
                                    <option value="{{ $type }}" {{ old('paper_type', $configuration->paper_type ?? '') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('paper_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700">Format</label>
                            <select name="format" id="format" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($formats as $format)
                                    <option value="{{ $format }}" {{ old('format', $configuration->format ?? '') == $format ? 'selected' : '' }}>
                                        {{ $format }}
                                    </option>
                                @endforeach
                            </select>
                            @error('format')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="binding_type" class="block text-sm font-medium text-gray-700">Type de reliure</label>
                            <select name="binding_type" id="binding_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="agrafage" {{ old('binding_type', $configuration->binding_type ?? '') == 'agrafage' ? 'selected' : '' }}>Agrafage</option>
                                <option value="spirale" {{ old('binding_type', $configuration->binding_type ?? '') == 'spirale' ? 'selected' : '' }}>Spirale</option>
                                <option value="dos_colle" {{ old('binding_type', $configuration->binding_type ?? '') == 'dos_colle' ? 'selected' : '' }}>Dos collé</option>
                                <option value="sans_reliure" {{ old('binding_type', $configuration->binding_type ?? '') == 'sans_reliure' ? 'selected' : '' }}>Sans reliure</option>
                            </select>
                            @error('binding_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Livraison -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Livraison</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="delivery_type" class="block text-sm font-medium text-gray-700">Type de livraison</label>
                            <select name="delivery_type" id="delivery_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="retrait_magasin" {{ old('delivery_type', $configuration->delivery_type ?? '') == 'retrait_magasin' ? 'selected' : '' }}>Retrait en magasin</option>
                                <option value="livraison_standard" {{ old('delivery_type', $configuration->delivery_type ?? '') == 'livraison_standard' ? 'selected' : '' }}>Livraison standard</option>
                                <option value="livraison_express" {{ old('delivery_type', $configuration->delivery_type ?? '') == 'livraison_express' ? 'selected' : '' }}>Livraison express</option>
                            </select>
                            @error('delivery_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Résumé et prix -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé et prix</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prix total</label>
                            <div class="mt-1 text-2xl font-bold text-indigo-600" id="totalPrice">0.00 €</div>
                            <input type="hidden" name="total_price" id="totalPriceInput" value="0.00">
                        </div>
                        @if($hasSubscription)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Avec abonnement</label>
                            <div class="mt-1 text-2xl font-bold text-green-600" id="subscriptionPrice">0.00 €</div>
                            <input type="hidden" name="is_subscription" value="1">
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Annuler
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Valider la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('configurationForm');
    const totalPriceElement = document.getElementById('totalPrice');
    const totalPriceInput = document.getElementById('totalPriceInput');
    const subscriptionPriceElement = document.getElementById('subscriptionPrice');
    const hasSubscription = @json($hasSubscription);

    // Fonction pour calculer le prix
    function calculatePrice() {
        const pages = parseInt(document.getElementById('pages').value) || 0;
        const printType = document.getElementById('print_type').value;
        const paperType = document.getElementById('paper_type').value;
        const format = document.getElementById('format').value;
        const bindingType = document.getElementById('binding_type').value;
        const deliveryType = document.getElementById('delivery_type').value;

        // Prix de base par page selon le type d'impression
        const basePricePerPage = printType === 'noir_blanc' ? 0.10 : 0.50;
        
        // Prix du papier
        const paperPrices = {
            'standard': 0.00,
            'recycle': 0.02,
            'premium': 0.05,
            'photo': 0.10
        };
        
        // Prix des formats
        const formatPrices = {
            'A4': 0.00,
            'A3': 0.50,
            'A5': -0.05
        };
        
        // Prix des reliures
        const bindingPrices = {
            'agrafage': 2.00,
            'spirale': 5.00,
            'dos_colle': 7.00,
            'sans_reliure': 0.00
        };
        
        // Prix des livraisons
        const deliveryPrices = {
            'retrait_magasin': 0.00,
            'livraison_standard': 5.00,
            'livraison_express': 15.00
        };

        // Calcul du prix total
        let totalPrice = (basePricePerPage + paperPrices[paperType] + formatPrices[format]) * pages;
        totalPrice += bindingPrices[bindingType] + deliveryPrices[deliveryType];

        // Mise à jour de l'affichage et du champ caché
        totalPriceElement.textContent = totalPrice.toFixed(2) + ' €';
        totalPriceInput.value = totalPrice.toFixed(2);

        if (hasSubscription) {
            const subscriptionPrice = totalPrice * 0.85; // 15% de réduction
            subscriptionPriceElement.textContent = subscriptionPrice.toFixed(2) + ' €';
        }
    }

    // Écouteurs d'événements pour le calcul automatique
    ['pages', 'print_type', 'paper_type', 'format', 'binding_type', 'delivery_type'].forEach(id => {
        document.getElementById(id).addEventListener('change', calculatePrice);
    });

    // Calcul initial
    calculatePrice();

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Vérifier que tous les champs requis sont remplis
        const requiredFields = ['name', 'pages', 'print_type', 'paper_type', 'format', 'binding_type', 'delivery_type'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value) {
                isValid = false;
                element.classList.add('border-red-500');
            } else {
                element.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            alert('Veuillez remplir tous les champs requis');
            return;
        }

        // Soumettre le formulaire
        this.submit();
    });
});
</script>
@endpush
@endsection 

