<?php

namespace App\Livewire\System;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

class AppReset extends Component
{
    public string $confirmation = '';
    public string $password = '';
    public bool $isResetting = false;

    public function mount(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasRole('superadmin'),
            403,
            'Hanya Super Admin yang diizinkan mengakses halaman reset aplikasi.'
        );
    }

    protected function getExistingTables(): array
    {
        return array_flip(array_map(function ($table) {
            return str_contains($table, '.') ? explode('.', $table, 2)[1] : $table;
        }, Schema::getTableListing()));
    }

    #[Computed]
    public function operationalCounts(): array
    {
        $existing = $this->getExistingTables();

        return [
            'orders' => isset($existing['orders']) ? DB::table('orders')->count() : 0,
            'order_items' => isset($existing['order_items']) ? DB::table('order_items')->count() : 0,
            'order_books' => isset($existing['order_books']) ? DB::table('order_books')->count() : 0,
            'shipments' => isset($existing['shipment_plans']) ? DB::table('shipment_plans')->count() : 0,
            'shipment_items' => isset($existing['shipment_plan_items']) ? DB::table('shipment_plan_items')->count() : 0,
            'stock_mutations' => isset($existing['stock_mutations']) ? DB::table('stock_mutations')->count() : 0,
            'stock_opnames' => isset($existing['stock_opnames']) ? DB::table('stock_opnames')->count() : 0,
            'productions' => isset($existing['productions']) ? DB::table('productions')->count() : 0,
            'production_results' => isset($existing['production_results']) ? DB::table('production_results')->count() : 0,
            'petis_productions' => isset($existing['petis_productions']) ? DB::table('petis_productions')->count() : 0,
            'petis_sales' => isset($existing['petis_sales']) ? DB::table('petis_sales')->count() : 0,
            'petis_opnames' => isset($existing['petis_opnames']) ? DB::table('petis_opnames')->count() : 0,
            'petis_entries' => isset($existing['petis_raw_material_entries']) ? DB::table('petis_raw_material_entries')->count() : 0,
            'monthly_backups' => isset($existing['monthly_backups']) ? DB::table('monthly_backups')->count() : 0,
        ];
    }

    #[Computed]
    public function masterCounts(): array
    {
        $existing = $this->getExistingTables();

        return [
            'markets' => isset($existing['markets']) ? DB::table('markets')->count() : 0,
            'customers' => isset($existing['customers']) ? DB::table('customers')->count() : 0,
            'warehouses' => isset($existing['warehouses']) ? DB::table('warehouses')->count() : 0,
            'teams' => isset($existing['teams']) ? DB::table('teams')->count() : 0,
            'petis_cookers' => isset($existing['petis_cookers']) ? DB::table('petis_cookers')->count() : 0,
            'employees' => isset($existing['employees']) ? DB::table('employees')->count() : 0,
            'items' => isset($existing['items']) ? DB::table('items')->count() : 0,
            'customer_categories' => isset($existing['customer_categories']) ? DB::table('customer_categories')->count() : 0,
            'item_categories' => isset($existing['item_categories']) ? DB::table('item_categories')->count() : 0,
            'sales_schedules' => isset($existing['sales_schedules']) ? DB::table('sales_schedules')->count() : 0,
            'users' => isset($existing['users']) ? DB::table('users')->count() : 0,
        ];
    }

    #[Computed]
    public function totalOperationalRecords(): int
    {
        return array_sum($this->operationalCounts);
    }

    public function executeReset(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasRole('superadmin'),
            403,
            'Hanya Super Admin yang diizinkan mengeksekusi reset aplikasi.'
        );

        $this->validate([
            'confirmation' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'confirmation.required' => 'Ketik kata "RESET" untuk mengonfirmasi.',
            'password.required' => 'Password akun Super Admin wajib diisi.',
        ]);

        if (!Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'Password akun yang Anda masukkan salah.');
            Flux::toast(
                heading: 'Password Salah',
                text: 'Password akun Anda tidak sesuai. Tindakan reset dibatalkan.',
                variant: 'danger'
            );
            return;
        }

        if (trim($this->confirmation) !== 'RESET') {
            $this->addError('confirmation', 'Ketik kata "RESET" dengan huruf kapital.');
            Flux::toast(
                heading: 'Konfirmasi Ditolak',
                text: 'Silakan ketik persis kata "RESET" (huruf kapital) untuk melanjutkan proses penghapusan.',
                variant: 'danger'
            );
            return;
        }

        $this->isResetting = true;

        try {
            $isMysql = DB::connection()->getDriverName() === 'mysql';

            try {
                // Atur timeout ketat (5 detik) agar MySQL tidak pernah hang menunggu lock
                if ($isMysql) {
                    DB::statement('SET SESSION lock_wait_timeout = 5;');
                    DB::statement('SET SESSION innodb_lock_wait_timeout = 5;');
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                } else {
                    DB::statement('PRAGMA foreign_keys = OFF;');
                }

                // Ambil daftar tabel yang ada sekaligus (menghindari puluhan query schema)
                $existingTables = $this->getExistingTables();

                // Daftar tabel operasional dan transaksi yang dikosongkan
                $tablesToReset = [
                    'order_items',
                    'orders',
                    'order_books',
                    'shipment_plan_items',
                    'shipment_plans',
                    'shipment_batches',
                    'stock_mutations',
                    'stock_opnames',
                    'warehouse_stocks',
                    'production_results',
                    'productions',
                    'petis_production_cookers',
                    'petis_productions',
                    'petis_sales',
                    'petis_opnames',
                    'petis_raw_material_entries',
                    'petis_raw_material_initial_stocks',
                    'monthly_backups',
                    'failed_jobs',
                    'jobs',
                    'job_batches',
                ];

                // Gunakan DELETE cepat (InnoDB DML level) yang kebal dari metadata lock
                foreach ($tablesToReset as $table) {
                    if (isset($existingTables[$table])) {
                        DB::table($table)->delete();

                        // Reset auto increment kembali ke 1
                        if ($isMysql) {
                            try {
                                DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1;");
                            } catch (\Throwable $ignored) {
                                // Ignore jika tabel sedang dibaca
                            }
                        }
                    }
                }

                // Reset status piutang pada data pelanggan karena riwayat pesanan sudah dikosongkan
                if (isset($existingTables['customers']) && Schema::hasColumn('customers', 'has_debt')) {
                    DB::table('customers')->update(['has_debt' => 0]);
                }
            } finally {
                // Selalu aktifkan kembali pemeriksaan foreign key
                if ($isMysql) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } else {
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
            }

            // Perbarui cache busting dashboard & bersihkan cache aplikasi secara instan (<5ms)
            Cache::forever('dashboard_cache_version', time());
            Cache::flush();

            // Reset cache permissions Spatie
            if (class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }

            $this->confirmation = '';
            $this->password = '';
            $this->modal('confirm-reset-modal')->close();

            Flux::toast(
                heading: 'Reset Aplikasi Berhasil',
                text: 'Seluruh data transaksi dan operasional telah berhasil dikosongkan. Seluruh data master di menu Database tetap aman.',
                variant: 'success'
            );
        } catch (\Throwable $e) {
            Log::error('App reset failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);

            Flux::toast(
                heading: 'Gagal Melakukan Reset',
                text: 'Terjadi kesalahan saat memproses data: ' . $e->getMessage(),
                variant: 'danger'
            );
        } finally {
            $this->isResetting = false;
        }
    }

    public function resetConfirmationFields(): void
    {
        $this->confirmation = '';
        $this->password = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.system.app-reset')
            ->title('Reset Aplikasi - Sistem');
    }
}
