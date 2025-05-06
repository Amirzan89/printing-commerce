<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PaketJasa extends Model
{
    use HasFactory;
    protected $table = "paket_jasa";
    protected $primaryKey = "id_paket_jasa";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = true;
    protected $fillable = [
        'uuid', 'nama_admin', 'role', 'id_auth'
    ];
}