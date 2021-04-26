<?php

namespace Differ\Formatters\Plain;

use function Functional\flat_map;

function format(array $data): string
{
    return implode(
        "\n",
        array_filter(formatIter($data), fn ($elem) => !is_null($elem))
    );
}

function formatIter(?array $data, string $prevPath = ''): array
{
    if (is_null($data)) {
        return [null];
    }

    return flat_map(
        $data,
        function ($elem) use ($prevPath): array {
            $key = $elem['key'];
            $path = $prevPath === '' ? $key : "{$prevPath}.{$key}";

            $beginString = "Property '{$path}' was ";

            switch ($elem['type']) {
                case 'unchanged':
                    return formatIter($elem['children'], $path);
                case 'replace':
                    $oldValue = formatValue($elem['oldValue']);
                    $newValue = formatValue($elem['newValue']);
                    return ["{$beginString}updated. From {$oldValue} to {$newValue}"];
                case 'add':
                    $newValue = formatValue($elem['newValue']);
                    return ["{$beginString}added with value: {$newValue}"];
                case 'remove':
                    return ["{$beginString}removed"];
                default:
                    return [null];
            }
        }
    );
}

/**
* @param mixed $value
**/
function formatValue($value): string
{
    $type = gettype($value);
    if ($type === 'NULL') {
        return 'null';
    } elseif ($type === 'boolean' || $type === 'integer' || $type === 'string') {
        return var_export($value, true);
    } else {
        return '[complex value]';
    }
}
