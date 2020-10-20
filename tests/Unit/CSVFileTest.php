<?php

namespace Tests\Unit;

use App\Http\CSVFile;
use App\Http\CSVFileReader;
use PHPUnit\Framework\TestCase;

class CSVFileTest extends TestCase
{
    public function testGetAll()
    {
        $csvFile = $this->createCSVFile();
        $expectedData = [
            [ 'a' => '1', 'b' => '2', 'c' => '3' ],
            [ 'a' => '11', 'b' => '12', 'c' => '13' ]
        ];
        $this->assertInstanceOf(CSVFile::class, $csvFile);
        $this->assertEquals($expectedData, $csvFile->getAll());
    }

    public function testGetAllFiltered()
    {
        $csvFile = $this->createCSVFile();
        $expectedData = [
            [ 'a' => '11', 'b' => '12', 'c' => '13' ]
        ];
        $this->assertInstanceOf(CSVFile::class, $csvFile);
        $this->assertEquals($expectedData, $csvFile->getAllFiltered('b', '12'));
    }

    /**
     * @return CSVFile
     */
    protected function createCSVFile()
    {
        $temp = tmpfile();
        fwrite($temp, "a,b,c\r\n1,2,3\r\n11,12,13");
        $path = stream_get_meta_data($temp)['uri'];
        return CSVFileReader::readFile($path, false);
    }
}
