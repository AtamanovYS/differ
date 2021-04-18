<?php

namespace Differ\Differ;

use function Differ\Parsers\processFile;
use function Differ\Formatters\getPresentation;
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
            $newElem['updated'] = false;

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

                $newElem['updated'] = true;
                $comparableData[$indexOldElem]['updated'] = true;
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
