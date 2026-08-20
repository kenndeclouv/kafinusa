<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\CustomerCategories\Index as CustomerCategoryIndex;
use App\Livewire\ItemCategories\Index as ItemCategoryIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Permissions\Index as PermissionsIndex;

// Route::view('/', 'welcome')->name('home');
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/error/{code}', function ($code) {
    return abort($code);
});

Route::view('/offline', 'offline')->name('offline');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Livewire\Dashboard\Index::class)->name('dashboard');
    
    // Categories
    Route::get('customer-categories', CustomerCategoryIndex::class)->middleware('can:customer_categories:read')->name('customer-categories.index');
    Route::get('item-categories', ItemCategoryIndex::class)->middleware('can:item_categories:read')->name('item-categories.index');
    
    // Markets
    Route::prefix('markets')->name('markets.')->group(function () {
        Route::get('/', \App\Livewire\Markets\Index::class)->middleware('can:markets:read')->name('index');
        Route::get('/{market}', \App\Livewire\Markets\Show::class)->middleware('can:markets:read')->name('show');
    });

    // Employees & Sales Schedules
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', \App\Livewire\Employees\Index::class)->middleware('can:employees:read')->name('index');
    });
    
    Route::prefix('sales-schedules')->name('sales-schedules.')->group(function () {
        Route::get('/', \App\Livewire\SalesSchedules\Index::class)->middleware('can:sales_schedules:read')->name('index');
    });

    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', \App\Livewire\Items\Index::class)->middleware('can:items:read')->name('index');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', \App\Livewire\Customers\Index::class)->middleware('can:customers:read')->name('index');
    });

    // Transaksi / Order Books
    Route::prefix('order-books')->name('order-books.')->group(function () {
        Route::get('/', \App\Livewire\OrderBooks\Index::class)->name('index');
        Route::get('{orderBook}', \App\Livewire\OrderBooks\Show::class)->name('show');
        Route::get('{orderBook}/shipments', \App\Livewire\OrderBooks\ManageShipments::class)->name('shipments');
        Route::get('{orderBook}/shipments/print', \App\Livewire\OrderBooks\PrintShipments::class)->name('shipments.print');
        Route::get('{orderBook}/shipments/notas', \App\Livewire\OrderBooks\PrintNotas::class)->name('shipments.notas');
        Route::get('{orderBook}/shipments/deliveries', \App\Livewire\OrderBooks\PrintDeliveries::class)->name('shipments.deliveries');
        Route::get('{orderBook}/unordered-customers', \App\Livewire\OrderBooks\UnorderedCustomers::class)->name('unordered-customers');
    });

    // Access Control
    Route::prefix('access')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', UsersIndex::class)->middleware('can:users:read')->name('index');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', RolesIndex::class)->middleware('can:roles:read')->name('index');
        });

        Route::get('permissions', PermissionsIndex::class)->middleware('can:permissions:read')->name('permissions.index');

        Route::get('notifications/send', \App\Livewire\Notifications\Index::class)
            ->middleware('can:notifications:send')
            ->name('notifications.index');
    });

    // Logs & System Monitor
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', \App\Livewire\Logs\Index::class)->name('index');
        Route::get('{log}', \App\Livewire\Logs\Show::class)->name('show');
    });

    Route::get('system-monitor', \App\Livewire\SystemMonitor\Index::class)->name('system-monitor.index');
});

require __DIR__.'/settings.php';
