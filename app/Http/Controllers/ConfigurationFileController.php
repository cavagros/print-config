<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Models\ConfigurationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Enums\PrintConfigurationStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ConfigurationFileController extends Controller
{
    use AuthorizesRequests;

    public function show(PrintConfiguration $configuration)
    {
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ces fichiers.');
        }

        $files = $configuration->files()->orderBy('order')->get()->map(function ($file) use ($configuration) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'size_human' => $this->formatBytes($file->size),
                'preview_url' => route('dossier.files.preview', ['configuration' => $configuration->id, 'file' => $file->id]),
                'order' => $file->order
            ];
        });

        $isValidated = $configuration->status === 'file_sent';

        return view('dossier.files', compact('configuration', 'files', 'isValidated'));
    }

    public function store(Request $request, PrintConfiguration $configuration)
    {
        if ($configuration->status === 'file_sent') {
            return response()->json([
                'error' => 'Vous avez validé l\'envoi de vos fichiers et ne pouvez plus effectuer de modifications.'
            ], 422);
        }

        if ($configuration->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à modifier ces fichiers.'
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
        ]);

        $file = $request->file('file');
        
        $configFile = ConfigurationFile::create([
            'print_configuration_id' => $configuration->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $file->store('files', 'public'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'order' => $configuration->files()->count() + 1
        ]);

        return response()->json([
            'message' => 'Fichier uploadé avec succès',
            'file' => [
                'id' => $configFile->id,
                'original_name' => $configFile->original_name,
                'size_human' => $this->formatBytes($configFile->size),
                'preview_url' => route('dossier.files.preview', ['configuration' => $configuration->id, 'file' => $configFile->id]),
                'order' => $configFile->order
            ]
        ]);
    }

    public function destroy(PrintConfiguration $configuration, ConfigurationFile $file)
    {
        if ($configuration->status === 'file_sent') {
            return response()->json([
                'error' => 'Vous avez validé l\'envoi de vos fichiers et ne pouvez plus effectuer de modifications.'
            ], 422);
        }

        if ($configuration->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à modifier ces fichiers.'
            ], 403);
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return response()->json([
            'message' => 'Fichier supprimé avec succès'
        ]);
    }

    public function validateFiles(Request $request, PrintConfiguration $configuration)
    {
        if ($configuration->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Vous n\'êtes pas autorisé à modifier ces fichiers.'
            ], 403);
        }

        try {
            if ($configuration->files()->count() === 0) {
                return response()->json([
                    'error' => 'Vous devez ajouter au moins un fichier avant de valider.'
                ], 422);
            }

            $configuration->update([
                'status' => 'file_sent',
                'step' => 2
            ]);

            return response()->json([
                'message' => 'Les fichiers ont été validés avec succès.',
                'redirect' => route('dossier.cabinet', $configuration)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors de la validation des fichiers.'
            ], 500);
        }
    }

    public function preview(PrintConfiguration $configuration, ConfigurationFile $file)
    {
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ce fichier.');
        }

        if ($file->print_configuration_id !== $configuration->id) {
            abort(404, 'Fichier non trouvé.');
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return response()->file(
            Storage::disk('public')->path($file->file_path),
            ['Content-Type' => $file->mime_type]
        );
    }

    private function formatBytes($bytes)
    {
        if ($bytes > 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
