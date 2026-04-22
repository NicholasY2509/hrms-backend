<?php

namespace App\Modules\UnpaidLeave\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UnpaidLeaveApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $unpaidLeave;

    public function __construct($unpaidLeave)
    {
        $this->unpaidLeave = $unpaidLeave;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; 
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:8000'));
        $detailUrl = $frontendUrl . '/unpaid_leaves/' . $this->unpaidLeave->id;

        return (new MailMessage)
            ->subject('Approval Request: Data Pengajuan Izin')
            ->greeting('Hello, ' . ($notifiable->full_name ?? $notifiable->name))
            ->line("Anda memiliki permintaan persetujuan untuk cuti karyawan atas nama " . $this->unpaidLeave->employee->full_name)
            ->action('View Approval', $detailUrl)
            ->line('Harap tinjau dan lakukan tindakan yang diperlukan.')
            ->line('Permintaan ini harus disetujui dalam waktu 7 hari, sebelum ' . Carbon::now()->addDays(7)->format('d-m-Y') . '.');
    }

    /**
     * Send notification via database (app)
     */
    public function toArray($notifiable): array
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:8000'));
        $detailUrl = $frontendUrl . '/unpaid_leaves/' . $this->unpaidLeave->id;

        return [
            'message' => "Anda memiliki permintaan persetujuan untuk cuti karyawan atas nama " . $this->unpaidLeave->employee->full_name,
            'action_url' => $detailUrl,
            'unpaid_leave_id' => $this->unpaidLeave->id,
        ];
    }
}
