<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurisdictionType;
use App\Models\PleadingType;
use App\Models\RepresentationZone;
use App\Models\PrintType;
use App\Models\BindingType;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    private function checkAdmin()
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }
    }

    public function index()
    {
        if (auth()->user()->is_admin) {
            return view('admin.configurations.index', [
                'jurisdictions' => JurisdictionType::all(),
                'pleadings' => PleadingType::all(),
                'zones' => RepresentationZone::all(),
                'printTypes' => PrintType::all(),
                'bindingTypes' => BindingType::all()
            ]);
        } else {
            return view('dashboard');
        }
    }

    // Jurisdiction Types
    public function createJurisdiction()
    {
        $this->checkAdmin();
        return view('admin.configurations.jurisdiction.create');
    }

    public function storeJurisdiction(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:jurisdiction_types',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        JurisdictionType::create($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Type de juridiction créé avec succès');
    }

    public function editJurisdiction(JurisdictionType $jurisdiction)
    {
        $this->checkAdmin();
        return view('admin.configurations.jurisdiction.edit', compact('jurisdiction'));
    }

    public function updateJurisdiction(Request $request, JurisdictionType $jurisdiction)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:jurisdiction_types,code,' . $jurisdiction->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $jurisdiction->update($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Type de juridiction mis à jour avec succès');
    }

    public function indexJurisdiction()
    {
        $this->checkAdmin();
        $jurisdictions = JurisdictionType::all();
        return view('admin.configurations.jurisdiction.index', compact('jurisdictions'));
    }

    public function destroyJurisdiction(JurisdictionType $jurisdiction)
    {
        $this->checkAdmin();

        $jurisdiction->delete();

        return redirect()->route('admin.settings.jurisdiction.index')
            ->with('success', 'Type de juridiction supprimé avec succès');
    }

    // Pleading Types
    public function indexPleading()
    {
        $this->checkAdmin();
        $pleadings = PleadingType::all();
        return view('admin.configurations.pleading.index', compact('pleadings'));
    }

    public function createPleading()
    {
        $this->checkAdmin();
        return view('admin.configurations.pleading.create');
    }

    public function storePleading(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:pleading_types',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        PleadingType::create($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Type de plaidoirie créé avec succès');
    }

    public function editPleading(PleadingType $pleading)
    {
        $this->checkAdmin();
        return view('admin.configurations.pleading.edit', compact('pleading'));
    }

    public function updatePleading(Request $request, PleadingType $pleading)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:pleading_types,code,' . $pleading->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $pleading->update($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Type de plaidoirie mis à jour avec succès');
    }

    // Representation Zones
    public function indexZone()
    {
        $this->checkAdmin();
        $zones = RepresentationZone::all();
        return view('admin.configurations.zone.index', compact('zones'));
    }

    public function createZone()
    {
        $this->checkAdmin();
        return view('admin.configurations.zone.create');
    }

    public function storeZone(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:representation_zones',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        RepresentationZone::create($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Zone de représentation créée avec succès');
    }

    public function editZone(RepresentationZone $zone)
    {
        $this->checkAdmin();
        return view('admin.configurations.zone.edit', compact('zone'));
    }

    public function updateZone(Request $request, RepresentationZone $zone)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:representation_zones,code,' . $zone->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $zone->update($validated);

        return redirect()->route('admin.configurations.index')
            ->with('success', 'Zone de représentation mise à jour avec succès');
    }

    // Binding Types
    public function indexBinding()
    {
        $this->checkAdmin();
        $bindings = BindingType::all();
        return view('admin.configurations.binding.index', compact('bindings'));
    }

    public function createBinding()
    {
        $this->checkAdmin();
        return view('admin.configurations.binding.create');
    }

    public function storeBinding(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:binding_types',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        BindingType::create($validated);

        return redirect()->route('admin.settings.binding.index')
            ->with('success', 'Type de reliure créé avec succès');
    }

    public function editBinding(BindingType $binding)
    {
        $this->checkAdmin();
        return view('admin.configurations.binding.edit', compact('binding'));
    }

    public function updateBinding(Request $request, BindingType $binding)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:binding_types,code,' . $binding->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $binding->update($validated);

        return redirect()->route('admin.settings.binding.index')
            ->with('success', 'Type de reliure mis à jour avec succès');
    }

    public function destroyBinding(BindingType $binding)
    {
        $this->checkAdmin();

        $binding->delete();

        return redirect()->route('admin.settings.binding.index')
            ->with('success', 'Type de reliure supprimé avec succès');
    }

    public function indexPrint()
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $printTypes = PrintType::all();
        return view('admin.configurations.print.index', compact('printTypes'));
    }

    public function editPrint(PrintType $printType)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        return view('admin.configurations.print.edit', compact('printType'));
    }

    public function createPrint()
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        return view('admin.configurations.print.create');
    }

    public function storePrint(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:print_types',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        PrintType::create($validated);

        return redirect()->route('admin.settings.print.index')
            ->with('success', 'Type d\'impression créé avec succès');
    }

    public function updatePrint(Request $request, PrintType $printType)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:print_types,code,' . $printType->id,
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $printType->update($validated);

        return redirect()->route('admin.settings.print.index')
            ->with('success', 'Type d\'impression mis à jour avec succès');
    }

    public function destroyPrint(PrintType $printType)
    {
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        $printType->delete();

        return redirect()->route('admin.settings.print.index')
            ->with('success', 'Type d\'impression supprimé avec succès');
    }
} 