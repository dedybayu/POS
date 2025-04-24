<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangModel extends Model
{
    use HasFactory;
    protected $table = 'm_barang';
    protected $primaryKey = 'barang_id';

    protected $fillable = ['kategori_id', 'barang_nama', 'barang_kode', 'harga_beli', 'harga_jual'];

    public function kategori(): BelongsTo{
        return $this->belongsTo(KategoriModel::class, 'kategori_id', 'kategori_id');
    }

    public function stok()
    {
        return $this->hasMany(StokModel::class, 'barang_id', 'barang_id');
    }

    public function penjualan_detail()
    {
        return $this->hasMany(PenjualanDetailModel::class, 'barang_id', 'barang_id');
    }

    public function getRealStokAttribute()
{
    $stokMasuk = $this->stok()->sum('stok_jumlah');
    $stokKeluar = PenjualanDetailModel::where('barang_id', $this->barang_id)->sum('jumlah');

    return $stokMasuk - $stokKeluar;
}

}
