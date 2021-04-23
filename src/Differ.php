<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\getPresentation;
use function Functional\sort;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $fileData1 = getFileData($pathToFile1);
    $fileData2 = getFileData($pathToFile2);
    $processedData1 = parse($fileData1['content'], $fileData1['extension'], $fileData1['path']);
    $processedData2 = parse($fileData2['content'], $fileData2['extension'], $fileData2['path']);
    $comparableData = compare($processedData1, $processedData2);
    return getPresentation($comparableData, $format);
}

function getFileData(string $pathToFile): array
{
    if (!is_readable($pathToFile)) {
        throw new \Exception("file {$pathToFile} doesn't exist or doesn't available");
    }

    return [
        'content' => file_get_contents($pathToFile),
        'extension' => pathinfo($pathToFile, PATHINFO_EXTENSION),
        'path' => realpath($pathToFile)
    ];
}

function compare(object $data1, object $data2): array
{
    return compareIter($data1, $data2) ?? [];
}

function compareIter(object $data1, object $data2, ?int $constantLabel = null): ?array
{
    $data1Array = get_object_vars($data1);
    $data2Array = get_object_vars($data2);

    if (count($data1Array) + count($data2Array) === 0) {
        return null;
    }

    $comparableData = array_map(
        function (string $key) use ($data1Array, $data2Array, $constantLabel): array {
            $processData = function (
                string $key,
                array $leadingArray,
                array $slaveArray,
                int $defaultLabel,
                ?int $constantLabel
            ): ?array {
                if (!array_key_exists($key, $leadingArray)) {
                    return null;
                }

                if (!is_null($constantLabel)) {
                    $label = $constantLabel;
                } else {
                    if (
                        array_key_exists($key, $slaveArray) &&
                        (gettype($leadingArray[$key]) === 'object' && gettype($slaveArray[$key]) === 'object' ||
                        $leadingArray[$key] === $slaveArray[$key])
                    ) {
                        $label = 0;
                    } else {
                        $label = $defaultLabel;
                    }
                }

                return [
                    'value' => $leadingArray[$key],
                    'isObject' => gettype($leadingArray[$key]) === 'object',
                    'label' => $label
                ];
            };

            $oldData = $processData($key, $data1Array, $data2Array, -1, $constantLabel);
            $newData = $processData($key, $data2Array, $data1Array, 1, $constantLabel);

            $children = compareIter(
                !is_null($oldData) && gettype($oldData['value']) === 'object' ? $oldData['value'] : new \stdClass(),
                !is_null($newData) && gettype($newData['value']) === 'object' ? $newData['value'] : new \stdClass(),
                gettype($oldData['value'] ?? null) !== 'object' || gettype($newData['value'] ?? null) !== 'object' ?
                    0 :
                    null
            );

            $getNodeType = function (?array $oldData, ?array $newData): string {
                if (!(is_null($oldData) || is_null($newData))) {
                    return $oldData['label'] !== 0 ? 'replace' : 'unchanged';
                } else {
                    return !is_null($oldData) ? 'remove' : 'add';
                }
            };

            return [
                'key' => $key,
                'oldData' => $oldData,
                'newData' => $newData,
                'type' => $getNodeType($oldData, $newData),
                'children' => $children,
            ];
        },
        array_unique([...array_keys($data1Array), ...array_keys($data2Array)])
    );

    return sort($comparableData, fn ($left, $right) => $left['key'] <=> $right['key']);
}
