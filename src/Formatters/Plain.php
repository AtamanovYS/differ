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

function formatIter(array $data): array
{
    return flat_map(
        $data,
        function ($elem): array {
            $path = implode('.', $elem['path']);
            $beginString = "Property '{$path}' was ";

            if (count($elem['children']) > 0) {
                return formatIter($elem['children']);
            } else {
                switch ($elem['type']) {
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
        }
    );
}

/**
* @param mixed $value
**/
function formatValue($value): string
{
    if (is_null($value)) {
        return 'null';
    } elseif (is_bool($value) || is_int($value) || is_string($value)) {
        return var_export($value, true);
    } else {
        return '[complex value]';
    }
}
