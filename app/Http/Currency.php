<?php

namespace App\Http;

use App\Exceptions\CurrencyNotSupportedException;
use ReflectionClass;

final class Currency
{
    const EUR = 'eur';
    const USD = 'usd';
    const GBP = 'gbp';

    private static $allCurrencies = [];

    public static function isSupported($currency)
    {
        $currencies = static::getAllCurrencies();

        return in_array($currency, $currencies);
    }

    private static function getAllCurrencies()
    {
        if (empty(static::$allCurrencies)) {
            $reflectionClass = new ReflectionClass(__CLASS__);
            static::$allCurrencies =  $reflectionClass->getConstants();
        }

        return static::$allCurrencies;
    }
}
