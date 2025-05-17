<?php
namespace Database\Seeders;
use App\Models\CatatanPesanan;
use App\Models\Pesanan;
use App\Models\PesananFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
class PesananSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    public function run(): void
    {
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        for($i = 1; $i <= 100; $i++){
            $idPesanan = Pesanan::insertGetId([
                'uuid' =>  Str::uuid(),
                'deskripsi' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nobis consequuntur praesentium quod delectus maiores non nostrum qui blanditiis odio optio adipisci illum dignissimos quidem iure, placeat incidunt tempore doloribus odit.',
                'status' => ['pending', 'proses', 'revisi', 'selesai', 'dibatalkan'][rand(0,4)],
                'status_pembayaran' => ['belum_bayar', 'menunggu_konfirmasi', 'lunas'][rand(0,2)],
                'total_harga' => 500000,
                'estimasi_waktu' => Carbon::now(),
                'jumlah_revisi' => 5,
                'id_user' => $jsonData['user'][rand(0, 40)],
                'id_jasa' => $jsonData['jasa'][rand(0, 2)],
                'id_paket_jasa' => $jsonData['paket_jasa'][rand(0, 5)],
            ]);
            $idPesanans[] = $idPesanan;
        }
        if(!isset($jsonData['pesanan'])){
            $jsonData['pesanan'] = [];
        }
        $jsonData['pesanan'] = array_merge($jsonData['pesanan'], $idPesanans);
        for($i = 1; $i <= 100; $i++){
            $idPesananFile = PesananFile::insertGetId([
                'file_path' => 'pathhhh',
                'status' => ['preview', 'final', 'revisi'][rand(0,2)],
                'uploaded_at' => Carbon::now(),
                'id_pesanan' => $jsonData['pesanan'][rand(0, 99)],
            ]);
            $idPesananFiles[] = $idPesananFile;
        }
        if(!isset($jsonData['pesanan_file'])){
            $jsonData['pesanan_file'] = [];
        }
        $jsonData['pesanan_file'] = array_merge($jsonData['pesanan_file'], $idPesananFiles);
        for($i = 1; $i <= 100; $i++){
            $idCatatanPesanans[] = CatatanPesanan::insertGetId([
                'catatan' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus commodi id, delectus minima, nulla cumque nam dolor, error nostrum iusto quos. Quae quod vero eum alias quasi rerum asperiores fugit!',
                'id_admin' => $jsonData['admin'][rand(0, 10)],
                'id_pesanan' => $jsonData['pesanan'][rand(0, 10)],
            ]);
        }
        if(!isset($jsonData['catatan_pesanan'])){
            $jsonData['catatan_pesanan'] = [];
        }
        $jsonData['catatan_pesanan'] = array_merge($jsonData['catatan_pesanan'], $idCatatanPesanans);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}