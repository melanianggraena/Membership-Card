<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionCodeService
{
    public function next(): string
    {
        $prefix = 'TRX-'.now()->format('Ymd').'-';
        $lastCode = Transaction::where('transaction_code', 'like', $prefix.'%')
            ->lockForUpdate()->orderByDesc('transaction_code')->value('transaction_code');
        $sequence = $lastCode ? ((int) substr($lastCode, -6)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
