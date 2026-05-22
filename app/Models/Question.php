<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $table = 'questions';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'question_type',
        'points',
        'level_id',
        'category_id',
        'is_approve',
        'subject_id',
        'language_id',
        'created_by',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id');
    }
}
