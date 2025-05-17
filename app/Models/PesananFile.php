<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PesananFile extends Model
{
    use HasFactory;
    protected $table = "pesanan_file";
    protected $primaryKey = "id_pesanan_file";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'file_path', 'status', 'uploaded_at', 'id_pesanan'
    ];
    public function toPesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}