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
use Illuminate\Support\Facades\Log;
class TransaksiController extends Controller
{
    /**
     * Admin: Confirm payment (Step 4 in manual payment flow)
     * This should be called from admin panel
     */
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->only('order_id', 'catatan_admin'), [
            'order_id' => 'required',
            'catatan_admin' => 'nullable|string|max:500'
        ], [
            'order_id.required' => 'Order ID wajib diisi',
            'catatan_admin.string' => 'Catatan admin harus berupa teks',
            'catatan_admin.max' => 'Catatan admin maksimal 500 karakter'
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->input('order_id'))->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            if ($transaksi->status_transaksi !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dalam status menunggu konfirmasi'
                ], 422);
            }

            // Update transaction to lunas
            $transaksi->update([
                'status_transaksi' => 'lunas',
                'confirmed_at' => Carbon::now(),
                'catatan_transaksi' => $request->input('catatan_admin') ?? 'Pembayaran dikonfirmasi oleh admin'
            ]);

            // Update pesanan status
            $pesanan = Pesanan::find($transaksi->id_pesanan);
            $pesanan->update([
                'status_pesanan' => 'diproses',
                'confirmed_at' => Carbon::now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil dikonfirmasi',
                'data' => [
                    'transaksi' => $transaksi->fresh(),
                    'pesanan' => $pesanan->fresh(),
                    'next_step' => 'Assign editor ke pesanan'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error confirming payment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengkonfirmasi pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Admin: Reject payment (Step 4 alternative in manual payment flow)
     */
    public function rejectPayment(Request $request)
    {
        $validator = Validator::make($request->only('order_id', 'alasan_penolakan'), [
            'order_id' => 'required',
            'alasan_penolakan' => 'required|string|max:500'
        ], [
            'order_id.required' => 'Order ID wajib diisi',
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi',
            'alasan_penolakan.string' => 'Alasan penolakan harus berupa teks',
            'alasan_penolakan.max' => 'Alasan penolakan maksimal 500 karakter'
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->input('order_id'))->first();
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ], 404);
            }
            if ($transaksi->status_transaksi !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaksi tidak dalam status menunggu konfirmasi'
                ], 422);
            }
            if($transaksi->bukti_pembayaran != null){
                Storage::disk('transaksi')->delete('bukti_pembayaran/' . $transaksi->bukti_pembayaran);
            }
            // Update transaction back to belum_bayar
            $transaksi->update([
                'status_transaksi' => 'dibatalkan',
                'bukti_pembayaran' => null,
                'waktu_pembayaran' => null,
                'alasan_penolakan' => $request->input('alasan_penolakan'),
                'expired_at' => Carbon::now()->addHours(24)
            ]);

            // Update pesanan status
            $pesanan = Pesanan::find($transaksi->id_pesanan);
            $pesanan->update([
                'status_pesanan' => 'pending',
                'alasan_reject' => $request->input('alasan_penolakan')
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil ditolak',
                'data' => [
                    'transaksi' => $transaksi->fresh(),
                    'alasan_penolakan' => $request->input('alasan_penolakan'),
                    'next_step' => 'User perlu upload ulang bukti pembayaran'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal menolak pembayaran: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak pembayaran'
            ], 500);
        }
    }
    public function delete(Request $request){
        $validator = Validator::make($request->only('order_id'), [
            'order_id' => 'required'
        ], [
            'order_id.required' => 'Order ID wajib diisi'
        ]);
        if ($validator->fails()) {
            $errors = [];
            foreach($validator->errors()->toArray() as $field => $errorMessages){
                $errors[$field] = $errorMessages[0];
                break;
            }
            return response()->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }
        $transaksi = Transaksi::where('order_id', $request->input('order_id'))->first();
        if (!$transaksi) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }   
        if($transaksi->bukti_pembayaran != null){
            Storage::disk('transaksi')->delete('bukti_pembayaran/' . $transaksi->bukti_pembayaran);
        }
        $transaksi->delete();
        return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil dihapus'], 200);
    }
} 