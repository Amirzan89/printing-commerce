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
    public function fromPesananFile()
    {
        return $this->hasMany(PesananFile::class, 'id_pesanan_file');
    }
    public function fromCatatanPesanan()
    {
        return $this->hasMany(CatatanPesanan::class, 'id_catatan_pesanan');
    }
    public function fromTransaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_transaksi');
    }
    public function fromReview()
    {
        return $this->hasMany(Review::class, 'id_review');
    }
    public function toUser()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    public function toJasa()
    {
        return $this->belongsTo(Jasa::class, 'id_jasa');
    }
    public function toPaketJasa()
    {
        return $this->belongsTo(Jasa::class, 'id_paket_jasa');
    }
}