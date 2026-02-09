<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
            $table->uuid('archived_by')->nullable();
            $table->text('archive_reason')->nullable();
            $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('task_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id')->index();
            $table->uuid('uploaded_by')->nullable();
            $table->string('file_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_attachments');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_at', 'archived_by', 'archive_reason']);
        });
    }
};
