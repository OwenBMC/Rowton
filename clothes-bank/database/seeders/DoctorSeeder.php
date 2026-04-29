<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/rptGPList.csv');
        $handle = fopen($filePath, 'r');

        // Skip the metadata line
        fgetcsv($handle);
        // Skip the header row
        fgetcsv($handle);

        $doctors = [];

        while (($data = fgetcsv($handle)) !== false) {
            $doctors[] = [
                'practice_id' => $data[4], // Practice ID
                'name' => $data[2], // GP Name
                'forename' => $data[3], // Forename
                'speciality' => 'General Practitioner',
                'phone_number' => null,     // Left null per instructions
                'email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in chunks to manage memory
            if (count($doctors) >= 500) {
                DB::table('doctors')->insert($doctors);
                $doctors = [];
            }
        }

        if (! empty($doctors)) {
            DB::table('doctors')->insert($doctors);
        }

        fclose($handle);
    }
}
