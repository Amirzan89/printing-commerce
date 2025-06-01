<?php
namespace Database\Seeders;
use App\Models\RiwayatEditor;
use Illuminate\Database\Seeder;
class RiwayatEditorSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    public function run(): void
    {
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        $idPesanan = $jsonData['pesanan'][rand(0, 99)];
        for($i = 1; $i <= 5; $i++){
            $nama = "Editor " . $i;
            $idRiwayatEditor[] = RiwayatEditor::insertGetId([
                'nama_editor' => $nama,
                'deskripsi_pengerjaan'=> 'kurangggg Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti eius dolor illo id cupiditate ea labore obcaecati odit commodi rem laboriosam, quis unde! Ipsam dignissimos temporibus molestiae minus sunt. Praesentium.',
                'revisi'=> $i,
                'id_editor' => $jsonData['editor'][rand(0, 4)],
                'id_pesanan' => $idPesanan,
            ]);
            $idRiwayatEditors[] = $idRiwayatEditor;
        }
        if(!isset($jsonData['riwayat_editor'])){
            $jsonData['riwayat_editor'] = [];
        }
        $jsonData['riwayat_editor'] = array_merge($jsonData['riwayat_editor'], $idRiwayatEditors);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}