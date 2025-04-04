<?php

namespace App\Notifications;

use App\Models\PrintConfiguration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderValidated extends Notification implements ShouldQueue
{
    use Queueable;

    private $configuration;

    public function __construct(PrintConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle commande validée - ' . $this->configuration->name)
            ->greeting('Bonjour,')
            ->line('Une nouvelle commande a été validée et est prête pour le paiement.')
            ->line('Détails de la commande :')
            ->line('- Nom : ' . $this->configuration->name)
            ->line('- Cabinet : ' . $this->configuration->cabinetInfo->cabinet_name)
            ->line('- Tribunal : ' . $this->configuration->tribunalInfo->tribunal_name)
            ->line('- Nombre de fichiers : ' . $this->configuration->files->count())
            ->line('- Prix total : ' . number_format($this->configuration->total_price, 2, ',', ' ') . ' €')
            ->action('Voir la commande', route('admin.configurations.show', $this->configuration))
            ->line('Merci de traiter cette commande dès que possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'configuration_id' => $this->configuration->id,
            'configuration_name' => $this->configuration->name,
            'cabinet_name' => $this->configuration->cabinetInfo->cabinet_name,
            'tribunal_name' => $this->configuration->tribunalInfo->tribunal_name,
            'total_price' => $this->configuration->total_price,
            'files_count' => $this->configuration->files->count(),
        ];
    }
} 