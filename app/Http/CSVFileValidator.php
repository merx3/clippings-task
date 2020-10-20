<?php

namespace App\Http;

use App\Exceptions\CSVFileException;

class CSVFileValidator
{
    public function validate($data)
    {
        if (!is_array($data)) {
            throw new CSVFileException('CSV data is not an array');
        }
        $documentNumbers = $this->getDocumentNumbers($data);
        foreach ($data as $index => $row) {
            $this->validateCustomerColumn($index, $row);
            $this->validateVATColumn($index, $row);
            $this->validateDocumentNumberColumn($index, $row);
            $this->validateTypeColumn($index, $row);
            $this->validateParentColumn($index, $row, $documentNumbers);
            $this->validateCurrencyColumn($index, $row);
            $this->validateTotalColumn($index, $row);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateCustomerColumn($index, $row)
    {
        if (!isset($row[CSVFile::COLUMN_CUSTOMER])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_CUSTOMER);
        }
        if (!strlen($row[CSVFile::COLUMN_CUSTOMER])) {
            throw new CSVFileException('Missing customer value on row ' . $index);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateVATColumn($index, $row)
    {
        if (!isset($row[CSVFile::COLUMN_VAT])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_VAT);
        }
        if (strlen($row[CSVFile::COLUMN_VAT]) !== 9 || !is_numeric($row[CSVFile::COLUMN_VAT])) {
            throw new CSVFileException('Invalid Vat on row ' . $index . ': ' . $row[CSVFile::COLUMN_VAT]);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateDocumentNumberColumn($index, $row)
    {
        if (!isset($row[CSVFile::COLUMN_DOCUMENT_NUMBER])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_DOCUMENT_NUMBER);
        }
        if (!strlen($row[CSVFile::COLUMN_DOCUMENT_NUMBER]) || !is_numeric($row[CSVFile::COLUMN_DOCUMENT_NUMBER])) {
            throw new CSVFileException('Invalid Document Number on row ' . $index);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateTypeColumn($index, $row)
    {
        $validTypes = [
            InvoiceSumCalculator::TYPE_INVOICE,
            InvoiceSumCalculator::TYPE_CREDIT,
            InvoiceSumCalculator::TYPE_DEBIT
        ];
        if (!isset($row[CSVFile::COLUMN_TYPE])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_TYPE);
        }
        $type = intval($row[CSVFile::COLUMN_TYPE]);
        if (!in_array($type, $validTypes)) {
            throw new CSVFileException('Invalid Type Number on row ' . $index
                . '. Allowed values: ' . implode(', ', $validTypes)
            );
        }
    }

    /**
     * @param $index
     * @param $row
     * @param $documentNumbers
     * @throws CSVFileException
     */
    private function validateParentColumn($index, $row, $documentNumbers)
    {
        if (!isset($row[CSVFile::COLUMN_PARENT_DOCUMENT])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_PARENT_DOCUMENT);
        }
        $parent = $row[CSVFile::COLUMN_PARENT_DOCUMENT];
        if (!$parent) {
            return;
        }
        $parentIndex = array_search($parent, $documentNumbers);
        if ($parentIndex === false) {
            throw new CSVFileException('Cannot find parent document for row ' . $index);
        }
        if ($parentIndex === $index) {
            throw new CSVFileException('Parent document references self on row ' . $index);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateCurrencyColumn($index, $row)
    {
        if (!isset($row[CSVFile::COLUMN_CURRENCY])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_CURRENCY);
        }

        if (!Currency::isSupported(strtolower($row[CSVFile::COLUMN_CURRENCY]))) {
            throw new CSVFileException('Currency not supported on row ' . $index);
        }
    }

    /**
     * @param $index
     * @param $row
     * @throws CSVFileException
     */
    private function validateTotalColumn($index, $row)
    {
        if (!isset($row[CSVFile::COLUMN_TOTAL])) {
            $this->throwMissingRowException($index, CSVFile::COLUMN_TOTAL);
        }

        if (!is_numeric($row[CSVFile::COLUMN_TOTAL])) {
            throw new CSVFileException('Total must be a number, on row ' . $index);
        }
    }

    /**
     * @param $index
     * @param $column
     * @throws CSVFileException
     */
    private function throwMissingRowException($index, $column)
    {
        throw new CSVFileException(sprintf('Missing column %s on row %d', $column, $index));
    }

    /**
     * @param $data
     * @return array
     */
    private function getDocumentNumbers($data)
    {
        $docNumbers = [];
        foreach ($data as $index => $row) {
            $docNumbers[$index] = isset($row[CSVFile::COLUMN_DOCUMENT_NUMBER])
                ? $row[CSVFile::COLUMN_DOCUMENT_NUMBER]
                : '';
        }

        return $docNumbers;
    }
}
