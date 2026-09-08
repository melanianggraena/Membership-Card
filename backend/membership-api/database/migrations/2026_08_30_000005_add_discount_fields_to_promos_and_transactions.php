<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->string('discount_type')->default('percentage')->after('terms');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->after('outlet_id')->constrained('promos')->nullOnDelete();
            $table->decimal('original_amount', 12, 2)->nullable()->after('reference_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
        });

        DB::table('transactions')->update(['original_amount' => DB::raw('amount')]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_id');
            $table->dropColumn(['original_amount', 'discount_amount']);
        });

        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
