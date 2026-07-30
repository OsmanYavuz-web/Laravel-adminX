<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];

    protected $listeners = ['$refresh'];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        if (!$user) {
            $this->unreadCount = 0;
            $this->notifications = [];
            return;
        }

        $this->unreadCount = Notification::forUser($user)->unread()->count();
        $this->notifications = Notification::forUser($user)->latest()->take(10)->get()->toArray();
    }

    public function markAsRead(int $id): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();
        $this->loadNotifications();
    }

    public function markAllAsRead(): void
    {
        Notification::forUser(auth()->user())->unread()->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
