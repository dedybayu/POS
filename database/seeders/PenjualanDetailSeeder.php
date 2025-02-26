<?php

namespace Database\Seeders;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PenjualanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        // Set harga untuk setiap barang (contoh)
        $hargaBarang = [
            1 => 10000, 2 => 15000, 3 => 20000, 4 => 25000, 5 => 30000,
            6 => 35000, 7 => 40000, 8 => 45000, 9 => 50000, 10 => 55000,
            11 => 60000, 12 => 65000, 13 => 70000, 14 => 75000, 15 => 80000
        ];

        // Loop untuk (10 transaksi)
        for ($penjualan_id = 1; $penjualan_id <= 10; $penjualan_id++) {
            // Pilih 3 barang untuk setiap transaksi
            for ($i = 0; $i < 3; $i++) {
                $barang_id = (($penjualan_id - 1) * 3 + $i) % 15 + 1; // Pilih barang dari 1-15
                $jumlah = rand(1, 5); // Jumlah barang dibeli secara acak antara 1-5

                $data[] = [
                    'penjualan_id' => $penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $hargaBarang[$barang_id],
                    'jumlah' => $jumlah,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        DB::table('t_penjualan_detail')->insert($data);
    }
}
