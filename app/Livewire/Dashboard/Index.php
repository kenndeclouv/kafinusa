<?php

namespace App\Livewire\Dashboard;

use App\Models\Customer;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderBook;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public function mount()
    {
        // Must be authenticated
        abort_unless(auth()->check(), 403);
    }

    #[Computed]
    public function stats()
    {
        $user = auth()->user();
        
        $stats = [
            'total_markets' => null,
            'total_customers' => null,
            'orders_today' => null,
            'weight_today' => null,
        ];
        
        if ($user->can('markets:read')) {
            $stats['total_markets'] = \Illuminate\Support\Facades\Cache::remember('stats_total_markets', 300, function () {
                return Market::count();
            });
        }
        
        if ($user->can('customers:read')) {
            $stats['total_customers'] = \Illuminate\Support\Facades\Cache::remember('stats_total_customers', 300, function () {
                return Customer::where('status', true)->count();
            });
        }
        
        if ($user->can('order_books:read')) {
            $today = now()->format('Y-m-d');
            
            $stats['orders_today'] = \Illuminate\Support\Facades\Cache::remember('stats_orders_today_' . $today, 300, function () use ($today) {
                return OrderBook::whereDate('book_date', $today)->withCount('orders')->get()->sum('orders_count');
            });
            
            $stats['weight_today'] = \Illuminate\Support\Facades\Cache::remember('stats_weight_today_' . $today, 300, function () use ($today) {
                return Order::whereHas('orderBook', function ($q) use ($today) {
                    $q->whereDate('book_date', $today);
                })->sum('total_calculated_weight');
            });
        }

        return $stats;
    }

    public $timeRange = '7'; // 1, 7, 14, 30, 'all'

    #[Computed]
    public function trendData()
    {
        if (!auth()->user()->can('order_books:read')) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('dash_trend_' . $this->timeRange, 300, function () {
            $days = $this->timeRange === 'all' ? 30 : (int)$this->timeRange;
            if ($days == 1) $days = 7; // Minimal 7 hari untuk melihat tren garis

            $tonnageSeries = [];
            $orderSeries = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $label = now()->subDays($i)->format('d M');
                
                $bookIds = OrderBook::whereDate('book_date', $date)->pluck('id');
                
                $weight = Order::whereIn('order_book_id', $bookIds)->sum('total_calculated_weight');
                $ordersCount = Order::whereIn('order_book_id', $bookIds)->count();
                
                $tonnageSeries[] = ['label' => $label, 'value' => $weight];
                $orderSeries[] = ['label' => $label, 'value' => $ordersCount * 50]; // Scaled for visibility in same chart, or keep separate
            }
            
            return [
                [
                    'label' => 'Tonase (kg)',
                    'color' => '#10b981', // Emerald
                    'data' => $tonnageSeries
                ],
                [
                    'label' => 'Jml Pesanan (x50)',
                    'color' => '#3b82f6', // Blue
                    'data' => $orderSeries
                ]
            ];
        });
    }

    private function getDateRangeQuery($query)
    {
        if ($this->timeRange !== 'all') {
            $days = (int)$this->timeRange;
            $query->where('created_at', '>=', now()->subDays($days));
        }
        return $query;
    }

    private function getBookDateRangeQuery($query)
    {
        if ($this->timeRange !== 'all') {
            $days = (int)$this->timeRange;
            $query->where('book_date', '>=', now()->subDays($days));
        }
        return $query;
    }

    #[Computed]
    public function topMarketsData()
    {
        if (!auth()->user()->can('order_books:read')) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('dash_top_markets_' . $this->timeRange, 300, function () {
            $query = OrderBook::query()->with('market');
            $query = $this->getBookDateRangeQuery($query);
            
            $books = $query->get();
            $marketStats = [];
            
            foreach ($books as $book) {
                $mId = $book->market_id;
                $mName = $book->market->name ?? 'Unknown';
                if (!isset($marketStats[$mName])) {
                    $marketStats[$mName] = 0;
                }
                $marketStats[$mName] += Order::where('order_book_id', $book->id)->count();
            }

            arsort($marketStats);
            $top5 = array_slice($marketStats, 0, 5, true);
            
            $data = [];
            foreach ($top5 as $name => $count) {
                $data[] = ['label' => mb_strimwidth($name, 0, 15, '...'), 'value' => $count];
            }
            return $data;
        });
    }

    #[Computed]
    public function topSalesData()
    {
        if (!auth()->user()->can('order_books:read')) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('dash_top_sales_' . $this->timeRange, 300, function () {
            $query = OrderBook::query()->with('employee');
            $query = $this->getBookDateRangeQuery($query);
            
            $books = $query->get();
            $salesStats = [];
            
            foreach ($books as $book) {
                $eId = $book->employee_id;
                $eName = $book->employee->name ?? 'Unknown';
                if (!isset($salesStats[$eName])) {
                    $salesStats[$eName] = 0;
                }
                $salesStats[$eName] += Order::where('order_book_id', $book->id)->count();
            }

            arsort($salesStats);
            $top5 = array_slice($salesStats, 0, 5, true);
            
            $data = [];
            foreach ($top5 as $name => $count) {
                $data[] = ['label' => mb_strimwidth($name, 0, 15, '...'), 'value' => $count];
            }
            return $data;
        });
    }

    #[Computed]
    public function topItemsData()
    {
        if (!auth()->user()->can('order_books:read')) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember('dash_top_items_' . $this->timeRange, 300, function () {
            $query = \App\Models\OrderItem::query()->with('item');
            $query = $this->getDateRangeQuery($query);
            
            $items = $query->selectRaw('item_id, SUM(quantity) as total_qty')
                           ->groupBy('item_id')
                           ->orderByDesc('total_qty')
                           ->limit(5)
                           ->get();
                           
            $data = [];
            foreach ($items as $item) {
                $name = $item->item->name ?? 'Unknown';
                $data[] = ['label' => mb_strimwidth($name, 0, 15, '...'), 'value' => (int)$item->total_qty];
            }
            return $data;
        });
    }

    #[Computed]
    public function myTasks()
    {
        $employeeId = auth()->user()->employee->id ?? 0;
        
        if (!$employeeId) {
            return collect();
        }

        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        return OrderBook::with(['market'])
            ->withCount('orders')
            ->where('employee_id', $employeeId)
            ->whereIn('book_date', [$today, $tomorrow])
            ->orderBy('book_date', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
