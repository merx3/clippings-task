<?php

namespace App\Http\Controllers;

use App\Exceptions\CurrencyNotSupportedException;
use App\Http\CSVFile;
use App\Http\CSVFileReader;
use App\Http\Currency;
use App\Http\InvoiceSumCalculator;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    const REQUEST_PARAM_FILE = 'invoice_file';
    const REQUEST_EXCHANGE = 'exchange';
    const REQUEST_OUTPUT_CURRENCY = 'output_currency';
    const REQUEST_FILTER_CUSTOMER = 'filter_customer';

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws CurrencyNotSupportedException
     */
    public function calculate(Request $request) {
        $file = $request->file(self::REQUEST_PARAM_FILE);
        $csv = CSVFileReader::readFile($file->getRealPath());
        $customerFilter = $request->input(self::REQUEST_FILTER_CUSTOMER);
        $data = $customerFilter
            ? $csv->getAllFiltered(CSVFile::COLUMN_CUSTOMER, $customerFilter)
            : $csv->getAll();
        $outputCurrency = $this->validateOutputCurrency($request->input(self::REQUEST_OUTPUT_CURRENCY));
        $exchange = $this->cleanExchange($request->input(self::REQUEST_EXCHANGE));
        $calculator = new InvoiceSumCalculator($data, $exchange);
        $sum = $calculator->getSumAs($outputCurrency);

        return response()->json([
            "sum" => $sum
        ], 200);
    }

    /**
     * @param $exchange
     * @return array
     * @throws CurrencyNotSupportedException
     */
    private function cleanExchange($exchange)
    {
        $cleanValues = [];
        foreach ($exchange as $currency => $rate) {
            $currency = strtolower($currency);
            if (!Currency::isSupported($currency) || !is_numeric($rate)) {
                throw new CurrencyNotSupportedException(sprintf(
                   "Cannot use exchange rate for currency %s.", $currency
                ));
            }
            $value = floatval($rate);
            if ($value <= 0) {
                throw new CurrencyNotSupportedException(sprintf(
                    "Exchange rate for currency %s must be a positive value.", $currency
                ));
            }
            $cleanValues[$currency] = floatval($rate);
        }

        return $cleanValues;
    }

    private function validateOutputCurrency($currency)
    {
        $currency = strtolower($currency);
        if (!Currency::isSupported($currency)) {
            throw new CurrencyNotSupportedException(sprintf(
                "Output currency %s is not supported.", $currency
            ));
        }

        return $currency;
    }
}
