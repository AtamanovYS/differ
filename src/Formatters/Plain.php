<?php

namespace Differ\Formatters\Plain;

use function Functional\flat_map;

function format(array $data): string
{
    return implode(
        "\n",
        array_filter(formatIter($data))
    );
}

function formatIter(array $data, string $prevPath = ''): array
{
    return flat_map(
        $data,
        function ($elem) use ($prevPath): array {
            if ($prevPath !== '') {
                $path = "{$prevPath}.{$elem['key']}";
            } else {
                $path = $elem['key'];
            }
            $beginString = "Property '{$path}' was ";

            switch ($elem['type']) {
                case 'nested':
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
                case 'unchanged':
                    return [null];
                default:
                    throw new \Exception("unknown node type: \"{$elem['type']}\"");
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
    }
    if (is_object($value) || is_array($value)) {
        return '[complex value]';
    }
    return var_export($value, true);
}
