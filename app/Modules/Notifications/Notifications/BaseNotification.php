<?php

namespace App\Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (isset($this->data['type']) && $this->data['type'] === 'approval_required') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return $this->data;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'data' => $this->data,
            'read_at' => null,
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:8000'));
        $actionUrl = $this->data['action_url'] ?? '';

        if (!empty($actionUrl) && !str_starts_with($actionUrl, '/')) {
            $actionUrl = '/' . $actionUrl;
        }

        $detailUrl = rtrim($frontendUrl, '/') . $actionUrl;
        
        $name = $notifiable->employee->full_name ?? $notifiable->name ?? $notifiable->email ?? 'Pengguna';

        return (new MailMessage)
            ->subject($this->data['title'] ?? 'Pemberitahuan Sistem')
            ->view('emails.custom_notification', [
                'name' => $name,
                'data' => $this->data,
                'detailUrl' => $detailUrl
            ]);
    }
}
