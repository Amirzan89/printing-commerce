<?php
namespace Database\Seeders;

use App\Models\PesananRevisi;
use App\Models\RevisiUser;
use App\Models\RevisiEditor;
use App\Models\Pesanan;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RevisiSeeder extends Seeder
{
    public function run(): void
    {
        // Sample revision notes
        $userRevisionNotes = [
            'Tolong ubah warna background menjadi lebih terang',
            'Font terlalu kecil, mohon diperbesar',
            'Logo perusahaan kurang jelas, bisa diperjelas?',
            'Tata letak masih kurang rapi, mohon diperbaiki',
            'Warna teks sulit dibaca, bisa diganti?'
        ];
        
        $editorNotes = [
            'Sudah diperbaiki sesuai permintaan',
            'Preview versi terbaru',
            'Final version setelah revisi',
            'Mohon dicek kembali',
            'Hasil akhir siap cetak'
        ];
        
        // Get some pesanan for testing
        $pesananList = Pesanan::limit(10)->get();
        
        foreach ($pesananList as $pesanan) {
            // Random number of revisions (1-3)
            $numRevisions = rand(1, 3);
            
            for ($revisionNum = 1; $revisionNum <= $numRevisions; $revisionNum++) {
                $createdAt = Carbon::now()->subDays(rand(1, 15));
                
                // Create revision record
                $revision = PesananRevisi::create([
                    'urutan_revisi' => $revisionNum,
                    'id_pesanan' => $pesanan->id_pesanan,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]);
                
                // Create user files for this revision
                for ($fileNum = 1; $fileNum <= rand(1, 2); $fileNum++) {
                    RevisiUser::create([
                        'nama_file' => "user_revision_{$revisionNum}_{$fileNum}.pdf",
                        'type' => 'revisi',
                        'user_notes' => $userRevisionNotes[rand(0, count($userRevisionNotes) - 1)],
                        'uploaded_at' => $createdAt,
                        'id_revisi' => $revision->id_revisi,
                        'id_user' => $pesanan->id_user
                    ]);
                }
                
                // Create editor response (80% chance)
                if (rand(1, 10) <= 8) {
                    $responseAt = $createdAt->copy()->addHours(rand(2, 24));
                    $fileType = ($revisionNum === $numRevisions) ? 'final' : 'preview';
                    
                    RevisiEditor::create([
                        'nama_file' => "editor_response_{$revisionNum}.pdf",
                        'type' => $fileType,
                        'editor_notes' => $editorNotes[rand(0, count($editorNotes) - 1)],
                        'uploaded_at' => $responseAt,
                        'id_editor' => $pesanan->id_editor,
                        'id_revisi' => $revision->id_revisi
                    ]);
                }
            }
            
            // Update pesanan status
            $lastRevision = $pesanan->revisions()->latest('urutan_revisi')->first();
            if ($lastRevision && $lastRevision->editorFiles()->where('type', 'final')->exists()) {
                $pesanan->update(['status' => 'selesai']);
            } else {
                $pesanan->update(['status' => 'revisi']);
            }
        }
        
        // Also create some initial brief files
        foreach ($pesananList as $pesanan) {
            // Create initial brief files (before any revision)
            for ($fileNum = 1; $fileNum <= rand(1, 3); $fileNum++) {
                RevisiUser::create([
                    'nama_file' => "initial_brief_{$fileNum}.pdf",
                    'type' => 'brief',
                    'user_notes' => "File brief awal pesanan",
                    'uploaded_at' => $pesanan->created_at,
                    'id_revisi' => null,
                    'id_user' => $pesanan->id_user
                ]);
            }
            
            // Create initial editor preview (before any revision)
            if (rand(1, 10) <= 7) { // 70% chance
                RevisiEditor::create([
                    'nama_file' => "initial_preview.pdf",
                    'type' => 'preview',
                    'editor_notes' => "Preview awal sebelum revisi",
                    'uploaded_at' => $pesanan->created_at->addDays(1),
                    'id_revisi' => null,
                    'id_editor' => $pesanan->id_editor
                ]);
            }
        }
    }
} 