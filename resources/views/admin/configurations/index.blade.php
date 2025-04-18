@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Paramètres</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Jurisdiction Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Types de juridiction</h2>
            <p class="text-gray-600 mb-4">Gérer les différents types de juridictions disponibles dans le système.</p>
            <a href="{{ route('admin.settings.jurisdiction.index') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Gérer les types de juridiction
            </a>
        </div>

        <!-- Pleading Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Types de plaidoirie</h2>
            <p class="text-gray-600 mb-4">Gérer les différents types de plaidoiries disponibles.</p>
            <a href="{{ route('admin.settings.pleading.index') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Gérer les types de plaidoirie
            </a>
        </div>

        <!-- Representation Zones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Zones de représentation</h2>
            <p class="text-gray-600 mb-4">Gérer les différentes zones de représentation.</p>
            <a href="{{ route('admin.settings.zone.index') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Gérer les zones de représentation
            </a>
        </div>

        <!-- Print Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Types d'impression</h2>
            <p class="text-gray-600 mb-4">Gérer les différents types d'impression disponibles.</p>
            <a href="{{ route('admin.settings.print.index') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Gérer les types d'impression
            </a>
        </div>

        <!-- Binding Types -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Types de reliure</h2>
            <p class="text-gray-600 mb-4">Gérer les différents types de reliure disponibles.</p>
            <a href="{{ route('admin.settings.binding.index') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Gérer les types de reliure
            </a>
        </div>
    </div>
</div>
@endsection 