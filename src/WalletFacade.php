<?php

namespace Aveeto\Wallet;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aveeto\Wallet\Wallet
 */
class WalletFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
