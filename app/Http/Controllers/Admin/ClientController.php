<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::with(['printConfigurations' => function($query) {
            $query->latest();
        }])->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function show(User $client)
    {
        $configurations = $client->printConfigurations()->latest()->get();
        return view('admin.clients.show', compact('client', 'configurations'));
    }

    public function updatePaymentStatus(PrintConfiguration $configuration)
    {
        $configuration->update([
            'is_paid' => !$configuration->is_paid
        ]);

        return back()->with('success', 'Statut de paiement mis à jour avec succès');
    }
}
