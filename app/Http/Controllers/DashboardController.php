<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                \Log::error('No authenticated user found');
                return redirect()->route('login');
            }

            \Log::info('Dashboard access', [
                'user_id' => $user->id,
                'is_admin' => $user->is_admin,
                'email' => $user->email
            ]);

            // Si l'utilisateur est admin, afficher le tableau de bord admin
            if ($user->is_admin) {
                // Récupération des statistiques
                $totalConfigurations = PrintConfiguration::count();
                
                // Récupération des dossiers payés avec les relations utilisateur
                $paidConfigurations = PrintConfiguration::where('is_paid', true)
                    ->with('user')
                    ->orderBy('updated_at', 'desc')
                    ->get();
                    
                // Récupération des dossiers non payés avec les relations utilisateur
                $unpaidConfigurations = PrintConfiguration::where('is_paid', false)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                // Récupération des utilisateurs avec le nombre de dossiers
                $users = User::withCount('printConfigurations')
                    ->with(['printConfigurations' => function($query) {
                        $query->orderBy('created_at', 'desc');
                    }])
                    ->orderBy('created_at', 'desc')
                    ->get();

                \Log::info('Admin dashboard data', [
                    'total_configurations' => $totalConfigurations,
                    'paid_configurations_count' => $paidConfigurations->count(),
                    'unpaid_configurations_count' => $unpaidConfigurations->count(),
                    'users_count' => $users->count()
                ]);

                return view('dashboard', compact(
                    'totalConfigurations',
                    'paidConfigurations',
                    'unpaidConfigurations',
                    'users'
                ));
            }

            // Sinon, afficher le tableau de bord utilisateur normal
            $configurations = $user->printConfigurations()
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('User dashboard data', [
                'configurations_count' => $configurations->count()
            ]);

            return view('dashboard', compact('configurations'));
        } catch (\Exception $e) {
            \Log::error('Dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors du chargement du tableau de bord.');
        }
    }
} 