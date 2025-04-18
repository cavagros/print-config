@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">{{ isset($jurisdiction) ? 'Modifier' : 'Créer' }} un type de juridiction</h1>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ isset($jurisdiction) ? route('settings.jurisdiction.update', $jurisdiction) : route('settings.jurisdiction.store') }}" method="POST">
            @csrf
            @if(isset($jurisdiction))
                @method('PUT')
            @endif

            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Nom</label>
                <input type="text" name="name" id="name" value="{{ old('name', $jurisdiction->name ?? '') }}" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="code" class="block text-gray-700 font-bold mb-2">Code</label>
                <input type="text" name="code" id="code" value="{{ old('code', $jurisdiction->code ?? '') }}" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" id="description" rows="3" 
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $jurisdiction->description ?? '') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                        {{ old('is_active', $jurisdiction->is_active ?? true) ? 'checked' : '' }}
                        class="mr-2">
                    <span class="text-gray-700">Actif</span>
                </label>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('admin.configurations.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    {{ isset($jurisdiction) ? 'Mettre à jour' : 'Créer' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 