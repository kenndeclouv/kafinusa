<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\PushNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use App\Models\User;

class Index extends Component
{
    public string $title = '';
    public string $message = '';
    public string $target_type = 'all'; // all, role, user
    public array $target_role = [];
    public array $target_user_id = [];

    #[Computed]
    public function roleOptions()
    {
        return Role::all()->mapWithKeys(fn($role) => [$role->name => \Illuminate\Support\Str::headline($role->name)])->toArray();
    }

    #[Computed]
    public function userOptions()
    {
        return User::all()->mapWithKeys(fn($user) => [$user->id => $user->name . ' (' . $user->email . ')'])->toArray();
    }

    #[Computed]
    public function targetTypeOptions()
    {
        return [
            'all' => 'Semua Pengguna',
            'role' => 'Berdasarkan Role',
            'user' => 'Pengguna Tertentu',
        ];
    }

    public function send()
    {
        Gate::authorize('notifications:send');

        $rules = [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_type' => 'required|in:all,role,user',
        ];

        if ($this->target_type === 'role') {
            $rules['target_role'] = 'required|array|min:1';
            $rules['target_role.*'] = 'exists:roles,name';
        } elseif ($this->target_type === 'user') {
            $rules['target_user_id'] = 'required|array|min:1';
            $rules['target_user_id.*'] = 'exists:users,id';
        }

        $this->validate($rules);

        $userIds = null;
        $segments = ['Subscribed Users', 'Total Subscriptions', 'Active Users', 'All'];

        if ($this->target_type === 'role') {
            $userIds = User::role($this->target_role)->pluck('id')->toArray();
            
            if (empty($userIds)) {
                Flux::toast(heading: 'Peringatan', text: 'Tidak ada user dengan role tersebut.', variant: 'warning');
                return;
            }
        } elseif ($this->target_type === 'user') {
            $userIds = array_map('intval', $this->target_user_id);
        }

        if ($userIds !== null) {
            $response = PushNotification::send($this->title, $this->message, userIds: $userIds);
        } else {
            $response = PushNotification::send($this->title, $this->message, segments: $segments);
        }

        if ($response && $response->successful()) {
            Flux::toast(heading: 'Berhasil', text: 'Notifikasi berhasil dikirim!', variant: 'success');
            $this->reset(['title', 'message', 'target_type', 'target_role', 'target_user_id']);
            Flux::modal('send-notification-modal')->close();
        } else {
            $errorMsg = $response ? ($response->json('errors.0') ?? $response->json('error') ?? 'Periksa log Laravel untuk detail.') : 'Kredensial belum dikonfigurasi.';
            $status = $response ? $response->status() : 'Error';
            
            Flux::toast(heading: 'Gagal (' . $status . ')', text: $errorMsg, variant: 'danger');
        }
    }

    public function render()
    {
        return view('livewire.notifications.index');
    }
}
