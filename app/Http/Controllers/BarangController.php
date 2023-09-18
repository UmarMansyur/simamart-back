<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Utils\HttpResponse;
use Illuminate\Http\Request;

class BarangController extends Controller {
  public function __construct()
  {
    $this->rule = [
      'nama_barang' => 'required|unique:barang',
      'kode_barang' => 'required',
      'harga_beli' => 'required',
      'harga_jual' => 'required',
      'stok_barang' => 'required'
    ];
    $this->model = Barang::class;
  }

  public function index() 
  {
    try {
      return $this->__showAll(['nama_barang', 'kode_barang', 'harga_beli', 'harga_jual', 'stok_barang']);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function show($id)
  {
    try {
      return $this->__show(['id' => $id]);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function store(Request $request) 
  {
    try {
      $this->validate($request, $this->rule);
      $data = $this->model::create($request->all());
      return $data;
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function update(Request $request, $id)
  {
    try {
      return $this->__update($request->all(), ['id' => $id]);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }

  public function destroy($id)
  {
    try {
      return $this->__destroy(['id' => $id]);
    } catch (\Throwable $th) {
      return HttpResponse::error($th->getMessage());
    }
  }
}