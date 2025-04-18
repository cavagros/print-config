<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Models\ConfigurationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConfigurationFileController extends Controller
{
    public function show(PrintConfiguration $configuration)
    {
        $files = $configuration->files()
            ->orderBy('order')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'size_human' => $this->formatBytes($file->size),
                    'preview_url' => Storage::url($file->file_path),
                    'order' => $file->order
                ];
            });

        return view('dossier.files', [
            'configuration' => $configuration,
            'files' => $files,
            'isValidated' => $configuration->status === 'file_sent' || $configuration->is_locked
        ]);
    }

    public function store(Request $request, PrintConfiguration $configuration)
    {
        if ($configuration->status === 'file_sent' || $configuration->is_locked) {
            return back()->with('error', 'Les fichiers ont été validés et ne peuvent plus être modifiés.');
        }

        if ($configuration->hasReachedMaxFiles()) {
            return back()->with('error', 'Le nombre maximum de fichiers (5) a été atteint.');
        }

        $request->validate([
            'files.*' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
        ]);

        try {
            $files = $request->file('files');
            $uploadedCount = 0;

            foreach ($files as $file) {
                if ($configuration->hasReachedMaxFiles()) {
                    break;
                }

                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $size = $file->getSize();

                $directory = $configuration->getFilesDirectory();
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }

                $fileName = Str::random(40) . '.' . $extension;
                $path = $file->storeAs($directory, $fileName, 'public');

                $configuration->files()->create([
                    'original_name' => $originalName,
                    'file_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $size,
                    'order' => $configuration->getFilesCount() + 1
                ]);

                $uploadedCount++;
            }

            if ($uploadedCount > 0) {
                return back()->with('success', $uploadedCount . ' fichier(s) uploadé(s) avec succès');
            }

            return back()->with('error', 'Aucun fichier n\'a pu être uploadé');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'upload des fichiers : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'upload des fichiers');
        }
    }

    public function destroy(PrintConfiguration $configuration, ConfigurationFile $file)
    {
        if ($configuration->status === 'file_sent' || $configuration->is_locked) {
            return back()->with('error', 'Les fichiers ont été validés et ne peuvent plus être modifiés.');
        }

        if ($file->print_configuration_id !== $configuration->id) {
            return back()->with('error', 'Fichier non trouvé.');
        }

        try {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
            return back()->with('success', 'Fichier supprimé avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du fichier : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression du fichier.');
        }
    }

    public function validateFiles(Request $request, PrintConfiguration $configuration)
    {
        if ($configuration->status === 'file_sent' || $configuration->is_locked) {
            return back()->with('error', 'Les fichiers ont déjà été validés.');
        }

        if ($configuration->getFilesCount() === 0) {
            return back()->with('error', 'Aucun fichier à valider.');
        }

        try {
            $configuration->update([
                'status' => 'file_sent',
                'is_locked' => true
            ]);

            return redirect()->route('dossier.cabinet', $configuration)
                ->with('success', 'Fichiers validés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation des fichiers : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la validation des fichiers.');
        }
    }

    public function preview(PrintConfiguration $configuration, ConfigurationFile $file)
    {
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
