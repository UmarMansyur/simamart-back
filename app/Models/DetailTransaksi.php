<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
  protected $table = 'detail_transaksi';
  protected $fillable = [
    'transaksi_id',
    'barang_id',
    'harga_beli',
    'harga_jual',
    'jumlah',
    'total_harga'
  ];
}
