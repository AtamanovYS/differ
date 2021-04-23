<?php

namespace Differ\Formatters\Plain;

use function Functional\flat_map;

function getPresentation(array $data): string
{
    return implode(
        "\n",
        array_filter(getPresentationIter($data), fn ($elem) => !is_null($elem))
    );
}

function getPresentationIter(?array $data, string $prevPath = ''): array
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
                    return getPresentationIter($elem['children'], $path);
                case 'replace':
                    $oldValue = getValuePresentation($elem['oldData']['value']);
                    $newValue = getValuePresentation($elem['newData']['value']);
                    return ["{$beginString}updated. From {$oldValue} to {$newValue}"];
                case 'add':
                    $newValue = getValuePresentation($elem['newData']['value']);
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
