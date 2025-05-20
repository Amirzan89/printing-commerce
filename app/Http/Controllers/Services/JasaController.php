<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Jasa;
use App\Models\PaketJasa;
class JasaController extends Controller
{
    public function createJasa(Request $rt){
        $validator = Validator::make($rt->only('nama_jasa', 'deskripsi', 'link_video', 'rentang_usia', 'foto'), [
            'nama_jasa' => 'required|min:6|max:30',
            'thumbnail_jasa' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'kategori' => 'required|in:printing,desain',
            'nama_paket_jasa' => 'required|min:3|max:15',
            'deskripsi_paket_jasa' => 'required|max:500',
            'harga_paket_jasa' => 'required|integer',
            'waktu_pengerjaan' => 'required|date|max:20',
            'maksimal_revisi' => 'required|integer|max:20',
            'fitur' => 'required|max:300',
        ], [
            'nama_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_jasa.min' => 'Nama Jasa minimal 6 karakter',
            'nama_jasa.max' => 'Nama Jasa maksimal 30 karakter',
            'thumbnail_jasa.required' => 'Foto wajib di isi',
            'thumbnail_jasa.image' => 'Foto harus berupa gambar',
            'thumbnail_jasa.mimes' => 'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail_jasa.max' => 'Ukuran Thumbnail Jasa tidak boleh lebih dari 5MB',
            'kategori.required' => 'Jenis kelamin wajib di isi',
            'kategori.in' => 'Jenis kelamin harus Printing atau Desain',
            'nama_paket_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_paket_jasa.min' => 'Nama Jasa minimal 3 karakter',
            'nama_paket_jasa.max' => 'Nama Jasa maksimal 15 karakter',
            'deskripsi_paket_jasa.required' => 'Deskripsi Paket Jasa wajib di isi',
            'deskripsi_paket_jasa.max' => 'Deskripsi Paket Jasa maksimal 30 karakter',
            'harga_paket_jasa.required' => 'Harga Paket Jasa di isi',
            'harga_paket_jasa.integer'=>'Harga Paket Jasa harus berupa angka',
            'waktu_pengerjaan.required' => 'Waktu Pengerjaan wajib di isi',
            'waktu_pengerjaan.date' => 'Format Waktu Pengerjaan tidak valid',
            'maksimal_revisi.required' => 'Maksimal Revisi di isi',
            'maksimal_revisi.integer'=>'Maksimal Revisi harus berupa angka',
            'maksimal_revisi.max'=>'Maksimal Revisi maksimal 20',
            'fitur.required' => 'Fitur wajib di isi',
            'fitur.max' => 'Fitur maksimal 300 karakter',
        ]);
        if ($validator->fails()){
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $idJasa = Jasa::insertGetId([
            'nama_jasa' => $rt->input('nama_jasa'),
            'thumbnail_jasa' => $rt->input('thumbnail_jasa'),
            'kategori' => $rt->input('kategori'),
        ]);
        $ins = PaketJasa::insert([
            'nama_paket_jasa' => $rt->input('nama_paket_jasa'),
            'deskripsi_paket_jasa' => $rt->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $rt->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $rt->input('waktu_pengerjaan'),
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
        $validator = Validator::make($rt->only('id_jasa', 'nama_jasa', 'deskripsi', 'link_video', 'rentang_usia', 'foto'), [
            'id_jasa' => 'required',
            'nama_jasa' => 'required|min:6|max:30',
            'thumbnail_jasa' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'kategori' => 'required|in:printing,desain',
            'nama_paket_jasa' => 'required|min:3|max:15',
            'deskripsi_paket_jasa' => 'required|max:500',
            'harga_paket_jasa' => 'required|integer',
            'waktu_pengerjaan' => 'required|date|max:20',
            'maksimal_revisi' => 'required|integer|max:20',
            'fitur' => 'required|max:300',
        ], [
            'id_jasa.required' => 'ID Jasa wajib di isi',
            'nama_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_jasa.min' => 'Nama Jasa minimal 6 karakter',
            'nama_jasa.max' => 'Nama Jasa maksimal 30 karakter',
            'thumbnail_jasa.required' => 'Foto wajib di isi',
            'thumbnail_jasa.image' => 'Foto harus berupa gambar',
            'thumbnail_jasa.mimes' => 'Format Thumbnail Jasa tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail_jasa.max' => 'Ukuran Thumbnail Jasa tidak boleh lebih dari 5MB',
            'kategori.required' => 'Jenis kelamin wajib di isi',
            'kategori.in' => 'Jenis kelamin harus Printing atau Desain',
            'nama_paket_jasa.required' => 'Nama Jasa wajib di isi',
            'nama_paket_jasa.min' => 'Nama Jasa minimal 3 karakter',
            'nama_paket_jasa.max' => 'Nama Jasa maksimal 15 karakter',
            'deskripsi_paket_jasa.required' => 'Deskripsi Paket Jasa wajib di isi',
            'deskripsi_paket_jasa.max' => 'Deskripsi Paket Jasa maksimal 30 karakter',
            'harga_paket_jasa.required' => 'Harga Paket Jasa di isi',
            'harga_paket_jasa.integer'=>'Harga Paket Jasa harus berupa angka',
            'waktu_pengerjaan.required' => 'Waktu Pengerjaan wajib di isi',
            'waktu_pengerjaan.date' => 'Format Waktu Pengerjaan tidak valid',
            'maksimal_revisi.required' => 'Maksimal Revisi di isi',
            'maksimal_revisi.integer'=>'Maksimal Revisi harus berupa angka',
            'maksimal_revisi.max'=>'Maksimal Revisi maksimal 20',
            'fitur.required' => 'Fitur wajib di isi',
            'fitur.max' => 'Fitur maksimal 300 karakter',
        ]);
        if ($validator->fails()){
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        //check data jasa
        $jasa = Jasa::select('thumbnail_jasa')->where('id_jasa', $rt->input('id_jasa'))->firstOrFail();
        $paketJasa = PaketJasa::where('id_jasa', $rt->input('id_jasa'))->firstOrFail();
        if($rt->hasFile('thumbnail_jasa')){
            $fi = $rt->file('thumbnail_jasa');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Foto tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $ftd = public_path('assets3/img/admin/') . $jasa['thumbnail_jasa'];
            if(file_exists($ftd) && !is_dir($ftd)){
                unlink($ftd);
            }
            $fh = $fi->hashName();
            $fi->move(public_path('assets3/img/admin/'), $fh);
        }
        $updatedDetailJasa = $paketJasa::update([
            'nama_paket_jasa' => $rt->input('nama_paket_jasa'),
            'deskripsi_paket_jasa' => $rt->input('deskripsi_paket_jasa'),
            'harga_paket_jasa' => $rt->input('harga_paket_jasa'),
            'waktu_pengerjaan' => $rt->input('waktu_pengerjaan'),
            'maksimal_revisi' => $rt->input('maksimal_revisi'),
            'fitur' => $rt->input('fitur'),
            'id_jasa' => $rt->input('id_jasa'),
        ]);
        if (!$updatedDetailJasa){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Paket Jasa'], 500);
        }
        $updatedJasa = $jasa->update([
            'nama_jasa' => $rt->input('nama_jasa'),
            'thumbnail_jasa' => $rt->input('thumbnail_jasa'),
            'kategori' => $rt->input('kategori'),
        ]);
        if (!$updatedJasa){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Jasa'], 500);
        }
        return response()->json(['status' =>'success','message'=>'Data Jasa berhasil di perbarui']);
    }
    public function deleteJasa(Request $rt){
        $validator = Validator::make($rt->only('id_jasa'), [
            'id_jasa' => 'required',
        ], [
            'id_jasa.required' => 'ID Jasa wajib di isi',
        ]);
        if ($validator->fails()){
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        Jasa::where('id_jasa',$rt->input('id_jasa'))->firstOrFail();
        if(!PaketJasa::where('id_jasa',$rt->input('id_jasa'))->delete()){
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data Paket Jasa'], 500);
        }
        if(!Jasa::where('id_jasa',$rt->input('id_jasa'))->delete()){
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data Jasa'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data Jasa berhasil dihapus']);
    }
}