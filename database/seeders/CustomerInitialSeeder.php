<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerInitialSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/pelanggan_awal.csv');

        if (!file_exists($filePath)) {
            $this->command->error("File {$filePath} tidak ditemukan!");
            return;
        }

        $file = fopen($filePath, 'r');
        
        // Lewati baris pertama (header: name, whatsapp_number, address, borrowed_gallons)
        fgetcsv($file);

        $insertedCount = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            // Lewati baris kosong jika ada
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $name = trim($row[0] ?? '');
            $phone = trim($row[1] ?? '');
            $address = !empty($row[2]) ? trim($row[2]) : null;
            $borrowedGallons = isset($row[3]) && is_numeric($row[3]) ? (int) $row[3] : 0;

            // Auto-fix: Jika awalan '0' hilang akibat format number spreadsheet (contoh: 81234567890)
            if (str_starts_with($phone, '8')) {
                $phone = '0' . $phone;
            }

            Customer::updateOrCreate(
                ['whatsapp_number' => $phone],
                [
                    'name'             => $name,
                    'whatsapp_number'  => $phone,
                    'address'          => $address,
                    'borrowed_gallons' => $borrowedGallons,
                ]
            );

            $insertedCount++;
        }

        fclose($file);

        $this->command->info("Berhasil mengimpor {$insertedCount} data pelanggan ke database!");
    }
}