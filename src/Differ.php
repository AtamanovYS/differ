<?php

namespace Differ\Differ;

use Webmozart\PathUtil\Path;

use function Functional\{reindex, first_index_of, sort};
use function Funct\Strings\chompRight;
use function Funct\Collection\some;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $dataFile1 = processFile($pathToFile1);
    $dataFile2 = processFile($pathToFile2);
    $comparableData = compare($dataFile1, $dataFile2);
    return comparableDataToString($comparableData);
}

function processFile(string $pathToFile): array
{
    $absolutePathToFile = getAbsolutePathToFile($pathToFile);
    $fileContent = @file_get_contents($absolutePathToFile);

    if ($fileContent === false) {
        throw new \Exception("file {$absolutePathToFile} doesn't exist or doesn't available");
    }

    $jsonData = @json_decode($fileContent, true);
    if ($jsonData === null) {
        throw new \Exception("file {$absolutePathToFile} cannot be decoded to JSON or it has high level of nesting");
    }

    return $jsonData;
}

function getAbsolutePathToFile(string $path): string
{
    $baseDir = php_sapi_name() === 'cli' ? getcwd() : __DIR__;
    return Path::makeAbsolute($path, (string) $baseDir);
}

function compare(array $data1, array $data2): array
{
    $postfixForNewKeys = '_' . uniqid();
    $data2WithNewKeys = reindex($data2, fn($value, $key) => "{$key}$postfixForNewKeys");
    $combinedData = array_merge($data1, $data2WithNewKeys);
    $comparableData = array_reduce(
        array_keys($combinedData),
        function ($comparableData, $key) use ($combinedData, $postfixForNewKeys) {

            $value = $combinedData[$key];
            $keyWithoutPostfix = chompRight($key, $postfixForNewKeys);
            $thisOldElem = $keyWithoutPostfix === $key;

            $newElem = [];
            $newElem['key'] = $keyWithoutPostfix;
            $newElem['value'] = $value;
            $newElem['printableValue'] = gettype($value) === 'string' ? $value : var_export($value, true);
            $newElem['label'] = $thisOldElem ? '-' : '+';
            $newElem['labelValue'] = $thisOldElem ? 0 : 1;

            if (!$thisOldElem && some($comparableData, fn ($item) => $item['key'] === $newElem['key'])) {
                $indexOldElem = first_index_of(
                    $comparableData,
                    fn ($item) => $item['key'] === $newElem['key'] ? $item : null
                );
                if ($comparableData[$indexOldElem]['value'] === $newElem['value']) {
                    $comparableData[$indexOldElem]['label'] = ' ';
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
        fn ($left, $right, $comparableData) => $left['key'] === $right['key'] ?
                                               $left['labelValue'] <=> $right['labelValue'] :
                                               $left['key'] <=> $right['key']
    );
}

function comparableDataToString(array $comparableData): string
{
    return array_reduce(
        $comparableData,
        function ($res, $item): string {
            ['label' => $label, 'key' => $key, 'printableValue' => $value] = $item;
            $res .= "\n  {$label} {$key}: $value";
            return $res;
        },
        '{'
    ) . "\n}";
}
