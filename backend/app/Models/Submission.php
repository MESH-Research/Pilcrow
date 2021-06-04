<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Publication;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'publication_id',
    ];

    public function publication()
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

}
