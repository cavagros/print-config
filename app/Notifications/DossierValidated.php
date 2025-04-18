<?php

namespace App\Notifications;

use App\Models\PrintConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierValidated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $configuration;

    public function __construct(PrintConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = route('admin.dossiers.show', $this->configuration);

        $cabinetName = $this->configuration->cabinetInfo ? $this->configuration->cabinetInfo->cabinet_name : 'Non renseigné';
        $tribunalName = $this->configuration->tribunalInfo ? $this->configuration->tribunalInfo->tribunal_name : 'Non renseigné';

        return (new MailMessage)
            ->subject('Nouveau dossier à traiter')
            ->line('Un nouveau dossier a été validé et nécessite votre attention.')
            ->line('Cabinet : ' . $cabinetName)
            ->line('Tribunal : ' . $tribunalName)
            ->line('Nombre de fichiers : ' . $this->configuration->files->count())
            ->action('Voir le dossier', $url)
            ->line('Merci de traiter ce dossier dans les plus brefs délais.');
    }

    public function toArray($notifiable)
    {
        $cabinetName = $this->configuration->cabinetInfo ? $this->configuration->cabinetInfo->cabinet_name : 'Non renseigné';
        $tribunalName = $this->configuration->tribunalInfo ? $this->configuration->tribunalInfo->tribunal_name : 'Non renseigné';

        return [
            'configuration_id' => $this->configuration->id,
            'cabinet_name' => $cabinetName,
            'tribunal_name' => $tribunalName,
            'files_count' => $this->configuration->files->count(),
        ];
    }
} 