<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;

class CommandMenu extends Component
{
    public $search = '';

    #[Computed]
    public function navigationItems()
    {
        $user = auth()->user();
        
        $items = [
            [
                'label' => 'Beranda',
                'icon' => 'home',
                'route' => 'dashboard',
                'permission' => null,
                'group' => 'Navigasi Utama',
            ],
            [
                'label' => 'Buku Order',
                'icon' => 'book-open',
                'route' => 'order-books.index',
                'permission' => ['order_books:read', 'order_books:read-self'],
                'group' => 'Transaksi',
            ],
            [
                'label' => 'Pasar',
                'icon' => 'building-storefront',
                'route' => 'markets.index',
                'permission' => 'markets:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Pelanggan',
                'icon' => 'user-group',
                'route' => 'customers.index',
                'permission' => 'customers:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Pegawai',
                'icon' => 'identification',
                'route' => 'employees.index',
                'permission' => 'employees:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Jadwal Kunjungan',
                'icon' => 'calendar-days',
                'route' => 'sales-schedules.index',
                'permission' => 'sales_schedules:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Barang',
                'icon' => 'cube',
                'route' => 'items.index',
                'permission' => 'items:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Kategori Pelanggan',
                'icon' => 'users',
                'route' => 'customer-categories.index',
                'permission' => 'customer_categories:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Kategori Barang',
                'icon' => 'tag',
                'route' => 'item-categories.index',
                'permission' => 'item_categories:read',
                'group' => 'Data Master',
            ],
            [
                'label' => 'Pengguna Sistem',
                'icon' => 'users',
                'route' => 'users.index',
                'permission' => 'users:read',
                'group' => 'Hak Akses',
            ],
            [
                'label' => 'Peran (Role)',
                'icon' => 'key',
                'route' => 'roles.index',
                'permission' => 'roles:read',
                'group' => 'Hak Akses',
            ],
            [
                'label' => 'Kirim Notifikasi',
                'icon' => 'paper-airplane',
                'route' => 'notifications.index',
                'permission' => 'notifications:send',
                'group' => 'Hak Akses',
            ],
            [
                'label' => 'Kalkulator',
                'icon' => 'calculator',
                'route' => null,
                'permission' => null,
                'group' => 'Alat',
            ],
            [
                'label' => 'Pengaturan Profil',
                'icon' => 'cog',
                'route' => 'profile.edit',
                'permission' => null,
                'group' => 'Sistem',
            ],
            [
                'label' => 'Logs Viewer',
                'icon' => 'document-text',
                'route' => 'logs.index',
                'permission' => 'logs.view',
                'group' => 'Sistem / Developer',
            ],
            [
                'label' => 'System Monitor',
                'icon' => 'cpu-chip',
                'route' => 'system-monitor.index',
                'permission' => null,
                'group' => 'Sistem / Developer',
            ],
        ];

        $searchTerm = strtolower($this->search);
        
        return collect($items)->filter(function ($item) use ($user, $searchTerm) {
            if (!empty($item['permission'])) {
                $permissions = is_array($item['permission']) ? $item['permission'] : [$item['permission']];
                if (!$user->hasAnyPermission($permissions)) {
                    return false;
                }
            }

            if (!empty($searchTerm)) {
                $matchLabel = str_contains(strtolower($item['label']), $searchTerm);
                $matchGroup = str_contains(strtolower($item['group']), $searchTerm);
                return $matchLabel || $matchGroup;
            }

            return true;
        })->groupBy('group');
    }

    public function render()
    {
        return view('components.command-menu');
    }
}
