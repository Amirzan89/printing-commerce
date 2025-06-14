<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\UtilityController;
use App\Models\Admin;
use App\Models\Pesanan;
use App\Models\Editor;
use Carbon\Carbon;
class PesananController extends Controller
{
    public function showAll(Request $request){
        $status = $request->query('status', 'menunggu');
        $validStatuses = ['menunggu', 'proses', 'dikerjakan', 'revisi', 'selesai', 'dibatalkan'];
        if (!in_array($status, $validStatuses)) {
            $status = 'menunggu';
        }
        if (!$request->has('status')) {
            return redirect('/pesanan?status='.$status);
        }
        // if(in_array($status, ['menunggu', 'proses', 'dikerjakan', 'revisi'])){
        //     $orderBy = 'asc';
        // }else{
        //     $orderBy = 'desc';
        // }
        $orderBy = 'asc';
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'dataPesanan' => Pesanan::select('pesanan.uuid', 'nama_user', 'status', 'estimasi_waktu', 'nama_editor')->join('jasa', 'jasa.id_jasa', '=', 'pesanan.id_jasa')->join('users', 'users.id_user', '=', 'pesanan.id_user')->join('editor', 'editor.id_editor', '=', 'pesanan.id_editor')->orderBy('pesanan.created_at', $orderBy)->where('status', $status)->get(),
            'headerData' => UtilityController::getHeaderData(),
            'currentStatus' => $status,
        ];
        return view('page.pesanan.data',$dataShow);
    }
    public function showDetail(Request $request, $uuid){
        $pesanan = Pesanan::with([
            'toUser',
            'toJasa',
            'toEditor',
            'toPaketJasa',
            'fromCatatanPesanan',
            'revisions.userFiles',
            'revisions.editorFiles'
        ])->where('uuid', $uuid)->first();
        if (!$pesanan) {
            return redirect('/pesanan')->with('error', 'Data Pesanan tidak ditemukan');
        }
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'pesananData' => [
                'uuid' => $pesanan->uuid,
                'nama_pelanggan' => $pesanan->toUser->nama_user ?? '-',
                'jenis_jasa' => $pesanan->toJasa->nama_jasa ?? '-',
                'kelas_jasa' => $pesanan->toPaketJasa->kelas_jasa,
                'maksimal_revisi' => $pesanan->maksimal_revisi ?? 0,
                'revisi_used' => $pesanan->revisi_used,
                'sisa_revisi' => $pesanan->revisi_tersisa,
                'deskripsi' => $pesanan->deskripsi ?? '-',
                'catatan_pesanan' => $pesanan->fromCatatanPesanan,
                'revisions' => $pesanan->revisions,
                'estimasi_waktu' => [
                    'dari' => $pesanan->estimasi_waktu ? Carbon::parse($pesanan->estimasi_waktu)->format('Y-m-d') : null,
                    'sampai' => $pesanan->estimasi_waktu ? Carbon::parse($pesanan->estimasi_waktu)->addDays($pesanan->toPaketJasa->waktu_pengerjaan ?? 0)->format('Y-m-d') : null
                ],
                'editor' => [
                    'id' => $pesanan->toEditor->id_editor ?? null,
                    'nama' => $pesanan->toEditor->nama_editor ?? '-'
                ],
                'status' => ucfirst($pesanan->status)
            ],
            'headerData' => UtilityController::getHeaderData(),
            'editorList' => Editor::select('id_editor', 'nama_editor')->get()
        ];
        return view('page.pesanan.detail', $dataShow);
    }
}
?>