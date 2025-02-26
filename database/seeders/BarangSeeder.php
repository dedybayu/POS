<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Barang dari Supplier 1
            ['barang_id' => 1, 'kategori_id' => 1, 'barang_kode' => 'BRG001', 'barang_nama' => 'Laptop Asus ROG', 'harga_beli' => 15000000, 'harga_jual' => 17000000],
            ['barang_id' => 2, 'kategori_id' => 1, 'barang_kode' => 'BRG002', 'barang_nama' => 'Monitor LG 24 Inch', 'harga_beli' => 2500000, 'harga_jual' => 3000000],
            ['barang_id' => 3, 'kategori_id' => 1, 'barang_kode' => 'BRG003', 'barang_nama' => 'Keyboard Mechanical Razer', 'harga_beli' => 1200000, 'harga_jual' => 1500000],
            ['barang_id' => 4, 'kategori_id' => 1, 'barang_kode' => 'BRG004', 'barang_nama' => 'Mouse Logitech G502', 'harga_beli' => 800000, 'harga_jual' => 1000000],
            ['barang_id' => 5, 'kategori_id' => 1, 'barang_kode' => 'BRG005', 'barang_nama' => 'SSD NVMe 1TB Samsung', 'harga_beli' => 1800000, 'harga_jual' => 2200000],

            // Barang dari Supplier 2
            ['barang_id' => 6, 'kategori_id' => 2, 'barang_kode' => 'BRG006', 'barang_nama' => 'Kaos Polos Cotton', 'harga_beli' => 50000, 'harga_jual' => 80000],
            ['barang_id' => 7, 'kategori_id' => 2, 'barang_kode' => 'BRG007', 'barang_nama' => 'Celana Jeans Levis', 'harga_beli' => 200000, 'harga_jual' => 250000],
            ['barang_id' => 8, 'kategori_id' => 2, 'barang_kode' => 'BRG008', 'barang_nama' => 'Jaket Hoodie Adidas', 'harga_beli' => 300000, 'harga_jual' => 350000],
            ['barang_id' => 9, 'kategori_id' => 2, 'barang_kode' => 'BRG009', 'barang_nama' => 'Sepatu Sneakers Nike', 'harga_beli' => 700000, 'harga_jual' => 900000],
            ['barang_id' => 10, 'kategori_id' => 2, 'barang_kode' => 'BRG010', 'barang_nama' => 'Topi Baseball NY', 'harga_beli' => 150000, 'harga_jual' => 180000],

            // Barang dari Supplier 3
            ['barang_id' => 11, 'kategori_id' => 3, 'barang_kode' => 'BRG011', 'barang_nama' => 'Mie Instan Goreng', 'harga_beli' => 2500, 'harga_jual' => 3500],
            ['barang_id' => 12, 'kategori_id' => 3, 'barang_kode' => 'BRG012', 'barang_nama' => 'Beras Premium 5Kg', 'harga_beli' => 70000, 'harga_jual' => 80000],
            ['barang_id' => 13, 'kategori_id' => 3, 'barang_kode' => 'BRG013', 'barang_nama' => 'Susu UHT Full Cream', 'harga_beli' => 15000, 'harga_jual' => 18000],
            ['barang_id' => 14, 'kategori_id' => 3, 'barang_kode' => 'BRG014', 'barang_nama' => 'Minyak Goreng 2L', 'harga_beli' => 25000, 'harga_jual' => 30000],
            ['barang_id' => 15, 'kategori_id' => 3, 'barang_kode' => 'BRG015', 'barang_nama' => 'Gula Pasir 1Kg', 'harga_beli' => 13000, 'harga_jual' => 16000],
        ];

        DB::table('m_barang')->insert($data);
    }
}
