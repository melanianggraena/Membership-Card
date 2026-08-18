<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {

            $table->id();

            $table->string('room_name');

            $table->text('description')->nullable();

            $table->decimal('access_price',12,2);

            $table->integer('capacity');

            $table->enum('status',[
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
