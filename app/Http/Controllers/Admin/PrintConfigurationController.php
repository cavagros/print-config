<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class PrintConfigurationController extends Controller
{
    public function index()
    {
        $configurations = PrintConfiguration::with('user')
            ->latest()
            ->paginate(10);

        return view('admin.configurations.index', compact('configurations'));
    }

    public function show(PrintConfiguration $configuration)
    {
        return view('admin.configurations.show', compact('configuration'));
    }

    public function destroy(PrintConfiguration $configuration)
    {
        $configuration->delete();
        return redirect()->route('admin.configurations.index')
            ->with('success', 'Configuration supprimée avec succès');
    }
}
