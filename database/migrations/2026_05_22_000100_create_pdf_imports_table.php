<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_imports', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('status')->default('queued');
            $table->unsignedInteger('total_pages')->default(0);
            $table->unsignedInteger('processed_pages')->default(0);
            $table->unsignedInteger('total_questions_found')->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_imports');
    }
};
