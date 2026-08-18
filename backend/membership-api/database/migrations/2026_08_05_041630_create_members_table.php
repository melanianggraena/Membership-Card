<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('member_code')->unique();

            $table->string('full_name');

            $table->string('phone')->unique();

            $table->string('email')->nullable();

            $table->string('nfc_uid')->unique()->nullable();

            $table->decimal('balance',12,2)->default(0);

            $table->enum('status',[
                'active',
                'inactive'
            ])->default('active');

            $table->dateTime('last_used')->nullable();

            $table->dateTime('expired_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
