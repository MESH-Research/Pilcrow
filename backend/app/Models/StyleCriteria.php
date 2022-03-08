<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Watson\Validating\ValidatingTrait;

class StyleCriteria extends Model
{
    use HasFactory;
    use ValidatingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'publication_id',
        'name',
        'description',
    ];

    protected $rules = [
        'name' => 'required|maxLength:20',
        'description' => 'maxLength:4096',
        'icon' => 'maxLength:50',
    ];

    /**
     * Define publication relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
