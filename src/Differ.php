<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;
use function Functional\sort;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $fileData1 = getFileData($pathToFile1);
    $fileData2 = getFileData($pathToFile2);
    $processedData1 = parse($fileData1['content'], $fileData1['extension']);
    $processedData2 = parse($fileData2['content'], $fileData2['extension']);
    $comparableData = compare($processedData1, $processedData2);
    return format($comparableData, $format);
}

function getFileData(string $pathToFile): array
{
    if (!is_readable($pathToFile)) {
        throw new \Exception("file {$pathToFile} doesn't exist or doesn't available");
    }

    return [
        'content' => file_get_contents($pathToFile),
        'extension' => pathinfo($pathToFile, PATHINFO_EXTENSION),
    ];
}

function compare(object $data1, object $data2): array
{
    return compareIter($data1, $data2) ?? [];
}

/**
* @param mixed $data1
* @param mixed $data2
**/
function compareIter($data1, $data2, bool $parentObjectRemove = false, bool $parentObjectAdd = false): ?array
{
    $data1Properties = get_object_vars(gettype($data1) === 'object' ? $data1 : new \stdClass());
    $data2Properties = get_object_vars(gettype($data2) === 'object' ? $data2 : new \stdClass());

    if (count($data1Properties) + count($data2Properties) === 0) {
        return null;
    }

    $comparableData = array_map(
        function (string $key) use ($data1Properties, $data2Properties, $parentObjectRemove, $parentObjectAdd): array {
            $oldValue = $data1Properties[$key] ?? null;
            $newValue = $data2Properties[$key] ?? null;

            if ($parentObjectRemove) {
                $type = 'unchanged';
                $oldValueExist = true;
                $children = compareIter($oldValue, null, $parentObjectRemove);
            } elseif ($parentObjectAdd) {
                $type = 'unchanged';
                $newValueExist = true;
                $children = compareIter($oldValue, $newValue, $parentObjectRemove, $parentObjectAdd);
            } elseif (array_key_exists($key, $data1Properties) && !array_key_exists($key, $data2Properties)) {
                $type = 'remove';
                $oldValueExist = true;
                $children = compareIter($oldValue, null, true);
            } elseif (!array_key_exists($key, $data1Properties) && array_key_exists($key, $data2Properties)) {
                $type = 'add';
                $newValueExist = true;
                $children = compareIter(null, $newValue, false, true);
            } elseif ($newValue === $oldValue || (gettype($oldValue) === 'object' && gettype($newValue) === 'object')) {
                $type = 'unchanged';
                $newValueExist = true;
                $oldValueExist = true;
                $children = compareIter($oldValue, $newValue);
            } else {
                $type = 'replace';
                $newValueExist = true;
                $oldValueExist = true;
                $children = compareIter(
                    $oldValue,
                    $newValue,
                    gettype($oldValue) === 'object' ? true : false,
                    gettype($newValue) === 'object' ? true : false
                );
            }

            // существование значение ввёл, так как само значение может быть равно null
            // И при выводе в формате Stylish есть проблемы с идентификацией,
            // в Stylish.php можно и без этой информации пытаться вывести
            // но это получится сложнее, непонятные условия появятся
            // легче тут запомнить
            return [
                'key' => $key,
                'oldValue' => $oldValue,
                'oldValueExist' => $oldValueExist ?? false,
                'newValue' => $newValue,
                'newValueExist' => $newValueExist ?? false,
                'type' => $type,
                'children' => $children,
            ];
        },
        array_unique([...array_keys($data1Properties), ...array_keys($data2Properties)])
    );

    return sort($comparableData, fn ($left, $right) => $left['key'] <=> $right['key']);
}
