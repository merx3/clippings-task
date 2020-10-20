<?php

namespace Tests\Unit;

use App\Exceptions\CSVFileException;
use App\Http\CSVFile;
use App\Http\CSVFileValidator;
use App\Http\Currency;
use PHPUnit\Framework\TestCase;

class CSVFileValidatorTest extends TestCase
{
    /**
     * @dataProvider validationData
     * @param $data
     * @param $exceptionMessage
     */
    public function testValidate($data, $exceptionMessage)
    {
        if ($exceptionMessage) {
            $this->expectException(CSVFileException::class);
            $this->expectExceptionMessage($exceptionMessage);
        } else {
            $this->expectNotToPerformAssertions();
        }
        $validator = new CSVFileValidator();
        $validator->validate($data);
    }

    public function validationData()
    {
        $validData = [
            [
                CSVFile::COLUMN_CUSTOMER => 'Cust1',
                CSVFile::COLUMN_VAT => '123456789',
                CSVFile::COLUMN_DOCUMENT_NUMBER => '1000000257',
                CSVFile::COLUMN_TYPE => '1',
                CSVFile::COLUMN_PARENT_DOCUMENT => '',
                CSVFile::COLUMN_CURRENCY => Currency::EUR,
                CSVFile::COLUMN_TOTAL => 100
            ],
            [
                CSVFile::COLUMN_CUSTOMER => 'Cust2',
                CSVFile::COLUMN_VAT => '213456789',
                CSVFile::COLUMN_DOCUMENT_NUMBER => '1000000258',
                CSVFile::COLUMN_TYPE => '2',
                CSVFile::COLUMN_PARENT_DOCUMENT => '1000000257',
                CSVFile::COLUMN_CURRENCY => Currency::USD,
                CSVFile::COLUMN_TOTAL => 300
            ]
        ];
        $mandatoryColumns = [
            CSVFile::COLUMN_CUSTOMER,
            CSVFile::COLUMN_VAT,
            CSVFile::COLUMN_DOCUMENT_NUMBER,
            CSVFile::COLUMN_TYPE,
            CSVFile::COLUMN_CURRENCY,
            CSVFile::COLUMN_TOTAL
        ];
        $testMissing = array_map(function ($column) use ($validData) {
            return [
                $this->setColumnValue($validData, 1, $column, false),
                'Missing column ' . $column . ' on row 1'
            ];
        }, $mandatoryColumns);
        return array_merge($testMissing, [
            'data is valid' => [$validData, false],
            'not an array' => [1, 'CSV data is not an array'],
            'customer is empty' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_CUSTOMER, ''),
                'Missing customer value on row 1'
            ],
            'invalid VAT' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_VAT, '1234'),
                'Invalid Vat on row 1'
            ],
            'invalid document number' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_DOCUMENT_NUMBER, 'true'),
                'Invalid Document Number on row 1'
            ],
            'invalid type' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_TYPE, 'credit'),
                'Invalid Type Number on row 1. Allowed values: 1, 2, 3'
            ],
            'missing parent document' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_PARENT_DOCUMENT, '1'),
                'Cannot find parent document for row 1'
            ],
            'parent document cannot be self' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_PARENT_DOCUMENT, '1000000258'),
                'Parent document references self on row 1'
            ],
            'not supported currency' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_CURRENCY, 'test'),
                'Currency not supported on row 1'
            ],
            'total is not a number' => [
                $this->setColumnValue($validData, 1, CSVFile::COLUMN_TOTAL, 'tbd'),
                'Total must be a number, on row 1'
            ]
        ]);
    }

    private function setColumnValue($data, $rowIdx, $column, $value)
    {
        if ($value === false) {
            unset($data[$rowIdx][$column]);
        } else {
            $data[$rowIdx][$column] = $value;
        }

        return $data;
    }
}
