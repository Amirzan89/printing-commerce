<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MetodePembayaran extends Model
{
    use HasFactory;
    protected $table = "metode_pembayaran";
    protected $primaryKey = "id_metode_pembayaran";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'nama_metode_pembayaran', 'nomor_metode_pembayaran', 'deskripsi_1', 'deskripsi_2', 'thumbnail', 'icon'
    ];
}