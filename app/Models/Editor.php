<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Editor extends Model
{
    use HasFactory;
    protected $table = "editor";
    protected $primaryKey = "id_editor";
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = false;
    protected $fillable = [
        'uuid', 'nama_editor', 'jenis_kelamin', 'no_telpon'
    ];
    public function revisiFiles()
    {
        return $this->hasMany(RevisiEditor::class, 'id_editor');
    }
}