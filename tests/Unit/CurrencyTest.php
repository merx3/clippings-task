<?php

namespace Tests\Unit;

use App\Http\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
    public function testValidCurrency()
    {
        $this->assertTrue(Currency::isSupported(Currency::EUR));
    }

    public function testInvalidCurrency()
    {
        $this->assertFalse(Currency::isSupported(''));
    }
}
