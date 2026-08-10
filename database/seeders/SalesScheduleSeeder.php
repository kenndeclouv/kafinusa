<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalesSchedule;
use App\Models\Employee;
use App\Models\Market;

class SalesScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dayMap = [
            'SENIN' => 1,
            'SELASA' => 2,
            'RABU' => 3,
            'KAMIS' => 4,
            'JUMAT' => 5,
            'SABTU' => 6,
            'AHAD' => 0,
        ];

        // Format: [Nama Sales] => [ [Hari, Nama Pasar], ... ]
        $schedules = [
            'Andika' => [
                ['SABTU', 'WAJAK'],
                ['AHAD', 'SINGOSARI'],
                ['SENIN', 'BLIMBING'],
                ['SELASA', 'COKOLIYO'],
                ['RABU', 'GONDANG LEGI'],
                ['KAMIS', 'KEBALEN'],
            ],
            'Puji' => [
                ['SABTU', 'TUREN'],
                ['AHAD', 'WATES'],
                ['SENIN', 'BATU'],
                ['SELASA', 'KESAMBEN'],
                ['RABU', 'UKM'],
                ['KAMIS', 'WAGIR'],
            ],
            'Jeheri' => [
                ['SABTU', 'PAL'],
                ['AHAD', 'LAWANG'],
                ['SENIN', 'DINOYO'],
                ['SELASA', 'TP REJO'],
                ['RABU', 'BANTUR'],
                ['RABU', 'WONOKERTO'],
                ['KAMIS', 'PBS'],
            ],
            'Edi' => [
                ['SABTU', 'GEDOG'],
                ['AHAD', 'TUMPANG'],
                ['SENIN', 'BUNUL'],
                ['SELASA', 'WLINGI'],
                ['RABU', 'MDY'],
                ['KAMIS', 'PAKIS AJI'],
            ],
            'Wahyudi' => [
                ['SABTU', 'GAROTAN'],
                ['AHAD', 'JABUNG'],
                ['SENIN', 'PUJON'],
                ['SELASA', 'KEPANJEN'],
                ['RABU', 'TAJINAN'],
                ['KAMIS', 'KOTA2'],
            ],
            'Soleh' => [
                ['SABTU', 'DAMPIT'],
                ['AHAD', 'PAKISJERU'],
                ['SENIN', 'KARANGPLOSO'],
                ['SELASA', 'DONOMULYO'],
                ['RABU', 'GADANG'],
                ['KAMIS', 'MKS'],
            ],
        ];

        foreach ($schedules as $salesName => $salesSchedules) {
            $employee = Employee::where('name', $salesName)->first();
            if (!$employee) {
                continue;
            }

            foreach ($salesSchedules as $sch) {
                $dayName = $sch[0];
                $marketName = $sch[1];
                $dayOfWeek = $dayMap[$dayName] ?? null;

                if ($dayOfWeek === null) continue;

                // Cari pasar atau buat baru
                $market = Market::firstOrCreate(
                    ['name' => $marketName],
                    [
                        'code' => strtoupper(substr(str_replace(' ', '', $marketName), 0, 5)),
                        'address' => '-',
                    ]
                );

                SalesSchedule::firstOrCreate([
                    'employee_id' => $employee->id,
                    'market_id' => $market->id,
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }
    }
}
