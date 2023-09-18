<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Utils\HttpResponse;
use Illuminate\Http\Request;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class DetailTransaksiController extends Controller
{
    public function __construct()
    {
        $this->rule = [
            'kode_transaksi' => 'required',
            'id_barang' => 'required',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
            'jumlah' => 'required',
            'total_harga' => 'required'
        ];
        $this->model = DetailTransaksi::class;
    }

    public function index()
    {
        try {
            return $this->__showAll(['kode_transaksi', 'id_barang', 'harga_beli', 'harga_jual', 'jumlah', 'total_harga']);
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

            foreach ($request->detail_transaksi as $detail) {
                $barang = Barang::where('id', $detail['barang_id'])->first();
                $total = $barang->stok_barang - $detail['jumlah'];
                if ($total < 0)
                    return HttpResponse::error(
                        'Stok ' . $barang->nama_barang . ' tidak cukup'
                    );
            }

            $data = Transaksi::create([
                'kode_transaksi' => $request->kode_transaksi,
                'total_harga' => $request->total_harga,
                'bayar' => $request->bayar,
                'kembalian' => $request->kembalian
            ]);

            foreach ($request->detail_transaksi as $detail) {
                DetailTransaksi::create([
                    'transaksi_id' => $data->id,
                    'barang_id' => $detail['barang_id'],
                    'harga_beli' => $detail['harga_beli'],
                    'harga_jual' => $detail['harga_jual'],
                    'jumlah' => $detail['jumlah'],
                    'total_harga' => $detail['total_harga']
                ]);

                $barang = Barang::where('id', $detail['barang_id'])->first();
                $total = $barang->stok_barang - $detail['jumlah'];
                if ($total < 0)
                    return HttpResponse::error(
                        'Stok ' . $barang->nama_barang . ' tidak cukup'
                    );
                $barang->update([
                    'stok_barang' => $barang->stok_barang - $detail['jumlah']
                ]);
            }
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

    public function report(Request $request)
    {
        try {

            if (!$request->start_date || !$request->end_date) {
                return HttpResponse::error('Tanggal awal dan akhir harus diisi');
            }

            if ($request->start_date > $request->end_date) {
                return HttpResponse::error('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
            }
            // export excel fput
            $data = DetailTransaksi::join('barang', 'barang.id', '=', 'detail_transaksi.barang_id')->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')->whereBetween('transaksi.created_at', [$request->start_date, $request->end_date])->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Laporan Transaksi');
            // merge cell
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A2', 'Tanggal: ' . $request->start_date . ' - ' . $request->end_date);
            $sheet->mergeCells('A2:G2');
            $sheet->setCellValue('A4', 'No');
            $sheet->setCellValue('B4', 'Kode Transaksi');
            $sheet->setCellValue('C4', 'Nama Barang');
            $sheet->setCellValue('D4', 'Harga Beli');
            $sheet->setCellValue('E4', 'Harga Jual');
            $sheet->setCellValue('F4', 'Jumlah');
            $sheet->setCellValue('G4', 'Total Harga');
            $no = 1;
            $row = 5;
            foreach($data as $d){
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $d->kode_transaksi);
                $sheet->setCellValue('C'.$row, $d->nama_barang);
                $sheet->setCellValue('D'.$row, $d->harga_beli);
                $sheet->setCellValue('E'.$row, $d->harga_jual);
                $sheet->setCellValue('F'.$row, $d->jumlah);
                $sheet->setCellValue('G'.$row, $d->total_harga);
                $row++;
            }
            $sheet->setCellValue('A'.$row, 'Total');
            $sheet->mergeCells('A'.$row.':C'.$row);
            $sheet->setCellValue('G'.$row, '=SUM(G5:G'.($row-1).')');
            $sheet->setCellValue('D'.$row, '=SUM(D5:D'.($row-1).')');
            $sheet->setCellValue('E'.$row, '=SUM(E5:E'.($row-1).')');
            $sheet->setCellValue('F'.$row, '=SUM(F5:F'.($row-1).')');
            // set laba
            $sheet->setCellValue('A'.($row+1), 'Laba');
            $sheet->mergeCells('A'.($row+1).':C'.($row+1));
            $sheet->setCellValue('G'.($row+1), '=SUM(G5:G'.($row-1).')-SUM(D5:D'.($row-1).')');
            $sheet->getStyle('A1:G'.$row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1:G'.$row+1)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A4:G4')->getFont()->setBold(true);
            $sheet->getStyle('A1')->getFont()->setBold(true);




            $barang = Barang::all();
            $sheet->setCellValue('A'.$row+3, 'Laporan Barang');
            $sheet->setCellValue('A'.$row+4, 'Tanggal: ' . $request->start_date . ' - ' . $request->end_date);
            $sheet->setCellValue('A'.$row+6, 'No');
            $sheet->setCellValue('B'.$row+6, 'Nama Barang');
            $sheet->setCellValue('C'.$row+6, 'Stok');
            $sheet->setCellValue('D'.$row+6, 'Harga Beli');
            $sheet->setCellValue('E'.$row+6, 'Harga Jual');
            $sheet->setCellValue('F'.$row+6, 'Total Harga Beli');
            $sheet->setCellValue('G'.$row+6, 'Total Harga Jual');
            $sheet->setCellValue('H'.$row+6, 'Total Laba');
            $no = 1;
            $row = $row+7;
            $start = $row;
            foreach($barang as $b){
                $sheet->setCellValue('A'.$row, $no++);
                $sheet->setCellValue('B'.$row, $b->nama_barang);
                $sheet->setCellValue('C'.$row, $b->stok_barang);
                $sheet->setCellValue('D'.$row, $b->harga_beli);
                $sheet->setCellValue('E'.$row, $b->harga_jual);
                // $sheet->setCellValue('F'.$row, '=SUMIF(C5:C'.($row-1).',B'.$row.',D5:D'.($row-1).')');
                $sheet->setCellValue('F'.$row, $b->harga_beli * $b->stok_barang);
                $sheet->setCellValue('G'.$row, $b->harga_jual * $b->stok_barang);
                $sheet->setCellValue('H'.$row, '=G'.$row.'-F'.$row);
                $row++;
            }

            $sheet->setCellValue('A'.$row, 'Total');
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValue('C'.$row, '=SUM(C'.$start.':C'.($row-1).')');
            $sheet->setCellValue('D'.$row, '=SUM(D'.$start.':D'.($row-1).')');
            $sheet->setCellValue('E'.$row, '=SUM(E'.$start.':E'.($row-1).')');
            $sheet->setCellValue('F'.$row, '=SUM(F'.$start.':F'.($row-1).')');
            $sheet->setCellValue('G'.$row, '=SUM(G'.$start.':G'.($row-1).')');
            $sheet->setCellValue('H'.$row, '=SUM(H'.$start.':H'.($row-1).')');
            $sheet->getStyle('A1:H'.$row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A1:H'.$row)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A4:H4')->getFont()->setBold(true);
            $sheet->getStyle('A1')->getFont()->setBold(true);

            $writer = new Xlsx($spreadsheet);
            $writer->save('laporan-transaksi.xlsx');
            // return file
            return HttpResponse::ok('Laporan berhasil diexport', ['url' => 'laporan-transaksi.xlsx']);
        } catch (\Throwable $th) {
            return HttpResponse::error($th->getMessage());
        }
    }
}
