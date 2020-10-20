<?php

namespace App\Http;

class CSVFileReader
{
    public static function readFile($path, $validate = true)
    {
        $fp = fopen($path, "r");
        $fileData = [];
        if ($fp) {
            $head = fgetcsv($fp);
            while($row = fgetcsv($fp)) {
                $rowAssoc = array_combine($head, $row);
                $fileData[] = $rowAssoc;
            }
            fclose($fp);
        }
        $validator = $validate ? new CSVFileValidator() : null;

        return new CSVFile($fileData, $validator);
    }
}
