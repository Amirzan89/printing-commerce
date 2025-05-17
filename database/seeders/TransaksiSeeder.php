<?php
namespace Database\Seeders;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
class TransaksiSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    public function run(): void
    {
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        $ids = [];
        for($i = 1; $i <= 100; $i++){
            $ids[] = Transaksi::insertGetId([
                'order_id' =>  rand(0, 999999),
                'jumlah' => rand(0000000, 9999999),
                'status' => ['belum_bayar', 'menunggu_konfirmasi', 'lunas'][rand(0,2)],
                'bukti_pembayaran' => '/1.jpg',
                'waktu_pembayaran' => Carbon::now(),
                'expired_at' => Carbon::now(),
                'id_metode_pembayaran' => $jsonData['metode_pembayaran'][rand(0, 2)],
                'id_pesanan' => $jsonData['pesanan'][rand(0, 99)]
            ]);
        }
        Storage::disk('transaksi')->put('/1.jpg', file_get_contents(database_path('seeders/resources/img/transaksi/1.jpg')));
        if(!isset($jsonData['transaksi'])){
            $jsonData['transaksi'] = [];
        }
        $jsonData['transaksi'] = array_merge($jsonData['transaksi'], $ids);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}