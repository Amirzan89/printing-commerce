<?php

namespace App\Http\Controllers\Services;
use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DataTables;
use Excel;
use Illuminate\Support\Facades\Validator;
use App\Exports\TransaksiExport;

class TransaksiController extends Controller
{

    public function validateTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'status' => 'required|in:lunas,belum_bayar,batal',
        ]);

        if($validator->fails()){
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            if ($transaksi->status !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dapat divalidasi. Status saat ini: ' . $transaksi->status
                ], 400);
            }
            
            $transaksi->update([
                'order_id' => 'TRX-' . $transaksi->order_id,
                'status' => $request->status
            ]);
            
            Pesanan::where('id_pesanan', $transaksi->id_pesanan)->update(['status_pembayaran' => $request->status]);
            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi divalidasi berhasil'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memvalidasi transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request)
    {
        // Validate request
        $validator = \Validator::make($request->all(), [
            'id_transaksi' => 'required|exists:transaksi,id',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'no_telpon' => 'required|string|regex:/^08[0-9]{9,11}$/',
            'email' => 'required|email',
            'status' => 'required|in:Menunggu Pembayaran,Proses,Selesai,Dibatalkan',
            'tanggal' => 'required|date',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'id_transaksi.required' => 'ID Transaksi diperlukan',
            'id_transaksi.exists' => 'ID Transaksi tidak valid',
            'nama_lengkap.required' => 'Nama Lengkap harus diisi',
            'jenis_kelamin.required' => 'Jenis Kelamin harus diisi',
            'jenis_kelamin.in' => 'Jenis Kelamin harus Laki-laki atau Perempuan',
            'no_telpon.required' => 'Nomor Telepon harus diisi',
            'no_telpon.regex' => 'Nomor Telepon harus dimulai dengan 08 dan terdiri dari 11-13 digit',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format Email tidak valid',
            'status.required' => 'Status Transaksi harus diisi',
            'status.in' => 'Status Transaksi tidak valid',
            'tanggal.required' => 'Tanggal Transaksi harus diisi',
            'tanggal.date' => 'Format Tanggal Transaksi tidak valid',
            'bukti_pembayaran.image' => 'File harus berupa gambar',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, atau jpg',
            'bukti_pembayaran.max' => 'Ukuran gambar maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 200);
        }

        try {
            // Find the transaction
            $transaksi = Transaksi::find($request->id_transaksi);
            
            if (!$transaksi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 200);
            }

            // Get the related pesanan
            $pesanan = $transaksi->toPesanan;
            if (!$pesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan terkait tidak ditemukan',
                ], 200);
            }

            // Get the user
            $user = $pesanan->toUser;
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User terkait tidak ditemukan',
                ], 200);
            }

            // Map status from UI to database format
            $statusMap = [
                'Menunggu Pembayaran' => 'belum_bayar',
                'Proses' => 'menunggu_konfirmasi',
                'Selesai' => 'lunas',
                'Dibatalkan' => 'dibatalkan'
            ];

            // Update user information
            $user->nama_user = $request->nama_lengkap;
            $user->jenis_kelamin = $request->jenis_kelamin;
            $user->no_telpon = $request->no_telpon;
            $user->email = $request->email;
            $user->save();

            // Update transaction status
            $transaksi->status = $statusMap[$request->status] ?? $request->status;
            
            // Handle bukti_pembayaran upload if provided
            if ($request->hasFile('bukti_pembayaran')) {
                $file = $request->file('bukti_pembayaran');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = 'img/bukti_pembayaran/';
                
                // Create directory if it doesn't exist
                if (!file_exists(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }
                
                // Move the file
                $file->move(public_path($path), $filename);
                
                // Delete old file if exists
                if ($transaksi->bukti_pembayaran && file_exists(public_path($transaksi->bukti_pembayaran))) {
                    unlink(public_path($transaksi->bukti_pembayaran));
                }
                
                $transaksi->bukti_pembayaran = $path . $filename;
                
                // If uploading bukti_pembayaran and status is still 'belum_bayar',
                // update it to 'menunggu_konfirmasi'
                if ($transaksi->status == 'belum_bayar') {
                    $transaksi->status = 'menunggu_konfirmasi';
                }
                
                // Set payment time
                $transaksi->waktu_pembayaran = now();
            }
            
            // Update transaction date
            $transaksi->updated_at = Carbon::parse($request->tanggal);
            $transaksi->save();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui',
                'data' => $transaksi
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 200);
        }
    }
    public function exportTransactions(Request $request)
    {
        try {
            $fileName = 'transactions_' . date('Y-m-d') . '.xlsx';
            
            // Filter parameters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $status = $request->input('status');
            
            return Excel::download(new TransaksiExport($startDate, $endDate, $status), $fileName);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export transactions: ' . $e->getMessage());
        }
    }
} 