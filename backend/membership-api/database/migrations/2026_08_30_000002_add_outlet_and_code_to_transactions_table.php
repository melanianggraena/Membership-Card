<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_code')->nullable()->after('id');
            $table->foreignId('outlet_id')->nullable()->after('room_id')->constrained('outlets')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY transaction_type ENUM('top_up','room_access','outlet_purchase') NOT NULL");
        }

        DB::table('transactions')->orderBy('id')->each(function (object $transaction): void {
            DB::table('transactions')->where('id', $transaction->id)->update([
                'transaction_code' => 'TRX-'.date('Ymd', strtotime($transaction->created_at)).'-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT),
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unique('transaction_code');
            $table->index(['transaction_type', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY transaction_type ENUM('top_up','room_access') NOT NULL");
        }
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['transaction_type', 'status', 'created_at']);
            $table->dropUnique(['transaction_code']);
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn('transaction_code');
        });
    }
};
