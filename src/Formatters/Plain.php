<?php

namespace Differ\Formatters\Plain;

use function Functional\flat_map;

function getPresentation(array $data): string
{
    $getPresentationIter = function (array $data, string $parent = '') use (&$getPresentationIter): array {
        return flat_map(
            $data,
            function ($elem) use ($getPresentationIter, $parent): array {
                $key = $elem['key'];
                $path = $parent === '' ? $key : "{$parent}.{$key}";

                $beginString = "Property '{$path}' was ";

                if (!(is_null($elem['oldData']) || is_null($elem['newData']))) {
                    if ($elem['oldData']['label'] !== 0) {
                        $oldValue = getValuePresentation($elem['oldData']['value']);
                        $newValue = getValuePresentation($elem['newData']['value']);
                        return ["{$beginString}updated. From {$oldValue} to {$newValue}"];
                    } elseif (
                        gettype($elem['oldData']['value']) === 'object' &&
                        gettype($elem['newData']['value']) === 'object'
                    ) {
                        return $getPresentationIter($elem['children'], $path);
                    } else {
                        return [null];
                    }
                } else {
                    if (!is_null($elem['oldData'])) {
                        return ["{$beginString}removed"];
                    } else {
                        $newValue = getValuePresentation($elem['newData']['value']);
                        return ["{$beginString}added with value: {$newValue}"];
                    }
                }
            }
        );
    };

    return implode(
        "\n",
        array_filter($getPresentationIter($data), fn ($elem) => !is_null($elem))
    );
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
