<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Utils\HttpResponse;
use Illuminate\Http\Request;

class TransaksiController extends Controller {
  public function __construct()
  {
    $this->rule = [
      'kode_transaksi' => 'required',
      'total_harga' => 'required',
      'bayar' => 'required',
      'kembalian' => 'required'
    ];
    $this->model = Transaksi::class;
  }

  public function index() 
  {
    try {
      return $this->__showAll(['kode_transaksi', 'total_harga', 'bayar', 'kembalian']);
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