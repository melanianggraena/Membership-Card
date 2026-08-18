<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('member_id')
                  ->nullable()
                  ->constrained('members')
                  ->nullOnDelete();

            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->cascadeOnDelete();

            $table->string('uid');

            $table->enum('access_status',[
                'success',
                'failed'
            ]);

            $table->string('reason');

            $table->dateTime('scanned_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_histories');
    }
};
