<?php

namespace Tigress;

use Exception;
use SimpleXMLElement;
use stdClass;

/**
 * Class Data Converter (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.02.10.0
 * @package Tigress\DataConverter
 */
class DataConverter
{
    private array $arrayData;
    private string $csvData;
    private string $jsonData;
    private stdClass $objectData;
    private string $xmlData;

    /**
     * Get the version of the DataConverter
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.02.10';
    }

    /**
     * DataConverter destructor
     */
    public function __destruct()
    {
        unset($this->arrayData);
        unset($this->csvData);
        unset($this->jsonData);
        unset($this->objectData);
        unset($this->xmlData);
    }

    /**
     * Convert CSV to array
     *
     * @param string $delimiter
     * @param bool $dataLine
     * @return void
     */
    public function csvToArray(string $delimiter = ';', bool $dataLine = true): void
    {
        $lines = explode("\n", $this->csvData);
        $header = null;
        $data = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue; // Skip empty lines
            $row = str_getcsv($line, $delimiter);
            if (!$header && $dataLine) {
                $header = $row;
            } elseif ($dataLine === false) {
                $data[] = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }

        $this->setArrayData($data);
    }

    /**
     * Convert JSON to array
     *
     * @return void
     */
    public function jsonToArray(): void
    {
        $this->setArrayData(json_decode($this->jsonData, true));
    }

    /**
     * Convert object to array
     *
     * @return void
     */
    public function objectToArray(): void
    {
        $this->setArrayData(json_decode(json_encode($this->objectData), true));
    }

    /**
     * Convert XML to array
     *
     * @return void
     */
    public function xmlToArray(): void
    {
        $this->setArrayData(json_decode(json_encode(simplexml_load_string($this->xmlData)), true));
    }

    /**
     * Convert array to CSV
     *
     * @param string $delimiter
     * @param string $enclosure
     * @return void
     */
    public function arrayToCsv(string $delimiter = ';', string $enclosure = '"'): void
    {
        // Extract headers from the first element of the array
        $header = array_keys($this->arrayData[0]);
        $csv = '';

        // Open a memory "file" for read/write...
        $f = fopen('php://memory', 'r+');

        // Write the headers
        fputcsv($f, $header, $delimiter, $enclosure);

        // Write the data
        foreach ($this->arrayData as $row) {
            fputcsv($f, $row, $delimiter, $enclosure);
        }

        // Rewind the "file" and read its content
        rewind($f);
        $csv = stream_get_contents($f);

        // Close the memory "file"
        fclose($f);

        $this->setCsvData($csv);
    }

    /**
     * Convert array to JSON
     *
     * @return void
     */
    public function arrayToJson(): void
    {
        $this->setJsonData(json_encode($this->arrayData));
    }

    /**
     * Convert array to object
     *
     * @return void
     */
    public function arrayToObject(): void
    {
        $this->setObjectData(json_decode(json_encode($this->arrayData)));
    }

    /**
     * Convert array to XML
     *
     * @param string $rootNode
     * @param string|null $prevKey
     * @return void
     * @throws Exception
     */
    public function arrayToXml(string $rootNode = 'root', ?string $prevKey = 'data'): void
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootNode . '/>');
        $this->arrayToXmlRec($xml, $this->arrayData, $prevKey);
        $this->setXmlData($xml->asXML());
    }

    /**
     * Convert CSV to JSON
     *
     * @return void
     */
    public function csvToJson(): void
    {
        $this->csvToArray();
        $this->arrayToJson();
    }

    /**
     * Convert CSV to object
     *
     * @return void
     */
    public function csvToObject(): void
    {
        $this->csvToArray();
        $this->arrayToObject();
    }

    /**
     * Convert CSV to XML
     *
     * @param string $rootNode
     * @param string $prevKey
     * @return void
     * @throws Exception
     */
    public function csvToXml(string $rootNode = 'root', string $prevKey = 'data'): void
    {
        $this->csvToArray();
        $this->arrayToXml($rootNode, $prevKey);
    }

    /**
     * Convert JSON to CSV
     *
     * @return void
     */
    public function jsonToCsv(): void
    {
        $this->jsonToArray();
        $this->arrayToCsv();
    }

    /**
     * Convert JSON to object
     *
     * @return void
     */
    public function jsonToObject(): void
    {
        $this->jsonToArray();
        $this->arrayToObject();
    }

    /**
     * Convert JSON to XML
     *
     * @param string $rootNode
     * @param string $prevKey
     * @return void
     * @throws Exception
     */
    public function jsonToXml(string $rootNode = 'root', string $prevKey = 'data'): void
    {
        $this->jsonToArray();
        $this->arrayToXml($rootNode, $prevKey);
    }

    /**
     * Convert object to CSV
     *
     * @return void
     */
    public function objectToCsv(): void
    {
        $this->objectToArray();
        $this->arrayToCsv();
    }

    /**
     * Convert object to JSON
     *
     * @return void
     */
    public function objectToJson(): void
    {
        $this->setJsonData(json_encode($this->objectData));
    }

    /**
     * Convert object to XML
     *
     * @param string $rootNode
     * @return void
     * @throws Exception
     */
    public function objectToXml(string $rootNode = 'root'): void
    {
        $this->setArrayData(json_decode(json_encode($this->objectData), true));
        $this->arrayToXml($rootNode);
    }

    /**
     * Convert XML to CSV
     *
     * @return void
     */
    public function xmlToCsv(): void
    {
        $this->xmlToArray();
        $this->arrayToCsv();
    }

    /**
     * Convert XML to JSON
     *
     * @return void
     */
    public function xmlToJson(): void
    {
        $this->xmlToArray();
        $this->arrayToJson();
    }

    /**
     * Convert XML to object
     *
     * @return void
     */
    public function xmlToObject(): void
    {
        $this->xmlToArray();
        $this->arrayToObject();
    }

    /**
     * Load CSV file into $csvData
     *
     * @param string $csvFile
     * @return void
     */
    public function loadCSV(string $csvFile): void
    {
        $file = new FileManager();
        $this->setCsvData($file->readLittleFile($csvFile));
    }

    /**
     * Save $csvData to a file
     *
     * @param string $csvFile
     * @return void
     */
    public function saveCSV(string $csvFile): void
    {
        $file = new FileManager();
        $file->writeLittleFile($csvFile, $this->csvData);
    }

    /**
     * Load JSON file into $jsonData
     *
     * @param string $jsonFile
     * @return void
     */
    public function loadJSON(string $jsonFile): void
    {
        $file = new FileManager();
        $this->setJsonData($file->readLittleFile($jsonFile));
    }

    /**
     * Save $jsonData to a file
     *
     * @param string $jsonFile
     * @return void
     */
    public function saveJSON(string $jsonFile): void
    {
        $file = new FileManager();
        $file->writeLittleFile($jsonFile, $this->jsonData);
    }

    /**
     * Load XML file into $xmlData
     *
     * @param string $xmlFile
     * @return void
     */
    public function loadXML(string $xmlFile): void
    {
        $file = new FileManager();
        $this->setXmlData($file->readLittleFile($xmlFile));
    }

    /**
     * Write $xmlData to a file
     *
     * @param string $xmlFile
     * @return void
     */
    public function saveXML(string $xmlFile): void
    {
        $file = new FileManager();
        $file->writeLittleFile($xmlFile, $this->xmlData);
    }

    /**
     * Get the array data
     *
     * @return array
     */
    public function getArrayData(): array
    {
        return $this->arrayData;
    }

    /**
     * Set the array data
     *
     * @param array $arrayData
     * @return void
     */
    public function setArrayData(array $arrayData): void
    {
        $this->arrayData = $arrayData;
    }

    /**
     * Get the CSV data
     *
     * @return string
     */
    public function getCsvData(): string
    {
        return $this->csvData;
    }

    /**
     * Set the CSV data
     *
     * @param string $csvData
     * @return void
     */
    public function setCsvData(string $csvData): void
    {
        $this->csvData = $csvData;
    }

    /**
     * Get the JSON data
     *
     * @return string
     */
    public function getJsonData(): string
    {
        return $this->jsonData;
    }

    /**
     * Set the JSON data
     *
     * @param string $jsonData
     * @return void
     */
    public function setJsonData(string $jsonData): void
    {
        $this->jsonData = $jsonData;
    }

    /**
     * Get the object data
     *
     * @return stdClass
     */
    public function getObjectData(): stdClass
    {
        return $this->objectData;
    }

    /**
     * Set the object data
     *
     * @param stdClass $objectData
     * @return void
     */
    public function setObjectData(stdClass $objectData): void
    {
        $this->objectData = $objectData;
    }

    /**
     * Get the XML data
     *
     * @return string
     */
    public function getXmlData(): string
    {
        return $this->xmlData;
    }

    /**
     * Set the XML data
     *
     * @param string $xmlData
     * @return void
     */
    public function setXmlData(string $xmlData): void
    {
        $this->xmlData = $xmlData;
    }

    /**
     * Recursive function to convert array to XML
     *
     * @param SimpleXMLElement $obj
     * @param array $array
     * @param string|null $prevKey
     * @return void
     */
    private function arrayToXmlRec(SimpleXMLElement &$obj, array $array, ?string $prevKey = 'data'): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0])) {
                    foreach ($value as $subValue) {
                        if (is_array($subValue)) {
                            $node = $obj->addChild($key);
                            $this->arrayToXmlRec($node, $subValue, $key);
                        } else {
                            $obj->addChild($key, $subValue);
                        }
                    }
                } elseif ($key == '@attributes') {
                    foreach ($value as $k => $v) {
                        $obj->addAttribute($k, $v);
                    }
                } else {
                    if (isset($value['_value'])) {
                        $node = $obj->addChild($key, $value['_value']);
                    } else {
                        $node = $obj->addChild($key);
                    }
                    $this->arrayToXmlRec($node, $value, $key);
                }
            } else {
                if ($key == '_value') {
                    // This value is already set!!!
                } elseif (is_numeric($key)) {
                    $obj->addChild($prevKey, $value);
                } else {
                    $obj->addChild($key, $value);
                }
            }
        }
    }
}