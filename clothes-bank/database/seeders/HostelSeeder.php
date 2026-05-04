<?php

namespace Database\Seeders;

use App\Models\Hostel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        // Path to your CSV file
        $filePath = database_path('data/rptHostelList.csv');

        if (!file_exists($filePath)) {
            $this->command->error("CSV file not found at $filePath");
            return;
        }

        $file = fopen($filePath, 'r');
        
        // Skip the header row
        $header = fgetcsv($file);

        // Map CSV column names to our local variables for clarity
        // CSV: Service, Primary Client Group, Location
        
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // $row[0] = Service (Name)
                // $row[1] = Primary Client Group
                // $row[2] = Location (City)
                
                DB::table('hostels')->insert([
                    'name' => $row[0],
                    'primary_client_group' => $row[1],
                    'city' => trim($row[2]),
                    'country' => 'UK',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
            $this->command->info('Hostels table seeded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding hostels: ' . $e->getMessage());
        }

        fclose($file);
    }
}