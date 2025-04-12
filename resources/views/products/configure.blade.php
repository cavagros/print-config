<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurateur d\'impression') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form id="print-config" class="space-y-8" action="{{ route('products.save-configuration') }}" method="POST">
                        @csrf
                        
                        <input type="hidden" name="name" value="Configuration du {{ now()->format('d/m/Y H:i') }}">
                        
                        <!-- Statut d'abonnement -->
                        @if(auth()->user()->hasActiveSubscription())
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium">Vous êtes abonné !</p>
                                        <p class="text-sm">Vous bénéficiez d'une réduction de 15% sur tous vos projets.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Nombre de pages -->
                        <div class="space-y-2">
                            <label for="pages" class="block text-sm font-medium">Nombre de pages</label>
                            <div class="flex items-center space-x-4">
                                <input type="number" name="pages" id="pages" min="1" 
                                    value="{{ $configuration ? $configuration->pages : 1 }}"
                                    class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                                <span class="text-sm text-gray-500">
                                    Réductions : 5% dès 100 pages, 10% dès 500 pages, 15% dès 1000 pages
                                </span>
                            </div>
                        </div>

                        <!-- Type d'impression -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Type d'impression</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input type="radio" name="print_type" value="noir_blanc" id="noir_blanc" 
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ !$configuration || $configuration->print_type === 'noir_blanc' ? 'checked' : '' }}>
                                    <label for="noir_blanc" class="ml-3">Noir et blanc (0,10 € / page)</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="print_type" value="couleur" id="couleur" 
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ $configuration && $configuration->print_type === 'couleur' ? 'checked' : '' }}>
                                    <label for="couleur" class="ml-3">Couleur (0,50 € / page)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Type de papier -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Type de papier</label>
                            <select name="paper_type" id="paper_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="standard" {{ !$configuration || $configuration->paper_type === 'standard' ? 'selected' : '' }}>Standard (pas de supplément)</option>
                                <option value="recycle" {{ $configuration && $configuration->paper_type === 'recycle' ? 'selected' : '' }}>Recyclé (+0,02 € / page)</option>
                                <option value="premium" {{ $configuration && $configuration->paper_type === 'premium' ? 'selected' : '' }}>Premium (+0,05 € / page)</option>
                                <option value="photo" {{ $configuration && $configuration->paper_type === 'photo' ? 'selected' : '' }}>Photo (+0,10 € / page)</option>
                            </select>
                        </div>

                        <!-- Format -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Format</label>
                            <select name="format" id="format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="A4" {{ !$configuration || $configuration->format === 'A4' ? 'selected' : '' }}>A4 (standard)</option>
                                <option value="A3" {{ $configuration && $configuration->format === 'A3' ? 'selected' : '' }}>A3 (+0,50 € / page)</option>
                                <option value="A5" {{ $configuration && $configuration->format === 'A5' ? 'selected' : '' }}>A5 (-0,05 € / page)</option>
                            </select>
                        </div>

                        <!-- Type de reliure -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Type de reliure</label>
                            <select name="binding_type" id="binding_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="sans_reliure" {{ !$configuration || $configuration->binding_type === 'sans_reliure' ? 'selected' : '' }}>Sans reliure (gratuit)</option>
                                <option value="agrafage" {{ $configuration && $configuration->binding_type === 'agrafage' ? 'selected' : '' }}>Agrafage (2,00 €)</option>
                                <option value="spirale" {{ $configuration && $configuration->binding_type === 'spirale' ? 'selected' : '' }}>Spirale (5,00 €)</option>
                                <option value="dos_colle" {{ $configuration && $configuration->binding_type === 'dos_colle' ? 'selected' : '' }}>Dos collé (7,00 €)</option>
                            </select>
                        </div>

                        <!-- Type de dépôt -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Type de dépôt</label>
                            <select name="delivery_type" id="delivery_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="retrait_magasin" {{ !$configuration || $configuration->delivery_type === 'retrait_magasin' ? 'selected' : '' }}>Retrait en magasin (gratuit)</option>
                                <option value="livraison_standard" {{ $configuration && $configuration->delivery_type === 'livraison_standard' ? 'selected' : '' }}>Livraison standard (5,00 €)</option>
                                <option value="livraison_express" {{ $configuration && $configuration->delivery_type === 'livraison_express' ? 'selected' : '' }}>Livraison express (15,00 €)</option>
                            </select>
                        </div>

                        <!-- Nom de la configuration -->
                        <div class="space-y-2">
                            <label for="configuration_name" class="block text-sm font-medium">Nom de la configuration</label>
                            <input type="text" name="configuration_name" id="configuration_name"
                                value="{{ $configuration ? $configuration->name : '' }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Ma configuration d'impression">
                        </div>

                        <!-- Récapitulatif détaillé -->
                        <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg space-y-4">
                            <h3 class="text-lg font-medium">Récapitulatif détaillé</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Prix par page :</span>
                                    <span id="price-per-page">0,00 €</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Prix total des pages :</span>
                                    <span id="total-pages-price">0,00 €</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Réduction appliquée :</span>
                                    <span id="discount-applied">0%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Prix de la reliure :</span>
                                    <span id="binding-price">0,00 €</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Prix de la livraison :</span>
                                    <span id="delivery-price">0,00 €</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-4 border-t">
                                    <span>Prix total :</span>
                                    <span id="estimated-price" class="text-2xl text-indigo-600 dark:text-indigo-400">0,00 €</span>
                                </div>
                            </div>
                        </div>

                        <!-- Boutons -->
                        <div class="flex justify-end space-x-4">
                            <button type="button" onclick="calculatePrice()" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Calculer le prix
                            </button>
                            <button type="button" onclick="proceedToPayment()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Payer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Fonction de calcul du prix
            window.calculatePrice = function() {
                const formData = new FormData(document.getElementById('print-config'));
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch('{{ route("products.calculate") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('estimated-price').textContent = data.price + ' €';
                    document.getElementById('price-per-page').textContent = data.details.price_per_page + ' €';
                    document.getElementById('total-pages-price').textContent = data.details.pages + ' €';
                    document.getElementById('binding-price').textContent = data.details.binding + ' €';
                    document.getElementById('delivery-price').textContent = data.details.delivery + ' €';
                    document.getElementById('discount-applied').textContent = data.details.discount_applied;
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du calcul du prix');
                });
            };

            // Fonction pour procéder au paiement
            window.proceedToPayment = function() {
                // Vérifier que tous les champs requis sont remplis
                const requiredFields = ['configuration_name', 'pages', 'print_type', 'binding_type', 'delivery_type', 'paper_type', 'format'];
                for (const field of requiredFields) {
                    const element = field === 'print_type' 
                        ? document.querySelector('input[name="print_type"]:checked')
                        : document.getElementById(field);
                    
                    if (!element || !element.value) {
                        alert(`Le champ ${field} est requis`);
                        return;
                    }
                }

                // Si le nom n'est pas fourni, générer un nom par défaut
                const name = document.getElementById('configuration_name').value || `Configuration du ${new Date().toLocaleDateString()}`;

                // Récupérer les valeurs du formulaire
                const formData = {
                    name: name,
                    pages: document.getElementById('pages').value,
                    print_type: document.querySelector('input[name="print_type"]:checked').value,
                    binding_type: document.getElementById('binding_type').value,
                    delivery_type: document.getElementById('delivery_type').value,
                    paper_type: document.getElementById('paper_type').value,
                    format: document.getElementById('format').value,
                    total_price: document.getElementById('estimated-price').textContent.replace(' €', '')
                };

                // Envoyer les données au serveur
                fetch('/products/save-configuration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Erreur lors de la sauvegarde de la configuration');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Rediriger vers la page de paiement avec l'ID de la configuration
                        window.location.href = `/payment/${data.configuration_id}`;
                    } else {
                        throw new Error(data.message || 'Erreur lors de la sauvegarde de la configuration');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert(error.message);
                });
            };

            // Ajouter les écouteurs d'événements
            document.querySelectorAll('#print-config input, #print-config select').forEach(element => {
                element.addEventListener('change', calculatePrice);
            });

            // Calculer le prix initial
            calculatePrice();
        });
    </script>
    @endpush
</x-app-layout> 
