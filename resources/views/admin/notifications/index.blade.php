<x-app-layout>
    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notifications
        </h2>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($notifications->isEmpty())
                        <p class="text-gray-500 text-center py-4">Aucune notification</p>
                    @else
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="border rounded-lg p-4 {{ $notification->read_at ? 'bg-gray-50' : 'bg-white border-indigo-200' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium {{ $notification->read_at ? 'text-gray-600' : 'text-indigo-600' }}">
                                                Nouvelle commande - {{ $notification->data['configuration_name'] }}
                                            </h3>
                                            <div class="mt-2 space-y-1">
                                                <p class="text-sm text-gray-600">Cabinet : {{ $notification->data['cabinet_name'] }}</p>
                                                <p class="text-sm text-gray-600">Tribunal : {{ $notification->data['tribunal_name'] }}</p>
                                                <p class="text-sm text-gray-600">Nombre de fichiers : {{ $notification->data['files_count'] }}</p>
                                                <p class="text-sm font-medium text-gray-900">Prix total : {{ number_format($notification->data['total_price'], 2, ',', ' ') }} €</p>
                                            </div>
                                            <div class="mt-3 flex items-center space-x-4">
                                                <a href="{{ route('admin.configurations.show', $notification->data['configuration_id']) }}" 
                                                   class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Voir les détails
                                                </a>
                                                @if(!$notification->read_at)
                                                    <form method="POST" action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Marquer comme lu
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            <span class="text-sm text-gray-500">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 