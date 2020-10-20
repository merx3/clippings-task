<?php

namespace App\Http;

class CSVFile
{
    const COLUMN_CUSTOMER = 'Customer';
    const COLUMN_VAT = 'Vat number';
    const COLUMN_DOCUMENT_NUMBER = 'Document number';
    const COLUMN_TYPE = 'Type';
    const COLUMN_PARENT_DOCUMENT = 'Parent document';
    const COLUMN_CURRENCY = 'Currency';
    const COLUMN_TOTAL = 'Total';

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [], CSVFileValidator $validator = null)
    {
        if ($validator) {
            $validator->validate($data);
        }
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function getAllFiltered($field, $value)
    {
        $filteredData = [];
        foreach ($this->data as $row) {
            if (isset($row[$field]) && strpos($row[$field], $value) !== false) {
                $filteredData[] = $row;
            }
        }

        return $filteredData;
    }
}
