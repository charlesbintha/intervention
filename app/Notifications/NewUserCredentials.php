<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
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
        $greeting = $this->isReset ? 'Bonjour,' : 'Bonjour '.$notifiable->name.',';
        $intro = $this->isReset
            ? 'Votre mot de passe a été réinitialisé. Voici vos nouveaux identifiants de connexion :'
            : 'Votre compte a été créé avec succès. Voici vos identifiants de connexion :';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.user-credentials', [
                'subject' => $subject,
                'greeting' => $greeting,
                'intro' => $intro,
                'user' => $notifiable,
                'password' => $this->password,
                'loginUrl' => route('login'),
            ]);
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
