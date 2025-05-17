<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\MetodePembayaran;
class MetodePembayaranSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    private function dataSeeder() : array
    {
        return [
            [
                'nama_metode_pembayaran' => 'BRI',
                'no_metode_pembayaran' => '973530284542',
                'deskripsi_1'=> 'fwffw',
                'deskripsi_2'=> 'disi',
                'thumbnail' => '1.jpg',
                'icon' => '1.jpeg',
            ],
            [
                'nama_metode_pembayaran' => 'BCA',
                'no_metode_pembayaran' => '973530284542',
                'deskripsi_1'=> 'fwffw',
                'deskripsi_2'=> 'disi',
                'thumbnail' => '1.jpg',
                'icon' => '1.jpeg',
            ],
            [
                'nama_metode_pembayaran' => 'GOPAY',
                'no_metode_pembayaran' => '973530284542',
                'deskripsi_1'=> 'fwffw',
                'deskripsi_2'=> 'disi',
                'thumbnail' => '1.jpg',
                'icon' => '1.jpeg',
            ],
        ];
    }
    public function run(): void
    {
        $ids = [];
        foreach($this->dataSeeder() as $mepe){
            $ids[] = MetodePembayaran::insertGetId($mepe);
            $destinationPath = public_path('img/mepe/' . $mepe['thumbnail']);
            $directory = dirname($destinationPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            copy(database_path('seeders/resources/img/mepe/' . $mepe['thumbnail']), $destinationPath);
        }
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        if(!isset($jsonData['metode_pembayaran'])){
            $jsonData['metode_pembayaran'] = [];
        }
        $jsonData['metode_pembayaran'] = array_merge($jsonData['metode_pembayaran'], $ids);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}