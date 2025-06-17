<?php
namespace App\Http\Controllers\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\UtilityController;
use App\Models\Admin;
use App\Models\Chat;
use App\Services\ChatService;
use App\Models\User;
use App\Models\Order;
use App\Models\Pesanan;

class ChatController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    
    public function index()
    {
        // Tampilkan halaman index chat
        return view('page.chat.index');
    }

    public function show($uuid)
    {
        // Tampilkan halaman detail chat
        return view('page.chat.detail');
    }

    public function showAll(Request $request)
    {
        // Ambil semua user yang memiliki chat
        $users = User::whereHas('pesanan')->get();
        
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'users' => $users
        ];
        
        return view('page.chat.index', $dataShow);
    }
    
    public function showDetail(Request $request, $uuid)
    {
        // Ambil data user dan pesanan
        $user = User::where('uuid', $uuid)->first();
        $pesanan = Pesanan::where('user_uuid', $uuid)->latest()->first();
        
        if (!$user || !$pesanan) {
            return redirect()->route('chat.index')->with('error', 'User atau pesanan tidak ditemukan');
        }
        
        // Ambil pesan chat dari database
        $messages = Chat::where('pesanan_uuid', $pesanan->uuid)
                        ->orderBy('created_at', 'asc')
                        ->get();
        
        $dataShow = [
            'userAuth' => array_merge(Admin::where('id_auth', $request->user()['id_auth'])->first()->toArray(), ['role' => $request->user()['role']]),
            'user' => $user,
            'pesanan' => $pesanan,
            'messages' => $messages
        ];
        
        return view('page.chat.detail', $dataShow);
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'pesanan_uuid' => 'required|exists:pesanan,uuid',
            'message' => 'required|string|max:1000'
        ]);
        
        $admin = Admin::where('id_auth', $request->user()['id_auth'])->first();
        $pesananUuid = $request->pesanan_uuid;
        $message = $request->message;
        
        // Simpan pesan ke database MySQL
        $chat = new Chat();
        $chat->pesanan_uuid = $pesananUuid;
        $chat->sender_uuid = $admin->uuid;
        $chat->sender_type = 'admin';
        $chat->message = $message;
        $chat->is_read = false;
        $chat->save();
        
        // Jika menggunakan FCM, kirim notifikasi ke user
        $pesanan = Pesanan::where('uuid', $pesananUuid)->first();
        if ($pesanan && $pesanan->user) {
            $this->chatService->sendNotification($pesanan->user->uuid, [
                'title' => 'Pesan Baru',
                'body' => 'Admin mengirim pesan: ' . substr($message, 0, 50),
                'order_id' => $pesananUuid
            ]);
        }
        
        return redirect()->back()->with('success', 'Pesan berhasil dikirim');
    }
}