<?php

namespace Differ\Differ;

use function Differ\Parsers\processFile;
use function Functional\{reindex, first_index_of, sort};
use function Funct\Strings\chompRight;
use function Funct\Collection\some;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $dataFile1 = processFile($pathToFile1);
    $dataFile2 = processFile($pathToFile2);
    $comparableData = compare($dataFile1, $dataFile2);
    return getPresentation($comparableData, $format);
}

function compare(object $data1, ?object $data2 = null): array
{
    $thisUniqParent = $data2 === null ? true : false;
    if ($data2 === null) {
        $data2 = new \stdClass();
    }

    $postfixForNewKeys = '_' . uniqid();
    $combinedData = array_merge(
        get_object_vars($data1),
        reindex(get_object_vars($data2), fn($value, $key) => "{$key}$postfixForNewKeys")
    );

    $comparableData = array_reduce(
        array_keys($combinedData),
        function ($comparableData, $key) use ($combinedData, $postfixForNewKeys, $thisUniqParent) {

            $value = $combinedData[$key];
            $keyWithoutPostfix = chompRight($key, $postfixForNewKeys);
            $thisOldElem = $keyWithoutPostfix === $key;

            $newElem = [];
            $newElem['key'] = $keyWithoutPostfix;
            $newElem['value'] = gettype($value) === 'object' ? compare($value) : $value;
            $newElem['object'] = gettype($value) === 'object' ? $value : null;
            $newElem['label'] = $thisUniqParent ? 0 : ($thisOldElem ? -1 : 1);

            if (!$thisOldElem && some($comparableData, fn ($item) => $item['key'] === $newElem['key'])) {
                $indexOldElem = first_index_of(
                    $comparableData,
                    fn ($item) => $item['key'] === $newElem['key'] ? $item : null
                );

                if ($comparableData[$indexOldElem]['object'] !== null && $newElem['object'] !== null) {
                    $comparableData[$indexOldElem]['value'] = compare(
                        $comparableData[$indexOldElem]['object'],
                        $newElem['object']
                    );
                    $comparableData[$indexOldElem]['label'] = 0;
                    return $comparableData;
                }

                if ($comparableData[$indexOldElem]['value'] === $newElem['value']) {
                    $comparableData[$indexOldElem]['label'] = 0;
                    return $comparableData;
                }
            }

            $comparableData[] = $newElem;
            return $comparableData;
        },
        []
    );

    return sort(
        $comparableData,
        fn ($left, $right) => $left['key'] === $right['key'] ?
                                               $left['label'] <=> $right['label'] :
                                               $left['key'] <=> $right['key']
    );
}

function getPresentation(array $comparableData, string $format, int $indent = 4): string
{
    $getPresent = __NAMESPACE__ . '\getPresentation' . ucfirst($format);

    if (!function_exists($getPresent)) {
        throw new \Exception("Unknown presentation format {$format}");
    }

    return $getPresent($comparableData);
}

function getPresentationStylish(array $data, int $indent = 2): string
{
    return array_reduce(
        $data,
        function ($res, $item) use ($indent): string {
            $key = $item['key'];
            if ($item['object'] !== null) {
                $value = getPresentationStylish($item['value'], $indent + 4);
            } else {
                $value = getValuePresentation($item['value']);
            }
            $label = $item['label'] < 0 ? '-' : ($item['label'] > 0 ? '+' : ' ');
            $res .= "\n" . str_repeat(' ', $indent) . "{$label} {$key}: $value";
            return $res;
        },
        '{'
    ) . "\n" . str_repeat(' ', $indent - 2) . '}';
}

/**
* @param bool|int|string|null $value
**/
function getValuePresentation($value): string
{
    $type = gettype($value);
    switch ($type) {
        case 'string':
            // Приведение к string - костыль для прохождения тестов
            // Он считает, что может вернуться тут другой тип, хотя выше проверка на тип string
            return (string) $value;
        case 'NULL':
            return 'null';
        case 'boolean':
        case 'integer':
            return var_export($value, true);
        default:
            throw new \Exception("Undefined presentation for value type {$type}");
    }
}
