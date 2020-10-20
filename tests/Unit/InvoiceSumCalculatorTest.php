<?php

namespace Tests\Unit;

use App\Http\CSVFile;
use App\Http\InvoiceSumCalculator;
use PHPUnit\Framework\TestCase;

class InvoiceSumCalculatorTest extends TestCase
{
    /**
     * @dataProvider invoicesProvider
     * @param $data
     * @param $exchangeRates
     * @param $outputCurrency
     */
    public function testSum($data, $exchangeRates, $outputCurrency, $expectedSum)
    {
        $calculator = new InvoiceSumCalculator($data, $exchangeRates);
        $this->assertEquals($expectedSum, $calculator->getSumAs($outputCurrency));
    }

    public function invoicesProvider()
    {
        $data = [
            [1, 'eur', 150],
            [2, 'usd', 130]
        ];
        $data2 = [
            [1, 'usd', 100],
            [3, 'gbp', 200]
        ];
        $data3 = [
            [1, 'usd', 100],
            [2, 'eur', 400],
            [2, 'gbp', 50],
        ];
        $exchangeRates = [
            'eur' => 1.0,
            'usd' => 0.867,
            'gbp' => 0.941
        ];
        return [
            'sum with credit to eur' => [$this->buildData($data), $exchangeRates, 'eur', 37.29],
            'sum with credit to usd' => [$this->buildData($data), $exchangeRates, 'usd', 43.01],
            'sum without credit to eur' => [$this->buildData($data2), $exchangeRates, 'eur', 274.9],
            'sum with credit > invoice' => [$this->buildData($data3), $exchangeRates, 'eur', -360.35]
        ];
    }

    private function buildData($values) {
        $data = [];
        foreach ($values as $value) {
            $data[] = [
                CsvFile::COLUMN_TYPE => $value[0],
                CsvFile::COLUMN_CURRENCY => $value[1],
                CsvFile::COLUMN_TOTAL => $value[2]
            ];
        }
        return $data;
    }
}
