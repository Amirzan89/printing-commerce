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
    /**
     * Get all transactions with DataTables integration
     */
    public function getAllTransactions(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaksi::with(['toPesanan', 'toMetodePembayaran'])
                ->select('transaksi.*')
                ->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('pesanan', function ($row) {
                    return $row->toPesanan ? $row->toPesanan->deskripsi : 'N/A';
                })
                ->addColumn('user', function ($row) {
                    if ($row->toPesanan && $row->toPesanan->toUser) {
                        return $row->toPesanan->toUser->name;
                    }
                    return 'N/A';
                })
                ->addColumn('metode_pembayaran', function ($row) {
                    return $row->toMetodePembayaran ? $row->toMetodePembayaran->nama_metode_pembayaran : 'N/A';
                })
                ->addColumn('status_label', function ($row) {
                    $status = $row->status;
                    $label = '';
                    
                    if ($status == 'belum_bayar') {
                        $label = '<span class="badge badge-warning">Belum Bayar</span>';
                    } elseif ($status == 'menunggu_konfirmasi') {
                        $label = '<span class="badge badge-info">Menunggu Konfirmasi</span>';
                    } elseif ($status == 'lunas') {
                        $label = '<span class="badge badge-success">Lunas</span>';
                    }
                    
                    return $label;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<div class="btn-group" role="group">';
                    $actionBtn .= '<a href="' . url('transaksi/detail/' . $row->order_id) . '" class="btn btn-sm btn-info">Detail</a>';
                    
                    if ($row->status == 'menunggu_konfirmasi') {
                        $actionBtn .= '<button data-id="' . $row->order_id . '" class="btn btn-sm btn-success confirm-payment-btn">Konfirmasi</button>';
                    }
                    
                    $actionBtn .= '</div>';
                    
                    return $actionBtn;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }
    }

    /**
     * Update transaction status (confirm payment)
     */
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            // Only transactions with status 'menunggu_konfirmasi' can be confirmed
            if ($transaksi->status !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction cannot be confirmed. Current status: ' . $transaksi->status
                ], 400);
            }
            
            // Update transaction status
            $transaksi->update([
                'status' => 'lunas'
            ]);
            
            // Update pesanan status
            Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->update(['status_pembayaran' => 'lunas']);
                        
            return response()->json([
                'status' => 'success',
                'message' => 'Payment confirmed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject payment (if proof is invalid)
     */
    public function rejectPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:transaksi,order_id',
            'rejection_reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transaksi = Transaksi::where('order_id', $request->order_id)->first();
            
            // Only transactions with status 'menunggu_konfirmasi' can be rejected
            if ($transaksi->status !== 'menunggu_konfirmasi') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction cannot be rejected. Current status: ' . $transaksi->status
                ], 400);
            }
            
            // Update transaction status back to belum_bayar
            $transaksi->update([
                'status' => 'belum_bayar',
                'bukti_pembayaran' => null // Remove the invalid proof
            ]);
            
            // Update pesanan status
            Pesanan::where('id_pesanan', $transaksi->id_pesanan)
                ->update(['status_pembayaran' => 'belum_bayar']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment rejected successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction statistics for dashboard
     */
    public function getTransactionStats()
    {
        try {
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            
            $stats = [
                'total_transactions' => Transaksi::count(),
                'pending_confirmation' => Transaksi::where('status', 'menunggu_konfirmasi')->count(),
                'confirmed_today' => Transaksi::where('status', 'lunas')
                    ->whereDate('updated_at', $today)
                    ->count(),
                'monthly_revenue' => Transaksi::where('status', 'lunas')
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->sum('jumlah'),
                'expired_transactions' => Transaksi::where('status', 'belum_bayar')
                    ->where('expired_at', '<', Carbon::now())
                    ->count()
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export transactions to Excel
     */
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

    /**
     * Get transaction details (admin view)
     */
    public function getTransactionDetail($orderId)
    {
        try {
            $transaksi = Transaksi::with(['toMetodePembayaran', 'toPesanan'])
                ->where('order_id', $orderId)
                ->first();
                
            if (!$transaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Get user information
            $user = null;
            if ($transaksi->toPesanan && $transaksi->toPesanan->toUser) {
                $user = $transaksi->toPesanan->toUser;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction' => $transaksi,
                    'order' => $transaksi->toPesanan,
                    'payment_method' => $transaksi->toMetodePembayaran,
                    'user' => $user,
                    'payment_proof_url' => $transaksi->bukti_pembayaran ? 
                        Storage::disk('transaksi')->url($transaksi->bukti_pembayaran) : null,
                    'is_expired' => Carbon::now()->isAfter($transaksi->expired_at)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter transactions by various parameters
     */
    public function filterTransactions(Request $request)
    {
        try {
            $query = Transaksi::with(['toPesanan', 'toMetodePembayaran']);
            
            // Apply filters if provided
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_id', 'like', "%{$search}%")
                      ->orWhereHas('toPesanan', function($pesananQuery) use ($search) {
                          $pesananQuery->where('deskripsi', 'like', "%{$search}%");
                      })
                      ->orWhereHas('toPesanan.toUser', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            // Order by created_at descending by default
            $query->orderBy('created_at', 'desc');
            
            // Paginate results
            $perPage = $request->has('per_page') ? $request->per_page : 15;
            $transactions = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to filter transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 