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
        for($i = 0; $i <= 2; $i++){
            $idJasa = Jasa::insertGetId([
                'uuid' =>  Str::uuid(),
                'kategori' => ['logo', 'banner', 'poster'][$i],
                'deskripsi_jasa' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla, placeat? Vitae quam error laudantium suscipit nulla soluta? Vero nobis cupiditate, quam similique nisi eligendi veniam nesciunt odit ipsam, at quis?',
            ]);
            $idJasas[] = $idJasa;
            for($l = 0; $l <= 2; $l++){
                $idPaketJasas[] = PaketJasa::insertGetId([
                    'kelas_jasa' => ['basic', 'standard', 'premium'][$l],
                    'harga_paket_jasa' => rand(10000, 100000),
                    'waktu_pengerjaan' => ['3 hari', '7 hari', '14 hari'][$l],
                    'maksimal_revisi' => rand(1, 5),
                    'deskripsi_singkat' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla, placeat? Vitae quam error laudantium suscipit nulla soluta? Vero nobis cupiditate, quam similique nisi eligendi veniam nesciunt odit ipsam, at quis?',
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