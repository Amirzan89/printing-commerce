<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class CatatanPesanan extends Model
{
    use HasFactory;
    protected $table = "catatan_pesanan";
    protected $primaryKey = "id_catatan_pesanan";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'catatan', 'id_admin', 'id_pesanan'
    ];
}