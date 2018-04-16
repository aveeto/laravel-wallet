<?php

namespace Depsimon\Wallet;

trait HasWallet
{
    /**
     * Retrieve the balance of this user's wallet
     */
    public function getBalanceAttribute()
    {
        return $this->wallet->balance;
    }

    /**
     * Retrieve the wallet of this user
     */
    public function wallet()
    {
        return $this->hasOne(config('wallet.wallet_model', Wallet::class))->withDefault();
    }

    /**
     * Retrieve all transactions of this user
     */
    public function transactions()
    {
        return $this->hasManyThrough(config('wallet.transaction_model', Transaction::class), config('wallet.wallet_model', Wallet::class))->latest();
    }

    /**
     * Determine if the user can withdraw the given amount
     * @param  integer $amount
     * @return boolean
     */
    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    /**
     * Move credits to this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     */
    public function deposit($amount, $type = 'deposit', $meta = [])
    {
        $this->wallet->balance += $amount;
        $this->wallet->save();

        $this->wallet->transactions()
        ->create([
            'amount' => $amount,
            'hash' => uniqid('lwch_'),
            'type' => $type,
            'meta' => $meta
        ]);
    }

    /**
     * Attempt to move credits from this account
     * @param  integer $amount
     * @param  string  $type
     * @param  array   $meta
     * @return boolean
     */
    public function withdraw($amount, $type = 'withdraw', $meta = [])
    {
        $accepted = $this->canWithdraw($amount);

        if ($accepted) {
            $this->wallet->balance -= $amount;
            $this->wallet->save();
            
            $this->wallet->transactions()
            ->create([
                'amount' => $amount,
                'hash' => uniqid('lwch_'),
                'type' => $type,
                'meta' => $meta
            ]);
        }

        return $accepted;
    }

    /**
     * Returns the actual balance for this wallet.
     * Might be different from the balance property if the database is manipulated
     * @return float balance
     */
    public function actualBalance()
    {
        $credits = $this->wallet->transactions()
            ->whereIn('type', ['deposit', 'refund'])
            ->sum('amount');

        $debits = $this->wallet->transactions()
            ->whereIn('type', ['withdraw', 'payout'])
            ->sum('amount');

        return $credits - $debits;
    }
}
