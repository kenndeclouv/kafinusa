<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MonthlyBackup;
use App\Http\Middleware\CheckMonthlyBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OrderBookAutoLockTest extends TestCase
{
    use RefreshDatabase;
    public function test_auto_lock_only_triggers_on_or_after_day_3()
    {
        MonthlyBackup::where('month', '2026-10')->delete();
        Cache::forget('monthly_backup_processed_2026-10');
        Cache::forget('is_locked_2026-10');
        Cache::forget('all_locked_months');

        $middleware = new CheckMonthlyBackup();
        $request = Request::create('/', 'GET');

        // Tanggal 1: Belum boleh dikunci
        Carbon::setTestNow('2026-11-01 10:00:00');
        $middleware->handle($request, fn($r) => response('ok'));
        $this->assertFalse(MonthlyBackup::where('month', '2026-10')->exists(), 'Bulan lalu tidak boleh terkunci di tanggal 1');

        // Tanggal 2: Belum boleh dikunci
        Carbon::setTestNow('2026-11-02 23:59:59');
        $middleware->handle($request, fn($r) => response('ok'));
        $this->assertFalse(MonthlyBackup::where('month', '2026-10')->exists(), 'Bulan lalu tidak boleh terkunci di tanggal 2');

        // Tanggal 3: Baru otomatis terkunci
        Carbon::setTestNow('2026-11-03 00:00:01');
        $middleware->handle($request, fn($r) => response('ok'));
        $this->assertTrue(MonthlyBackup::where('month', '2026-10')->exists(), 'Bulan lalu harus otomatis terkunci di tanggal 3');

        // Cleanup
        MonthlyBackup::where('month', '2026-10')->delete();
        Cache::forget('monthly_backup_processed_2026-10');
        Cache::forget('is_locked_2026-10');
        Cache::forget('all_locked_months');
        Carbon::setTestNow();
    }

    public function test_order_books_index_defaults_to_previous_month_on_day_1_and_2_if_empty()
    {
        $user = \App\Models\User::factory()->create();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'order_books:read']);
        $user->givePermissionTo('order_books:read');
        $this->actingAs($user);

        $market = \App\Models\Market::create(['name' => 'Pasar A', 'code' => 'PSA']);
        $employee = \App\Models\Employee::create([
            'employee_id_number' => 'EMP001',
            'name' => 'Driver 1',
            'phone_number' => '08123456789',
            'position' => 'driver',
            'status' => true,
        ]);

        // Order book tanggal 31 Oktober
        \App\Models\OrderBook::create([
            'market_id' => $market->id,
            'employee_id' => $employee->id,
            'book_date' => '2026-10-31',
            'status' => 'draft',
        ]);

        // Simulasikan tanggal 1 November: bulan November belum ada order book
        Carbon::setTestNow('2026-11-01 08:00:00');

        \Livewire\Livewire::test(\App\Livewire\OrderBooks\Index::class)
            ->assertSet('filterMonth', '2026-10')
            ->assertSee('Pasar A');

        // Pastikan order book 31 Oktober tidak terkunci pada tanggal 1 & 2
        $this->assertFalse(MonthlyBackup::isLocked('2026-10-31'));

        // Simulasikan tanggal 3 November: auto-lock berjalan
        Carbon::setTestNow('2026-11-03 08:00:00');
        (new CheckMonthlyBackup())->handle(Request::create('/', 'GET'), fn($r) => response('ok'));
        $this->assertTrue(MonthlyBackup::isLocked('2026-10-31'));

        Carbon::setTestNow();
    }
}
