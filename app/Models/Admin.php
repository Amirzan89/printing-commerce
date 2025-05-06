<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Admin extends Model
{
    use HasFactory;
    protected $table = "admin";
    protected $primaryKey = "id_admin";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'uuid', 'nama_admin', 'role', 'id_auth'
    ];
}