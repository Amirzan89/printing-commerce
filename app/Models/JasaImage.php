<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JasaImage extends Model
{
    use HasFactory;
    
    protected $table = 'jasa_images';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id_jasa',
        'image_path'
    ];
    
    public function jasa()
    {
        return $this->belongsTo(Jasa::class, 'id_jasa', 'id_jasa');
    }
} 