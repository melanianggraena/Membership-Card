<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete();

            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admins')
                  ->nullOnDelete();

            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();

            $table->enum('transaction_type', [
                'top_up',
                'room_access'
            ]);

            $table->unsignedBigInteger('reference_id');

            $table->decimal('amount',12,2);

            $table->decimal('balance_before',12,2);

            $table->decimal('balance_after',12,2);

            $table->enum('status',[
                'success',
                'failed'
            ])->default('success');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
