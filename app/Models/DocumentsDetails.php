<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentsDetails extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'doc_id',
        'stats',
        'rank',
        'email',
    ];

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Documents::class, 'doc_id');
    }
}
