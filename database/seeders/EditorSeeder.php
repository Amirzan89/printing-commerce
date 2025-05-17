<?php
namespace Database\Seeders;
use App\Models\Editor;
use App\Models\RiwayatEditor;
use Illuminate\Database\Seeder;
class EditorSeeder extends Seeder
{
    private static $tempFile;
    public function __construct(){
        self::$tempFile = database_path('seeders/temp/table.json');
    }
    public function run(): void
    {
        $jsonData = json_decode(file_get_contents(self::$tempFile), true);
        for($i = 1; $i <= 5; $i++){
            $nama = "Editor " . $i . "@gmail.com";
            $idEditor = Editor::insertGetId([
                'nama_editor' => $nama,
                'jenis_kelamin' => ['laki-laki', 'perempuan'][rand(0, 1)],
                'no_telpon' => '0855'.mt_rand(00000000,99999999),
            ]);
            $idEditors[] = $idEditor;
            $idPesanan = $jsonData['pesanan'][rand(0, 99)];
            for($l = 1; $l <= 3; $l++){
                $ids[] = RiwayatEditor::insertGetId([
                    'nama_editor' => $nama,
                    'deskripsi_pengerjaan'=> 'kurangggg Lorem ipsum dolor sit amet consectetur adipisicing elit. Deleniti eius dolor illo id cupiditate ea labore obcaecati odit commodi rem laboriosam, quis unde! Ipsam dignissimos temporibus molestiae minus sunt. Praesentium.',
                    'revisi'=> $l,
                    'id_editor' => $idEditor,
                    'id_pesanan' => $idPesanan,
                ]);
            }
        }
        if(!isset($jsonData['editor'])){
            $jsonData['editor'] = [];
        }
        $jsonData['editor'] = array_merge($jsonData['editor'], $idEditors);
        if(!isset($jsonData['riwayat_editor'])){
            $jsonData['riwayat_editor'] = [];
        }
        $jsonData['riwayat_editor'] = array_merge($jsonData['riwayat_editor'], $ids);
        file_put_contents(self::$tempFile,json_encode($jsonData, JSON_PRETTY_PRINT));
    }
}