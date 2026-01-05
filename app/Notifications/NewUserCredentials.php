<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCredentials extends Notification
{
    use Queueable;

    public $password;
    public $isReset;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, bool $isReset = false)
    {
        $this->password = $password;
        $this->isReset = $isReset;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isReset ? 'Réinitialisation de votre mot de passe' : 'Vos identifiants de connexion';
        $greeting = $this->isReset ? 'Bonjour,' : 'Bonjour ' . $notifiable->name . ',';
        $intro = $this->isReset
            ? 'Votre mot de passe a été réinitialisé. Voici vos nouveaux identifiants de connexion :'
            : 'Votre compte a été créé avec succès. Voici vos identifiants de connexion :';

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($intro)
            ->line('**Email :** ' . $notifiable->email)
            ->line('**Mot de passe :** ' . $this->password)
            ->line('**Rôle :** ' . ucfirst($notifiable->role))
            ->action('Se connecter', route('login'))
            ->line('Pour des raisons de sécurité, veuillez changer votre mot de passe après votre première connexion.')
            ->line('Si vous rencontrez des difficultés, veuillez contacter l\'administrateur.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
