<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatEditor extends Model
{
    use HasFactory;
    protected $table = "riwayat_editor";
    protected $primaryKey = "id_riwayat_editor";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'nama_editor', 'deskripsi_pengerjaan', 'revisi', 'id_editor', 'id_pesanan'
    ];
    public function toEditor()
    {
        return $this->belongsTo(Editor::class, 'id_editor');
    }
    public function toPesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}