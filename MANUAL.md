# Tigress Data Converter — Programmer's Manual

## Overview

`tigress/data-converter` is a PHP library that converts data between five formats:

| Format | Internal Representation |
|--------|------------------------|
| Array  | `array` |
| JSON   | `string` |
| XML    | `string` |
| Object | `stdClass` |
| CSV    | `string` |

The single class `Tigress\DataConverter` uses an **internal-state pattern**: you load or set source data, call a conversion method, then retrieve the result.

---

## Installation

```bash
composer require tigress/data-converter
```

Requires PHP ≥8.5 and the `tigress/file-manager` package.

---

## Quick Start

```php
use Tigress\DataConverter;

$dc = new DataConverter();

// Set source data
$dc->setJsonData('[{"name":"Alice","age":30},{"name":"Bob","age":25}]');

// Convert
$dc->jsonToXml();

// Retrieve result
echo $dc->getXmlData();
```

---

## Workflow

Every conversion follows this pattern:

1. **Set source data** — via a setter or file loader.
2. **Convert** — call a `formatAToFormatB()` method.
3. **Retrieve result** — via a getter or file saver.

### Example with file I/O

```php
$dc->loadJSON('input.json');
$dc->jsonToCsv();
$dc->saveCSV('output.csv');
```

---

## Data Setters / Getters

Set or retrieve data directly without file I/O:

| Format  | Setter                     | Getter                     |
|---------|----------------------------|----------------------------|
| Array   | `setArrayData(array)`      | `getArrayData(): array`    |
| CSV     | `setCsvData(string)`       | `getCsvData(): string`     |
| JSON    | `setJsonData(string)`      | `getJsonData(): string`    |
| Object  | `setObjectData(stdClass)`  | `getObjectData(): stdClass`|
| XML     | `setXmlData(string)`       | `getXmlData(): string`     |

---

## File Loaders / Savers

| Format  | Loader                      | Saver                      |
|---------|-----------------------------|----------------------------|
| CSV     | `loadCSV(string $filePath)` | `saveCSV(string $filePath)`|
| JSON    | `loadJSON(string $filePath)`| `saveJSON(string $filePath)`|
| XML     | `loadXML(string $filePath)` | `saveXML(string $filePath)`|

File operations use `Tigress\FileManager` internally. There are no load/save methods for array or object (use the setters/getters instead).

---

## Conversion Methods

All return `void`. Retrieve the result via the corresponding getter.

### Source → Array

| Method | Notes |
|--------|-------|
| `csvToArray(string $delimiter = ';', bool $dataLine = true)` | When `$dataLine = true` (default), the first CSV row is treated as column headers and each subsequent row becomes an associative array. When `false`, every row is a plain indexed array. |
| `jsonToArray()` | |
| `objectToArray()` | |
| `xmlToArray()` | |

### Source → CSV

| Method |
|--------|
| `arrayToCsv(string $delimiter = ';', string $enclosure = '"')` |
| `jsonToCsv()` |
| `objectToCsv()` |
| `xmlToCsv()` |

### Source → JSON

| Method |
|--------|
| `arrayToJson()` |
| `csvToJson()` |
| `objectToJson()` |
| `xmlToJson()` |

### Source → Object

| Method |
|--------|
| `arrayToObject()` |
| `csvToObject()` |
| `jsonToObject()` |
| `xmlToObject()` |

### Source → XML

| Method | Parameters |
|--------|-----------|
| `arrayToXml(string $rootNode = 'root', ?string $prevKey = 'data')` | `$rootNode` sets the XML root element name. `$prevKey` is used as the element name for numeric-keyed items. |
| `csvToXml(string $rootNode = 'root', string $prevKey = 'data')` | |
| `jsonToXml(string $rootNode = 'root', string $prevKey = 'data')` | |
| `objectToXml(string $rootNode = 'root')` | |

---

## XML Array Format

The `arrayToXmlRec()` private method recognises two special keys when converting arrays to XML:

- **`@attributes`** — an associative array whose key-value pairs become XML attributes on the parent element.
- **`_value`** — sets the text content of an element that also has attributes.

### Example

```php
$dc->setArrayData([
    'book' => [
        '@attributes' => ['id' => '123'],
        '_value' => 'The Title',
    ],
]);
$dc->arrayToXml();
echo $dc->getXmlData();
// <root><book id="123">The Title</book></root>
```

---

## CSV Behaviour

- **Source parsing** (`csvToArray`): the delimiter defaults to `;`. Row 1 is treated as the header row when `$dataLine = true` (the default).
- **Output generation** (`arrayToCsv`): the delimiter defaults to `;`, the enclosure to `"`. Column order follows `array_keys()` of the first row.

---

## Version

```php
echo DataConverter::version(); // e.g. "2025.12.09"
```

---

## Error Handling

Conversion methods may throw `\Exception`:
- `csvToXml`, `jsonToXml`, `arrayToXml`, `objectToXml` — if XML generation fails.
- `xmlToArray` — if `simplexml_load_string` fails.
- File operations — if the file cannot be read or written.
