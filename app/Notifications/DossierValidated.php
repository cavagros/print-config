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

        return (new MailMessage)
            ->subject('Nouveau dossier à traiter')
            ->line('Un nouveau dossier a été validé et nécessite votre attention.')
            ->line('Cabinet : ' . $this->configuration->cabinetInfo->cabinet_name)
            ->line('Tribunal : ' . $this->configuration->tribunalInfo->tribunal_name)
            ->line('Nombre de fichiers : ' . $this->configuration->files->count())
            ->action('Voir le dossier', $url)
            ->line('Merci de traiter ce dossier dans les plus brefs délais.');
    }

    public function toArray($notifiable)
    {
        return [
            'configuration_id' => $this->configuration->id,
            'cabinet_name' => $this->configuration->cabinetInfo->cabinet_name,
            'tribunal_name' => $this->configuration->tribunalInfo->tribunal_name,
            'files_count' => $this->configuration->files->count(),
        ];
    }
} 