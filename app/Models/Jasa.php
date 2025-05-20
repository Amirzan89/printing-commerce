<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Jasa extends Model
{
    use HasFactory;
    protected $table = "jasa";
    protected $primaryKey = "id_jasa";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'uuid', 'nama_jasa', 'thumbnail_jasa', 'kategori'
    ];
    public function fromPaketJasa()
    {
        return $this->hasMany(PaketJasa::class, 'id_paket_jasa');
    }
}