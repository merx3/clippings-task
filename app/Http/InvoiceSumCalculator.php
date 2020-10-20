<?php

namespace App\Http;

use App\Exceptions\CurrencyNotSupportedException;

class InvoiceSumCalculator
{
    const TYPE_INVOICE = 1;
    const TYPE_CREDIT = 2;
    const TYPE_DEBIT = 3;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $exchangeRates;

    /**
     * TBD
     *
     * @var float
     */
    private $vat;

    public function __construct(array $data, array $exchangeRates)
    {
        $this->data = $data;
        $this->exchangeRates = $exchangeRates;
    }

    /**
     * @param $vat
     * @return $this
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * @param $outputCurrency
     * @return float|int
     * @throws CurrencyNotSupportedException
     */
    public function getSumAs($outputCurrency)
    {
        $sum = 0;
        foreach ($this->data as $row) {
            $sum += $this->getRowValue($row, $outputCurrency);
        }

        return round($sum, 3);
    }

    /**
     * @param $row
     * @param $outputCurrency
     * @return float|int
     * @throws CurrencyNotSupportedException
     */
    protected function getRowValue($row, $outputCurrency)
    {
        $type = $row[CsvFile::COLUMN_TYPE];
        $currency = strtolower($row[CsvFile::COLUMN_CURRENCY]);
        $value = $row[CsvFile::COLUMN_TOTAL];
        $outputValue = $this->getExchangeRate($currency, $outputCurrency) * $value;
        if ($type == static::TYPE_CREDIT) {
            $outputValue = -$outputValue;
        }

        return $outputValue;
    }

    /**
     * @param $from
     * @param $to
     * @return float|int
     * @throws CurrencyNotSupportedException
     */
    protected function getExchangeRate($from, $to)
    {
        if (!isset($this->exchangeRates[$from]) || !isset($this->exchangeRates[$to])) {
            throw new CurrencyNotSupportedException(sprintf(
                "Cannot exchange from %s to %s. Missing exchange rate",
                $from, $to
            ));
        }

        $fromExc = $this->exchangeRates[$from];
        $toExc = $this->exchangeRates[$to];

        return $fromExc / $toExc;
    }
}
