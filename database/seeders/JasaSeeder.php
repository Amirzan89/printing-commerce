<?php
namespace Database\Seeders;
use App\Models\Jasa;
use App\Models\PaketJasa;
use Illuminate\Database\Seeder;
Use Illuminate\Support\Str;
use Carbon\Carbon;
class JasaSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    public function run(): void
    {
        for($i = 1; $i <= 3; $i++){
            $idJasa = Jasa::insertGetId([
                'uuid' =>  Str::uuid(),
                'thumbnail_jasa' => '/1.jpg',
                'kategori' => ['logo', 'banner', 'poster'][rand(0,2)],
            ]);
            $idJasas[] = $idJasa;
            for($l = 1; $l <= 3; $l++){
                $idPaketJasas[] = PaketJasa::insertGetId([
                    'kelas_jasa' => ['basic', 'standard', 'premium'][rand(0,2)],
                    'deskripsi_paket_jasa' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla, placeat? Vitae quam error laudantium suscipit nulla soluta? Vero nobis cupiditate, quam similique nisi eligendi veniam nesciunt odit ipsam, at quis?',
                    'harga_paket_jasa' => 20000,
                    'waktu_pengerjaan' => Carbon::now(),
                    'maksimal_revisi' => 3,
                    'fitur' => 'terserahh',
                    'id_jasa' => $idJasa,
                ]);
            }
        }
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        if(!isset($jsonData['jasa'])){
            $jsonData['jasa'] = [];
        }
        $jsonData['jasa'] = array_merge($jsonData['jasa'], $idJasas);
        if(!isset($jsonData['paket_jasa'])){
            $jsonData['paket_jasa'] = [];
        }
        $jsonData['paket_jasa'] = array_merge($jsonData['paket_jasa'], $idPaketJasas);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}