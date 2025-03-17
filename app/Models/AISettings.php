<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AISettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt'
    ];

    protected $table = 'ai_settings'; // Explicitly specify the table namess

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
