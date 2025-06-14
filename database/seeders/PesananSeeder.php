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
            $now = Carbon::now();
            $idPesanan = Pesanan::insertGetId([
                'uuid' =>  Str::uuid(),
                'deskripsi' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nobis consequuntur praesentium quod delectus maiores non nostrum qui blanditiis odio optio adipisci illum dignissimos quidem iure, placeat incidunt tempore doloribus odit.',
                'status' => ['pending', 'menunggu_konfirmasi', 'dikerjakan', 'revisi', 'selesai', 'dibatalkan'][rand(0,5)],
                'status_pembayaran' => ['belum_bayar', 'menunggu_konfirmasi', 'lunas'][rand(0,2)],
                'total_harga' => 500000,
                'catatan' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus commodi id, delectus minima, nulla cumque nam dolor, error nostrum iusto quos. Quae quod vero eum alias quasi rerum asperiores fugit!',
                'estimasi_waktu' => $now,
                'maksimal_revisi' => 5,
                'id_user' => $jsonData['user'][rand(0, 40)],
                'id_jasa' => $jsonData['jasa'][rand(0, 2)],
                'id_paket_jasa' => $jsonData['paket_jasa'][rand(0, 5)],
                'id_editor' => $jsonData['editor'][rand(0, 4)],
                'created_at' => $now,
                'updated_at' => $now
            ]);
            $idPesanans[] = $idPesanan;
        }
        if(!isset($jsonData['pesanan'])){
            $jsonData['pesanan'] = [];
        }
        $jsonData['pesanan'] = array_merge($jsonData['pesanan'], $idPesanans);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}