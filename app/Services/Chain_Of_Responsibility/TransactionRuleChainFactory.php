<?php

namespace App\Services\Chain_Of_Responsibility;

class TransactionRuleChainFactory
{
    public function buildWithdrawChain(): TransactionRule
    {
        $statusRule   = new AccountStatusActiveRule();
        $amountRule   = new PositiveAmountRule();
        $balanceRule  = new SufficientBalanceRule();

        // status -> amount -> balance
        $statusRule->setNext($amountRule)->setNext($balanceRule);

        return $statusRule;
    }

    public function buildDepositChain(): TransactionRule
    {
        $statusRule = new AccountStatusActiveRule();
        $amountRule = new PositiveAmountRule();

        // status -> amount
        $statusRule->setNext($amountRule)->setNext(null);

        return $statusRule;
    }
}
