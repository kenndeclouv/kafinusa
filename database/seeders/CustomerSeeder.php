<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Market;
use App\Models\CustomerCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data_pasar_customer.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("File data_pasar_customer.json tidak ditemukan!");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        
        $categoriesMap = [
            'Eceran' => CustomerCategory::where('code', 'ECER')->first()?->id,
            'T B B' => CustomerCategory::where('code', 'TBB')->first()?->id,
            'Grosir' => CustomerCategory::where('code', 'GROS')->first()?->id,
            'UKM' => CustomerCategory::where('code', 'UKM')->first()?->id,
        ];

        DB::beginTransaction();
        try {
            // Seed Markets first
            $marketIdMap = [];
            foreach ($data['markets'] as $market) {
                // Remove weird numbering if any
                $name = trim($market['name']);
                $code = Str::slug($name);
                
                $m = Market::firstOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'address' => '-']
                );
                
                $marketIdMap[$market['id']] = $m->id;
            }

            // Seed Customers
            $customersCount = 0;
            foreach ($data['customers'] as $customer) {
                $name = trim($customer['name']);
                // Abaikan nama yang terlalu pendek (1 huruf/simbol) atau tidak mengandung huruf/angka
                if (strlen($name) <= 1 || !preg_match('/[a-zA-Z0-9]/', $name)) {
                    continue;
                }

                $marketDbId = $marketIdMap[$customer['market_id']] ?? null;
                $catDbId = $categoriesMap[$customer['category']] ?? null;
                
                if ($marketDbId && $catDbId) {
                    Customer::firstOrCreate([
                        'market_id' => $marketDbId,
                        'customer_category_id' => $catDbId,
                        'name' => $name
                    ]);
                    $customersCount++;
                }
            }
            
            DB::commit();
            $this->command->info("Berhasil melakukan seeding: " . count($marketIdMap) . " Pasar & $customersCount Customer dari Excel.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
