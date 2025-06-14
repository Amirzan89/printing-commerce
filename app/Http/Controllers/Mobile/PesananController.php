<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Transaksi;
use App\Models\Jasa;
use App\Models\PaketJasa;
use App\Models\CatatanPesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PesananController extends Controller
{
    /**
     * Get all pesanan for the authenticated user with pagination
     */
    public function getAll(Request $request){
        try {
            $statusPesanan = $request->query('status_pesanan');
            $statusTransaksi = $request->query('status_transaksi');
            $query = Pesanan::select('id_pesanan', 'uuid', 'status', 'total_harga', 'estimasi_waktu', 'maksimal_revisi', 'created_at')
                ->join('transaksi', 'transaksi.id_pesanan', '=', 'pesanan.id_pesanan')
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user);
            
            // Apply status filter only if it's not 'all'
            if($statusPesanan && $statusPesanan != 'all'){
                if(!in_array($statusPesanan, ['pending', 'diproses', 'menunggu_editor', 'dibatalkan', 'selesai', 'dikerjakan', 'revisi'])){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Status pesanan tidak valid'
                    ], 400);
                }
                $query->where('status', $statusPesanan);
            }
            
            // Apply payment status filter only if it's not 'all'
            if($statusTransaksi && $statusTransaksi != 'all'){
                if(!in_array($statusTransaksi, ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'dibatalkan', 'expired'])){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Status pembayaran tidak valid'
                    ], 400);
                }
                $query->where('status_pembayaran', $statusTransaksi);
            }
            
            $pesanan = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil diambil',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving pesanan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil pesanan', 
                'data' => null
            ], 500);
        }
    }

    /**
     * Get detailed pesanan information
     */
    public function getDetail($uuid)
    {
        try {
            $pesanan = Pesanan::with([
                'fromPesananFile', 
                'fromCatatanPesanan', 
                'fromTransaksi.toMetodePembayaran',
                'toJasa',
                'toPaketJasa',
                'toEditor'
            ])
                ->where('uuid', $uuid)
                ->where('id_user', request()->user()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Detail Pesanan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail pesanan berhasil diambil',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving detail pesanan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail pesanan',
                'data' => null
            ], 500);
        }
    }

    /**
     * Create new pesanan (Step 1 in flow)
     */
    public function create(Request $request){
        try {
            $validator = Validator::make($request->only('id_jasa', 'id_paket_jasa', 'catatan_user', 'gambar_referensi', 'maksimal_revisi'), [
                'id_jasa' => 'required|exists:jasa,id_jasa',
                'id_paket_jasa' => 'required|exists:paket_jasa,id_paket_jasa',
                'catatan_user' => 'required|string|max:1000',
                'gambar_referensi' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
                'maksimal_revisi' => 'nullable|integer|min:0|max:5'
            ], [
                'id_jasa.required' => 'Pilih jasa terlebih dahulu',
                'id_jasa.exists' => 'Jasa tidak valid',
                'id_paket_jasa.required' => 'Pilih paket jasa terlebih dahulu',
                'id_paket_jasa.exists' => 'Paket jasa tidak valid',
                'catatan_user.required' => 'Catatan revisi wajib diisi',
                'catatan_user.max' => 'Catatan revisi maksimal 1000 karakter',
                'gambar_referensi.mimes' => 'Format gambar harus jpeg, png, atau jpg',
                'gambar_referensi.max' => 'Ukuran gambar maksimal 5MB'
            ]);
            if ($validator->fails()) {
                $errors = [];
                foreach($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }

            // Get jasa and paket details for pricing
            $jasa = Jasa::find($request->input('id_jasa'));
            $paketJasa = PaketJasa::find($request->input('id_paket_jasa'));
            if (!$jasa || !$paketJasa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jasa atau paket tidak ditemukan'
                ], 404);
            }
            // Calculate total price and estimation
            $estimasiWaktu = Carbon::now()->addDays($paketJasa->waktu_pengerjaan);
            $jumlahRevisi = $request->input('maksimal_revisi') ?? $paketJasa->maksimal_revisi;
            $uuid = Str::uuid();
            $idPesanan = Pesanan::insertGetId([
                'uuid' => $uuid,
                'deskripsi' => $request->catatan_user,
                'status' => 'pending',
                'total_harga' => $paketJasa->harga_paket_jasa,
                'estimasi_waktu' => $estimasiWaktu,
                'maksimal_revisi' => $jumlahRevisi,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'id_user' => User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user,
                'id_jasa' => $request->input('id_jasa'),
                'id_paket_jasa' => $request->input('id_paket_jasa')
            ]);

            // Handle image upload for revisi
            $gambarFilename = null;
            if ($request->hasFile('gambar_referensi')) {
                $file = $request->file('gambar_referensi');
                $gambarFilename = 'brief_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/revisi'), $gambarFilename);
            }

            // Create revisi record
            if($request->input('catatan_user') != null){
                CatatanPesanan::create([
                    'catatan_pesanan' => $request->input('catatan_user'),
                    'gambar_referensi' => $gambarFilename,
                    'uploaded_at' => now(),
                    'id_pesanan' => $idPesanan,
                    'id_user' => User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat. Silahkan lanjutkan ke pembayaran.',
                'data' => [
                    'id_pesanan' => $uuid,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pesanan',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel pesanan (only if pending or belum_bayar)
     */
    public function cancel(Request $request){
        try {
            $validator = Validator::make($request->only('id_pesanan'), [
                'id_pesanan' => 'required',
            ], [
                'id_pesanan.required' => 'ID pesanan wajib di isi',
            ]);
            if ($validator->fails()){
                $errors = [];
                foreach ($validator->errors()->toArray() as $field => $errorMessages){
                    $errors[$field] = $errorMessages[0];
                    break;
                }
                return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
            }
            $pesanan = Pesanan::where('uuid', $request->input('id_pesanan'))
                ->where('id_user', User::select('id_user')->where('id_auth', $request->user()->id_auth)->first()->id_user)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak ditemukan'
                ], 404);
            }

            if ($pesanan->status == 'dibatalkan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan sudah dibatalkan'
                ], 422);
            }
            if (!in_array($pesanan->status, ['pending', 'menunggu_konfirmasi'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesanan tidak dapat dibatalkan pada status ini'
                ], 422);
            }

            // Cancel related transactions
            Transaksi::where('id_pesanan', $pesanan->id_pesanan)
                ->update(['status' => 'dibatalkan']);

            $pesanan->update([
                'status' => 'dibatalkan',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibatalkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error canceling order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membatalkan pesanan',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}