<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticeSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/rptGPList.csv');
        $handle = fopen($filePath, 'r');

        // Skip the very first metadata line (Date Last Modified)
        fgetcsv($handle);
        // Skip the header row
        $header = fgetcsv($handle);

        $practices = [];

        while (($data = fgetcsv($handle)) !== false) {
            $practiceId = $data[4]; // 'Practice' column

            if (! isset($practices[$practiceId])) {
                $practices[$practiceId] = [
                    'id' => $practiceId,
                    'name' => $data[6],  // Address 1
                    'address_line_1' => $data[7],  // Address 2
                    'address_line_2' => $data[8],  // Address 3
                    'city' => $data[9],  // Address 4
                    'postcode' => $data[10], // Postcode
                    'county' => $data[11], // LCG
                    'phone_number' => $data[5],  // Phone No
                    'country' => 'UK',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        fclose($handle);

        // Using chunked insert for performance
        foreach (array_chunk($practices, 100) as $chunk) {
            DB::table('practices')->insert($chunk);
        }
    }
}
