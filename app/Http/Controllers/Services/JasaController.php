<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Admin;
use App\Models\Jasa;
use App\Models\PaketJasa;
use App\Models\JasaImage;

class JasaController extends Controller
{
    public function createJasa(Request $rt){
        $v = Validator::make($rt->only('nama_jasa', 'thumbnail_jasa', 'images', 'kategori', 'kelas_jasa', 'deskripsi_paket_jasa', 'harga_paket_jasa', 'waktu_pengerjaan', 'maksimal_revisi', 'fitur'), [
            'nama_jasa' => 'required|min:6|max:30',
            'thumbnail_jasa' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'kategori' => 'required|in:printing,desain',
            'kelas_jasa' => 'required|in:basic,standard,premium',
            'deskripsi_paket_jasa' => 'required|max:500',
            'harga_paket_jasa' => 'required|integer',
            'waktu_pengerjaan' => 'required|date',
            'maksimal_revisi' => 'required|integer|max:20',
            'fitur' => 'required|max:300',
        ], [
            'nama_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_jasa.min' => 'Nama Jasa minimal 6 karakter',
            'nama_jasa.max' => 'Nama Jasa maksimal 30 karakter',
            'thumbnail_jasa.required' => 'Thumbnail Jasa wajib di isi',
            'thumbnail_jasa.image' => 'Thumbnail Jasa harus berupa gambar',
            'thumbnail_jasa.mimes' => 'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail_jasa.max' => 'Ukuran Thumbnail Jasa tidak boleh lebih dari 5MB',
            'images.*.image' => 'Gambar tambahan harus berupa gambar',
            'images.*.mimes' => 'Format Gambar tambahan tidak valid. Gunakan format jpeg, png, jpg',
            'images.*.max' => 'Ukuran Gambar tambahan tidak boleh lebih dari 5MB',
            'kategori.required' => 'Kategori wajib di isi',
            'kategori.in' => 'Kategori harus Printing atau Desain',
            'kelas_jasa.required' => 'Kelas Jasa wajib di isi',
            'kelas_jasa.in' => 'Kelas Jasa harus Basic, Standard, atau Premium',
            'deskripsi_paket_jasa.required' => 'Deskripsi Paket Jasa wajib di isi',
            'deskripsi_paket_jasa.max' => 'Deskripsi Paket Jasa maksimal 500 karakter',
            'harga_paket_jasa.required' => 'Harga Paket Jasa wajib di isi',
            'harga_paket_jasa.integer'=>'Harga Paket Jasa harus berupa angka',
            'waktu_pengerjaan.required' => 'Waktu Pengerjaan wajib di isi',
            'waktu_pengerjaan.date' => 'Format Waktu Pengerjaan tidak valid',
            'maksimal_revisi.required' => 'Maksimal Revisi wajib di isi',
            'maksimal_revisi.integer'=>'Maksimal Revisi harus berupa angka',
            'maksimal_revisi.max'=>'Maksimal Revisi maksimal 20',
            'fitur.required' => 'Fitur wajib di isi',
            'fitur.max' => 'Fitur maksimal 300 karakter',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        
        $fh = null;
        if($rt->hasFile('thumbnail_jasa')){
            $fi = $rt->file('thumbnail_jasa');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/jasa/'), $fh);
        }
        
        $idJasa = Jasa::insertGetId([
            'uuid' =>  Str::uuid(),
            'nama_jasa' => $rt->input('nama_jasa'),
            'thumbnail_jasa' => $fh,
            'kategori' => $rt->input('kategori'),
        ]);
        
        if ($rt->hasFile('images')) {
            foreach ($rt->file('images') as $image) {
                if ($image->isValid() && in_array($image->extension(), ['jpeg', 'png', 'jpg'])) {
                    $imageName = $image->hashName();
                    $image->move(public_path('assets3/img/jasa/gallery/'), $imageName);
                    
                    JasaImage::create([
                        'id_jasa' => $idJasa,
                        'image_path' => $imageName
                    ]);
                }
            }
        }
        
        // Parse and format the date for MySQL datetime
        $waktuPengerjaan = Carbon::parse($rt->input('waktu_pengerjaan'))->format('Y-m-d H:i:s');
        
        $ins = PaketJasa::insert([
            'kelas_jasa' => $rt->input('kelas_jasa'),
            'deskripsi_paket_jasa' => $rt->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $rt->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $waktuPengerjaan,
            'maksimal_revisi' => $rt->input('maksimal_revisi'),
            'fitur' => $rt->input('fitur'),
            'id_jasa' => $idJasa,
        ]);
        
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Jasa'], 500);
        }
        
        return response()->json(['status'=>'success','message'=>'Data Jasa berhasil ditambahkan']);
    }
    
    public function updateJasa(Request $rt){
        $v = Validator::make($rt->only('id_jasa', 'nama_jasa', 'thumbnail_jasa', 'images', 'kategori', 'kelas_jasa', 'deskripsi_paket_jasa', 'harga_paket_jasa', 'waktu_pengerjaan', 'maksimal_revisi', 'fitur', 'deleted_images'), [
            'id_jasa' => 'required',
            'nama_jasa' => 'required|min:6|max:30',
            'thumbnail_jasa' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'kategori' => 'required|in:printing,desain',
            'kelas_jasa' => 'required|in:basic,standard,premium',
            'deskripsi_paket_jasa' => 'required|max:500',
            'harga_paket_jasa' => 'required|integer',
            'waktu_pengerjaan' => 'required|date',
            'maksimal_revisi' => 'required|integer|max:20',
            'fitur' => 'required|max:300',
            'deleted_images' => 'nullable|string',
        ], [
            'id_jasa.required' => 'ID Jasa wajib di isi',
            'nama_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_jasa.min' => 'Nama Jasa minimal 6 karakter',
            'nama_jasa.max' => 'Nama Jasa maksimal 30 karakter',
            'thumbnail_jasa.image' => 'Thumbnail Jasa harus berupa gambar',
            'thumbnail_jasa.mimes' => 'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail_jasa.max' => 'Ukuran Thumbnail Jasa tidak boleh lebih dari 5MB',
            'images.*.image' => 'Gambar tambahan harus berupa gambar',
            'images.*.mimes' => 'Format Gambar tambahan tidak valid. Gunakan format jpeg, png, jpg',
            'images.*.max' => 'Ukuran Gambar tambahan tidak boleh lebih dari 5MB',
            'kategori.required' => 'Kategori wajib di isi',
            'kategori.in' => 'Kategori harus Printing atau Desain',
            'kelas_jasa.required' => 'Kelas Jasa wajib di isi',
            'kelas_jasa.in' => 'Kelas Jasa harus Basic, Standard, atau Premium',
            'deskripsi_paket_jasa.required' => 'Deskripsi Paket Jasa wajib di isi',
            'deskripsi_paket_jasa.max' => 'Deskripsi Paket Jasa maksimal 500 karakter',
            'harga_paket_jasa.required' => 'Harga Paket Jasa wajib di isi',
            'harga_paket_jasa.integer'=>'Harga Paket Jasa harus berupa angka',
            'waktu_pengerjaan.required' => 'Waktu Pengerjaan wajib di isi',
            'waktu_pengerjaan.date' => 'Format Waktu Pengerjaan tidak valid',
            'maksimal_revisi.required' => 'Maksimal Revisi wajib di isi',
            'maksimal_revisi.integer'=>'Maksimal Revisi harus berupa angka',
            'maksimal_revisi.max'=>'Maksimal Revisi maksimal 20',
            'fitur.required' => 'Fitur wajib di isi',
            'fitur.max' => 'Fitur maksimal 300 karakter',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        
        $jasa = Jasa::where('uuid', $rt->input('id_jasa'))->firstOrFail();
        $paketJasa = PaketJasa::where('id_jasa', $jasa->id_jasa)->firstOrFail();
        
        $thumbnailPath = $jasa->thumbnail_jasa;
        if($rt->hasFile('thumbnail_jasa')){
            $thumbnail = $rt->file('thumbnail_jasa');
            if(!($thumbnail->isValid() && in_array($thumbnail->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            
            $oldThumbnailPath = public_path('assets3/img/jasa/') . $jasa->thumbnail_jasa;
            if($jasa->thumbnail_jasa && file_exists($oldThumbnailPath) && !is_dir($oldThumbnailPath)){
                unlink($oldThumbnailPath);
            }
            
            $thumbnailPath = $thumbnail->hashName();
            $thumbnail->move(public_path('assets3/img/jasa/'), $thumbnailPath);
        }
        
        if ($rt->hasFile('images')) {
            foreach ($rt->file('images') as $image) {
                if ($image->isValid() && in_array($image->extension(), ['jpeg', 'png', 'jpg'])) {
                    $imageName = $image->hashName();
                    $image->move(public_path('assets3/img/jasa/gallery/'), $imageName);
                    
                    JasaImage::create([
                        'id_jasa' => $jasa->id_jasa,
                        'image_path' => $imageName
                    ]);
                }
            }
        }
        
        if ($rt->has('deleted_images') && !empty($rt->input('deleted_images'))) {
            $deletedImages = json_decode($rt->input('deleted_images'), true);
            if (is_array($deletedImages)) {
                foreach ($deletedImages as $imageId) {
                    $image = JasaImage::find($imageId);
                    if ($image) {
                        $imagePath = public_path('assets3/img/jasa/gallery/') . $image->image_path;
                        if (file_exists($imagePath) && !is_dir($imagePath)) {
                            unlink($imagePath);
                        }
                        $image->delete();
                    }
                }
            }
        }
        
        $jasa->update([
            'nama_jasa' => $rt->input('nama_jasa'),
            'thumbnail_jasa' => $thumbnailPath,
            'kategori' => $rt->input('kategori'),
        ]);
        
        // Parse and format the date for MySQL datetime
        $waktuPengerjaan = Carbon::parse($rt->input('waktu_pengerjaan'))->format('Y-m-d H:i:s');
        
        $paketJasa->update([
            'kelas_jasa' => $rt->input('kelas_jasa'),
            'deskripsi_paket_jasa' => $rt->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $rt->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $waktuPengerjaan,
            'maksimal_revisi' => $rt->input('maksimal_revisi'),
            'fitur' => $rt->input('fitur'),
        ]);
        
        return response()->json(['status'=>'success','message'=>'Data Jasa berhasil diupdate']);
    }
    
    public function deleteJasa(Request $rt){
        $v = Validator::make($rt->only('id_jasa'), [
            'id_jasa' => 'required',
        ], [
            'id_jasa.required' => 'ID Jasa wajib di isi',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        
        $jasa = Jasa::where('uuid', $rt->input('id_jasa'))->firstOrFail();
        
        $images = JasaImage::where('id_jasa', $jasa->id_jasa)->get();
        foreach ($images as $image) {
            $imagePath = public_path('assets3/img/jasa/gallery/') . $image->image_path;
            if (file_exists($imagePath) && !is_dir($imagePath)) {
                unlink($imagePath);
            }
        }
        
        if ($jasa->thumbnail_jasa) {
            $thumbnailPath = public_path('assets3/img/jasa/') . $jasa->thumbnail_jasa;
            if (file_exists($thumbnailPath) && !is_dir($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
        JasaImage::where('id_jasa', $jasa->id_jasa)->delete();
        PaketJasa::where('id_jasa', $jasa->id_jasa)->delete();
        $jasa->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Jasa berhasil dihapus']);
    }
}