<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
  protected $table = 'barang';
  protected $fillable = [
    'nama_barang',
    'kode_barang',
    'harga_beli',
    'harga_jual',
    'stok_barang'
  ];
}
