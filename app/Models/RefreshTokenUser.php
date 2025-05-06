<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class RefreshTokenUser extends Model
{
    use HasFactory;
    protected $table = "refresh_token_user";
    protected $primaryKey = "id_refresh_token_user";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = true;
    protected $fillable = [
        'email', 'token', 'number', 'id_auth'
    ];
    public function authAccount()
    {
        return $this->belongsTo(AuthAccount::class, 'id_auth');
    }
}