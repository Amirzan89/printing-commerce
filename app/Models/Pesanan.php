<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Pesanan extends Model
{
    use HasFactory;
    protected $table = "pesanan";
    protected $primaryKey = "id_pesanan";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = true;
    protected $fillable = [
        'uuid', 'deskripsi', 'status_pembayaran', 'total_harga', 'estimasi_waktu', 'jumlah_revisi', 'id_user', 'id_jasa', 'id_paket_jasa'
    ];
}