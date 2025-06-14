<?php
namespace Database\Seeders;
use App\Models\CatatanPesanan;
use App\Models\Pesanan;
use Illuminate\Database\Seeder;
class PesananCatatanSeeder extends Seeder
{
    public function run(): void
    {
        // Sample brief texts
        $briefTexts = [
            'Saya membutuhkan desain logo untuk perusahaan teknologi. Logo harus modern, minimalis, dan mencerminkan inovasi. Warna yang diinginkan adalah biru dan putih.',
            'Butuh banner untuk promosi produk makanan. Ukuran A3, dengan foto produk yang menarik dan informasi promo. Tema warna hangat seperti orange dan merah.',
            'Desain kartu nama untuk bisnis konsultan. Desain profesional, elegan, dengan informasi kontak lengkap. Preferensi warna hitam dan emas.',
            'Poster event musik untuk konser indie. Desain harus eye-catching, dengan informasi tanggal, tempat, dan lineup artis. Tema vintage atau retro.',
            'Flyer untuk grand opening restoran. Desain appetizing dengan foto makanan, promo opening, dan alamat lengkap. Warna sesuai branding restoran.',
            'Logo untuk startup fintech. Konsep trust, security, dan innovation. Warna biru navy atau hijau. Harus scalable untuk berbagai media.',
            'Desain kemasan produk skincare. Minimalis, clean, target market wanita 20-35 tahun. Warna soft pastel, dengan informasi produk yang jelas.',
            'Banner website untuk agency digital marketing. Header yang menarik, dengan call-to-action yang kuat. Responsive design untuk mobile dan desktop.',
            'Desain merchandise kaos untuk brand clothing. Artwork yang unik, target anak muda. Bisa screen printing, maksimal 3 warna.',
            'Infografis untuk presentasi bisnis. Data visualization yang mudah dipahami, professional look, dengan chart dan diagram yang menarik.'
        ];
        $sampleImages = [
            'sample_logo_ref.jpg',
            'sample_banner_ref.png', 
            'sample_card_ref.jpg',
            'sample_poster_ref.png',
            'sample_flyer_ref.jpg'
        ];
        $pesananList = Pesanan::all();
        foreach ($pesananList as $pesanan) {
            $briefText = $briefTexts[rand(0, count($briefTexts) - 1)];
            $hasImage = rand(1, 10) <= 7;
            $imageName = $hasImage ? $sampleImages[rand(0, count($sampleImages) - 1)] : null;
            CatatanPesanan::create([
                'catatan_pesanan' => $briefText,
                'gambar_referensi' => $imageName,
                'uploaded_at' => $pesanan->created_at,
                'id_pesanan' => $pesanan->id_pesanan,
                'id_user' => $pesanan->id_user
            ]);
        }
    }
}