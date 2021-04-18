<?php

namespace Differ\Formatters\Plain;

use function Funct\Strings\chompRight;

function getPresentation(array $data, string $parent = ''): string
{
    $res = array_reduce(
        $data,
        function ($res, $item) use ($parent): string {
            $key = $item['key'];
            $path = $parent === '' ? $key : "{$parent}.{$key}";

            $updatedNestedStructure = $item['object'] !== null && $item['label'] === 0 ? true : false;

            if ($updatedNestedStructure) {
                $value = getPresentation($item['value'], $path);
            } elseif ($item['label'] !== 0) {
                $value = getValuePresentation($item['value']);
            } else {
                return $res;
            }

            if ($item['updated'] && $item['label'] === 1) {
                $res .= "{$value}\n";
            } elseif (!$updatedNestedStructure) {
                $res .= "Property '{$path}' was ";
                if ($item['updated']) {
                    $res .= "updated. From {$value} to ";
                } elseif ($item['label'] === -1) {
                    $res .= "removed\n";
                } else {
                    $res .= "added with value: {$value}\n";
                }
            } else {
                $res .= $value;
            }
            return $res;
        },
        ''
    );

    // Самый последний символ переноса строки не нужен
    if ($parent === '') {
        $res = chompRight($res, "\n");
    }

    return $res;
}

/**
* @param bool|int|string|array|null $value
**/
function getValuePresentation($value): string
{
    $type = gettype($value);
    switch ($type) {
        case 'NULL':
            return 'null';
        case 'boolean':
        case 'integer':
        case 'string':
            return var_export($value, true);
        default:
            return '[complex value]';
    }
}
