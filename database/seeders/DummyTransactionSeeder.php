<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderBook;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Employee::all();
        $markets = Market::all();
        $customers = Customer::all();
        $items = Item::all();

        if ($sales->isEmpty() || $markets->isEmpty() || $customers->isEmpty() || $items->isEmpty()) {
            $this->command->warn('Tolong pastikan tabel Employee, Market, Customer, dan Item sudah terisi.');
            return;
        }

        $daysToGenerate = 60;
        
        $this->command->info('Mulai generate data ' . $daysToGenerate . ' hari...');
        
        $endDate = Carbon::now();
        
        DB::beginTransaction();
        
        try {
            for ($i = $daysToGenerate; $i >= 0; $i--) {
                $currentDate = $endDate->copy()->subDays($i);
                
                // 5 to 15 order books per day
                $booksCount = rand(5, 15);
                
                for ($b = 0; $b < $booksCount; $b++) {
                    $book = OrderBook::create([
                        'employee_id' => $sales->random()->id,
                        'market_id' => $markets->random()->id,
                        'book_date' => $currentDate->format('Y-m-d'),
                        'status' => 'completed',
                        'created_at' => $currentDate,
                        'updated_at' => $currentDate,
                    ]);

                    // 1 to 10 orders per book
                    $ordersCount = rand(1, 10);
                    
                    for ($o = 0; $o < $ordersCount; $o++) {
                        $order = Order::create([
                            'order_book_id' => $book->id,
                            'customer_id' => $customers->random()->id,
                            'status' => 'pending',
                            'total_calculated_weight' => 0, // will calculate later
                            'total_actual_weight' => 0,
                            'notes' => '',
                            'created_at' => $currentDate,
                            'updated_at' => $currentDate,
                        ]);

                        // 1 to 5 items per order
                        $itemsCount = rand(1, 5);
                        $orderItems = $items->random($itemsCount);
                        
                        $totalWeight = 0;
                        
                        foreach ($orderItems as $item) {
                            $qty = rand(5, 50);
                            $price = $item->price ?? rand(10000, 50000);
                            $weight = $item->weight ?? 1; // Assuming weight is 1 if null
                            
                            OrderItem::create([
                                'order_id' => $order->id,
                                'item_id' => $item->id,
                                'quantity' => $qty,
                                'price' => $price,
                                'truck_batch_label' => rand(0, 1) ? 'T' . rand(1, 5) : null,
                                'created_at' => $currentDate,
                                'updated_at' => $currentDate,
                            ]);
                            
                            $totalWeight += ($qty * $weight);
                        }
                        
                        $order->update(['total_calculated_weight' => $totalWeight]);
                    }
                }
                
                if ($i % 10 === 0) {
                    $this->command->info("Sisa " . $i . " hari...");
                }
            }
            
            DB::commit();
            $this->command->info('Selesai!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
}
