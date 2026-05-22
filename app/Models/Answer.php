<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $table = 'answers';

    public $timestamps = false;

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    protected $fillable = [
        'answer',
        'is_correct',
        'question_id',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
