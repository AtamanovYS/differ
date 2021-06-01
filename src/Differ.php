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
    $comparableData = buildDiff($processedData1, $processedData2);
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

function buildDiff(object $data1, object $data2): array
{
    $comparableData = array_map(
        function (string $key) use ($data1, $data2): array {
            $oldValue = $data1->$key ?? null;
            $newValue = $data2->$key ?? null;

            if (is_object($newValue) && is_object($oldValue)) {
                $type = 'nested';
                $children = buildDiff($oldValue, $newValue);
            } elseif (!property_exists($data2, $key)) {
                $type = 'remove';
            } elseif (!property_exists($data1, $key)) {
                $type = 'add';
            } elseif ($newValue === $oldValue) {
                $type = 'unchanged';
            } else {
                $type = 'replace';
            }

            return [
                'key' => $key,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
                'type' => $type,
                'children' => $children ?? []
            ];
        },
        array_unique([...array_keys(get_object_vars($data1)), ...array_keys(get_object_vars($data2))])
    );

    return sort($comparableData, fn ($left, $right) => $left['key'] <=> $right['key']);
}
