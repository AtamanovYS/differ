<?php

namespace Differ\Differ;

use function Differ\Parsers\processFile;
use function Differ\Formatters\getPresentation;
use function Functional\sort;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $comparableData = compare(processFile($pathToFile1), processFile($pathToFile2));
    return getPresentation($comparableData, $format);
}

function compare(object $data1, object $data2): array
{
    $compareIter = function (object $data1, object $data2, ?int $constantLabel = null) use (&$compareIter): ?array {
        $data1Array = get_object_vars($data1);
        $data2Array = get_object_vars($data2);

        if (count($data1Array) + count($data2Array) === 0) {
            return null;
        }

        $comparableData = array_map(
            function (string $key) use ($data1Array, $data2Array, $compareIter, $constantLabel): array {

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
                        'label' => $label
                    ];
                };

                $oldData = $processData($key, $data1Array, $data2Array, -1, $constantLabel);
                $newData = $processData($key, $data2Array, $data1Array, 1, $constantLabel);

                $children = $compareIter(
                    !is_null($oldData) && gettype($oldData['value']) === 'object' ? $oldData['value'] : new \stdClass(),
                    !is_null($newData) && gettype($newData['value']) === 'object' ? $newData['value'] : new \stdClass(),
                    gettype($oldData['value'] ?? null) !== 'object' || gettype($newData['value'] ?? null) !== 'object' ?
                        0 :
                        null
                );

                return [
                    'key' => $key,
                    'oldData' => $oldData,
                    'newData' => $newData,
                    'children' => $children
                ];
            },
            array_unique([...array_keys($data1Array), ...array_keys($data2Array)])
        );

        return sort($comparableData, fn ($left, $right) => $left['key'] <=> $right['key']);
    };

    return $compareIter($data1, $data2) ?? [];
}
