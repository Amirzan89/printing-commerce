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
        'file_path',
        'status',
        'uploaded_at',
        'id_pesanan',
        'id_revisi'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    // Relationships
    public function toPesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
    
    public function toRevision()
    {
        return $this->belongsTo(PesananRevision::class, 'id_revisi');
    }
    
    // Helper method to get full file path
    public function getFullPathAttribute()
    {
        return public_path('uploads/pesanan/' . $this->nama_file);
    }
    
    // Helper method to get file URL
    public function getUrlAttribute()
    {
        return url('uploads/pesanan/' . $this->nama_file);
    }
    
    // Simple scopes
    public function scopeBriefFiles($query)
    {
        return $query->where('status', 'brief');
    }
    
    public function scopePreviewFiles($query)
    {
        return $query->where('status', 'preview');
    }
    
    public function scopeRevisiFiles($query)
    {
        return $query->where('status', 'revisi');
    }
    
    public function scopeFinalFiles($query)
    {
        return $query->where('status', 'final');
    }
}