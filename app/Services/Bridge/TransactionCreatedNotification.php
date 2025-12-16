<?php

namespace App\Services\Bridge;

use App\Models\Transaction;
use App\Models\User;

class TransactionCreatedNotification extends BaseNotification
{
    public function __construct(
        $channel,
        private readonly Transaction $transaction,
    ) {
        parent::__construct($channel);
    }

    protected function buildMessage(User $user): array
    {
        $type = $this->transaction->type;
        $amount     = $this->transaction->amount;
        $createdAt  = $this->transaction->created_at->format('Y-m-d H:i');
        $fromAcc    = $this->transaction->fromAccount?->account_number;
        $toAcc      = $this->transaction->toAccount?->account_number;

        $title = "معاملة مالية جديدة";

        $body = match ($type) {
            'ايداع'  => "تم تنفيذ عملية إيداع بقيمة {$amount} إلى حساب رقم {$toAcc} بتاريخ {$createdAt}.",
            'سحب' => "تم تنفيذ عملية سحب بقيمة {$amount} من حساب رقم {$fromAcc} بتاريخ {$createdAt}.",
            'تحويل' => "تم تحويل مبلغ {$amount} من الحساب {$fromAcc} إلى الحساب {$toAcc} بتاريخ {$createdAt}.",
            default    => "تم تنفيذ عملية {$type} بقيمة {$amount} بتاريخ {$createdAt}.",
        };

        $data = [
            'transaction_id' => (string) $this->transaction->id,
            'type'           => $type,
            'amount'         => (string) $amount,
        ];

        return [$title , $body , $data];
    }
}
