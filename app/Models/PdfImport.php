<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfImport extends Model
{
    protected $table = 'pdf_imports';

    protected $fillable = [
        'original_name',
        'stored_path',
        'status',
        'total_pages',
        'processed_pages',
        'total_questions_found',
        'progress_percent',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
