<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Item;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    use WithPagination;

    public OrderBook $orderBook;

    public $editingOrderId = null;
    public $customer_id;
    
    // Array to hold the dynamic order items: [['item_id' => '', 'quantity' => 1]]
    public $orderItems = [];

    public function mount(OrderBook $orderBook)
    {
        abort_unless(auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']), 403, 'Unauthorized.');
        
        if (auth()->user()->hasPermissionTo('order_books:read-self') && !auth()->user()->hasPermissionTo('order_books:read')) {
            abort_if($orderBook->employee_id !== (auth()->user()->employee->id ?? 0), 403, 'Anda tidak memiliki akses ke buku order ini.');
        }

        $this->orderBook = $orderBook;
    }

    #[Computed]
    public function customers()
    {
        $existingCustomerIds = Order::where('order_book_id', $this->orderBook->id)
            ->when($this->editingOrderId, function ($query) {
                $query->where('id', '!=', $this->editingOrderId);
            })
            ->pluck('customer_id');

        return Customer::where('market_id', $this->orderBook->market_id)
            ->where('status', true)
            ->whereNotIn('id', $existingCustomerIds)
            ->get();
    }

    #[Computed]
    public function customerOptions()
    {
        return $this->customers->mapWithKeys(function ($c) {
            return [$c->id => $c->name . ($c->has_debt ? ' (ADA HUTANG)' : '')];
        })->toArray();
    }

    #[Computed]
    public function editingCustomerOptions()
    {
        if (!$this->editingOrderId || !$this->customer_id) return [];
        $c = Customer::find($this->customer_id);
        if (!$c) return [];
        return [$c->id => $c->name . ($c->has_debt ? ' (ADA HUTANG)' : '')];
    }

    #[Computed]
    public function items()
    {
        return Item::all();
    }

    #[Computed]
    public function orders()
    {
        return Order::where('order_book_id', $this->orderBook->id)
            ->with(['customer', 'orderItems.item'])
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function summary()
    {
        $cacheKey = 'order_book_summary_' . $this->orderBook->id . '_' . $this->orderBook->updated_at->timestamp;

        return \Illuminate\Support\Facades\Cache::rememberForever($cacheKey, function () {
            $orders = Order::where('order_book_id', $this->orderBook->id)
                ->with('orderItems.item')
                ->get();
                
            $totalWeight = $orders->sum('total_calculated_weight');
            
            $itemSummary = [];
            $totalItemsCount = 0;
            $totalEstimatedPrice = 0;
            
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    if (!$orderItem->item) continue;
                    $itemPrice = $orderItem->price;
                    if ($itemPrice == 0) {
                        $priceTypeKey = $orderItem->price_type ?? 'umum';
                        $itemPrice = data_get($orderItem->item, "prices.{$priceTypeKey}", 0);
                        if ($itemPrice == 0) {
                            $itemPrice = data_get($orderItem->item, 'prices.umum', 0);
                        }
                    }
                    
                    $totalEstimatedPrice += $itemPrice * $orderItem->quantity;
                    $itemId = $orderItem->item_id;
                    if (!isset($itemSummary[$itemId])) {
                        $itemSummary[$itemId] = [
                            'name' => $orderItem->item->name,
                            'quantity' => 0,
                        ];
                    }
                    $itemSummary[$itemId]['quantity'] += $orderItem->quantity;
                    $totalItemsCount += $orderItem->quantity;
                }
            }
            
            usort($itemSummary, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return [
                'totalWeight' => $totalWeight,
                'items' => $itemSummary,
                'totalItemsCount' => $totalItemsCount,
                'totalEstimatedPrice' => $totalEstimatedPrice,
            ];
        });
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->editingOrderId = null;
        $this->customer_id = null;
        $this->orderItems = [
            ['item_id' => '', 'quantity' => 1, 'price_type' => 'umum']
        ];
        $this->modal('create-order-modal')->show();
    }

    public function editOrder($id)
    {
        $this->resetValidation();
        $order = Order::with('orderItems')->findOrFail($id);
        
        $this->editingOrderId = $order->id;
        $this->customer_id = $order->customer_id;
        
        $this->orderItems = $order->orderItems->map(function ($orderItem) {
            return [
                'item_id' => $orderItem->item_id,
                'quantity' => $orderItem->quantity,
                'price_type' => $orderItem->price_type ?? 'umum',
            ];
        })->toArray();
        
        $this->modal('create-order-modal')->show();
    }

    public function addOrderItem()
    {
        $this->orderItems[] = ['item_id' => '', 'quantity' => 1, 'price_type' => 'umum'];
    }

    public function removeOrderItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems); // Re-index array
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'orderItems' => 'required|array|min:1',
            'orderItems.*.item_id' => 'required|exists:items,id',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'orderItems.*.price_type' => 'required|in:umum,promo,khusus',
        ];
    }

    public function messages()
    {
        return [
            'orderItems.*.item_id.required' => 'Barang harus dipilih.',
            'orderItems.*.quantity.required' => 'Jumlah harus diisi.',
            'orderItems.*.quantity.min' => 'Jumlah minimal 1.',
            'orderItems.*.price_type.required' => 'Tipe harga harus dipilih.',
            'orderItems.*.price_type.in' => 'Tipe harga tidak valid.',
        ];
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            // Retrieve all selected items to calculate weight
            $itemIds = collect($this->orderItems)->pluck('item_id')->toArray();
            $itemsFromDb = Item::whereIn('id', $itemIds)->get()->keyBy('id');

            $totalWeight = 0;
            foreach ($this->orderItems as $itemData) {
                $dbItem = $itemsFromDb->get($itemData['item_id']);
                if ($dbItem) {
                    $totalWeight += $dbItem->weight * $itemData['quantity'];
                }
            }

            if ($this->editingOrderId) {
                $order = Order::findOrFail($this->editingOrderId);
                $order->update([
                    'customer_id' => $this->customer_id,
                    'total_calculated_weight' => $totalWeight,
                ]);
                
                // Remove old items and re-insert
                $order->orderItems()->delete();
            } else {
                $order = Order::create([
                    'order_book_id' => $this->orderBook->id,
                    'customer_id' => $this->customer_id,
                    'total_calculated_weight' => $totalWeight,
                ]);
            }

            foreach ($this->orderItems as $itemData) {
                $dbItem = $itemsFromDb->get($itemData['item_id']);
                $price = 0;
                
                if ($dbItem && $dbItem->prices) {
                    $priceTypeKey = $itemData['price_type'] ?? 'umum';
                    $price = $dbItem->prices[$priceTypeKey] ?? 0;
                    if ($price == 0) {
                        $price = $dbItem->prices['umum'] ?? 0;
                    }
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $price,
                    'price_type' => $itemData['price_type'] ?? 'umum',
                ]);
            }
        });

        $this->modal('create-order-modal')->close();
        Flux::toast(heading: 'Success', text: $this->editingOrderId ? 'Pesanan berhasil diperbarui.' : 'Pesanan berhasil ditambahkan.', variant: 'success');
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        
        Flux::toast(heading: 'Success', text: 'Pesanan berhasil dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.order-books.show');
    }
}
