<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['kategori_id' => 1, 'kategori_kode' => 'ELC', 'kategori_nama' => 'Elektronik'],
            ['kategori_id' => 2, 'kategori_kode' => 'FAS', 'kategori_nama' => 'Fashion'],
            ['kategori_id' => 3, 'kategori_kode' => 'FOD', 'kategori_nama' => 'Makanan & Minuman'],
            ['kategori_id' => 4, 'kategori_kode' => 'BEA', 'kategori_nama' => 'Kecantikan'],
            ['kategori_id' => 5, 'kategori_kode' => 'SPR', 'kategori_nama' => 'Olahraga'],
        ];

        DB::table('m_kategori')->insert($data);
    }
}
