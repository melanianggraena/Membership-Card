<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::create('promos', function (Blueprint $table) { $table->id(); $table->string('title'); $table->string('image_path')->nullable(); $table->text('description'); $table->text('terms')->nullable(); $table->date('start_date'); $table->date('end_date'); $table->enum('status', ['active', 'inactive'])->default('active'); $table->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('promos'); }
};
