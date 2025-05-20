<?php
namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\MetodePembayaran;
use App\Models\PaketJasa;
class MetodePembayaranController extends Controller
{
    public function createMPembayaran(Request $rt){
        $v = Validator::make($rt->only('nama_metode_pembayaran', 'no_metode_pembayaran', 'deskripsi_1', 'deskripsi_2', 'thumbnail', 'icon'), [
            'nama_metode_pembayaran' => 'required|min:3|max:12',
            'no_metode_pembayaran' => 'required|min:3|max:20',
            'deskripsi_1' => 'required|max:500',
            'deskripsi_2' => 'required|max:500',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'icon' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'nama_metode_pembayaran.required' => 'Nama Metode Pembayaran wajib di isi',
            'nama_metode_pembayaran.min' => 'Nama Metode Pembayaran minimal 3 karakter',
            'nama_metode_pembayaran.max' => 'Nama Metode Pembayaran maksimal 12 karakter',
            'no_metode_pembayaran.required' => 'Nomor Metode Pembayaran wajib di isi',
            'no_metode_pembayaran.min' => 'Nomor Metode Pembayaran minimal 3 karakter',
            'no_metode_pembayaran.max' => 'Nomor Metode Pembayaran maksimal 20 karakter',
            'deskripsi_1.required' => 'Deskripsi 1 Metode Pembayaran wajib di isi',
            'deskripsi_1.max' => 'Deskripsi 1 Metode Pembayaran maksimal 500 karakter',
            'deskripsi_2.required' => 'Deskripsi 2 Metode Pembayaran wajib di isi',
            'deskripsi_2.max' => 'Deskripsi 2 Metode Pembayaran maksimal 500 karakter',
            'thumbnail.required' => 'Thumbnail wajib di isi',
            'thumbnail.image' => 'Thumbnail harus berupa gambar',
            'thumbnail.mimes' => 'Format Thumbnail Metode Pembayaran tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail.max' => 'Ukuran Thumbnail Metode Pembayaran tidak boleh lebih dari 5MB',
            'icon.required' => 'Icon Metode Pembayaran wajib di isi',
            'icon.image' => 'Icon Metode Pembayaran harus berupa gambar',
            'icon.mimes' => 'Format Icon Metode Pembayaran tidak valid. Gunakan format jpeg, png, jpg',
            'icon.max' => 'Ukuran Icon Metode Pembayaran tidak boleh lebih dari 5MB',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $ins = Metode_Pembayaran::insert([
            'nama_metode_pembayaran' => $rt->input('nama_metode_pembayaran'),
            'no_metode_pembayaran' => $rt->input('no_metode_pembayaran'),
            'deskripsi_1' => $rt->input('deskripsi_1'),
            'deskripsi_2' => $rt->input('deskripsi_2'),
            'thumbnail' => $rt->input('thumbnail'),
            'icon' => $rt->input('icon'),
        ]);
        if(!$ins){
            return response()->json(['status'=>'error','message'=>'Gagal menambahkan data Metode Pembayaran'], 500);
        }
        return response()->json(['status'=>'success','message'=>'Data Metode Pembayaran berhasil ditambahkan']);
    }
    public function updateMPembayaran(Request $rt){
        $v = Validator::make($rt->only('id_metode_pembayaran', 'nama_metode_pembayaran', 'no_metode_pembayaran', 'deskripsi_1', 'deskripsi_2', 'thumbnail', 'icon'), [
            'id_metode_pembayaran' => 'required',
            'nama_metode_pembayaran' => 'required|min:3|max:12',
            'no_metode_pembayaran' => 'required|min:3|max:20',
            'deskripsi_1' => 'required|max:500',
            'deskripsi_2' => 'required|max:500',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'icon' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'id_metode_pembayaran.required' => 'ID Metode Pembayaran wajib di isi',
            'nama_metode_pembayaran.required' => 'Nama Metode Pembayaran wajib di isi',
            'nama_metode_pembayaran.min' => 'Nama Metode Pembayaran minimal 3 karakter',
            'nama_metode_pembayaran.max' => 'Nama Metode Pembayaran maksimal 12 karakter',
            'no_metode_pembayaran.required' => 'Nomor Metode Pembayaran wajib di isi',
            'no_metode_pembayaran.min' => 'Nomor Metode Pembayaran minimal 3 karakter',
            'no_metode_pembayaran.max' => 'Nomor Metode Pembayaran maksimal 20 karakter',
            'deskripsi_1.required' => 'Deskripsi 1 Metode Pembayaran wajib di isi',
            'deskripsi_1.max' => 'Deskripsi 1 Metode Pembayaran maksimal 500 karakter',
            'deskripsi_2.required' => 'Deskripsi 2 Metode Pembayaran wajib di isi',
            'deskripsi_2.max' => 'Deskripsi 2 Metode Pembayaran maksimal 500 karakter',
            'thumbnail.required' => 'Thumbnail wajib di isi',
            'thumbnail.image' => 'Thumbnail harus berupa gambar',
            'thumbnail.mimes' => 'Format Thumbnail Metode Pembayaran tidak valid. Gunakan format jpeg, png, jpg',
            'thumbnail.max' => 'Ukuran Thumbnail Metode Pembayaran tidak boleh lebih dari 5MB',
            'icon.required' => 'Icon Metode Pembayaran wajib di isi',
            'icon.image' => 'Icon Metode Pembayaran harus berupa gambar',
            'icon.mimes' => 'Format Icon Metode Pembayaran tidak valid. Gunakan format jpeg, png, jpg',
            'icon.max' => 'Ukuran Icon Metode Pembayaran tidak boleh lebih dari 5MB',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $mepe = MetodePembayaran::select('thumbnail')->where('id_metode_pembayaran', $rt->input('id_metode_pembayaran'))->firstOrFail();
        $paketJasa = PaketJasa::where('id_metode_pembayaran', $rt->input('id_metode_pembayaran'))->firstOrFail();
        if($rt->hasFile('thumbnail')){
            $fi = $rt->file('thumbnail');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Thumbnail tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $ftd = public_path('assets3/img/metode-pembayaran/') . $mepe['thumbnail'];
            if(file_exists($ftd) && !is_dir($ftd)){
                unlink($ftd);
            }
            $ft = $fi->hashName();
            $fi->move(public_path('assets3/img/metode-pembayaran/'), $ft);
        }
        if($rt->hasFile('icon')){
            $fi = $rt->file('icon');
            if(!($fi->isValid() && in_array($fi->extension(), ['jpeg', 'png', 'jpg']))){
                return response()->json(['status'=>'error','message'=>'Format Icon tidak valid. Gunakan format jpeg, png, jpg'], 400);
            }
            $ftd = public_path('assets3/img/metode-pembayaran/') . $mepe['icon'];
            if(file_exists($ftd) && !is_dir($ftd)){
                unlink($ftd);
            }
            $fc = $fi->hashName();
            $fi->move(public_path('assets3/img/metode-pembayaran/'), $fc);
        }
        $uM = $mepe->update([
            'nama_metode_pembayaran' => $rt->input('nama_metode_pembayaran'),
            'deskripsi_1' => $rt->input('deskripsi_1'),
            'deskripsi_2' => $rt->input('deskripsi_2'),
            'thumbnail' => $rt->hasFile('thumbnail') ? $ft : $j['thumbnail'],
            'icon' => $rt->hasFile('icon') ? $fc : $j['icon'],
        ]);
        if (!$uM){
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui data Metode Pembayaran'], 500);
        }
        return response()->json(['status' =>'success','message'=>'Data Metode Pembayaran berhasil di perbarui']);
    }
    public function deleteMPembayaran(Request $rt){
        $v = Validator::make($rt->only('id_metode_pembayaran'), [
            'id_metode_pembayaran' => 'required',
        ], [
            'id_metode_pembayaran.required' => 'ID Metode Pembayaran wajib di isi',
        ]);
        if ($v->fails()){
            $errors = [];
            foreach ($v->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        MetodePembayaran::where('id_metode_pembayaran',$rt->input('id_metode_pembayaran'))->firstOrFail();
        if(!MetodePembayaran::where('id_metode_pembayaran',$rt->input('id_metode_pembayaran'))->delete()){
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus data Metode Pembayaran'], 500);
        }
        return response()->json(['status' => 'success', 'message' => 'Data Metode Pembayaran berhasil dihapus']);
    }
}