<?php

namespace Differ\Formatters\Stylish;

use function Functional\{flat_map, flatten};

function format(array $data): string
{
    return implode(
        "\n",
        ['{', ...flatten(formatIter($data)), '}']
    );
}

function formatIter(array $data, int $indent = 2): array
{
    return flat_map(
        $data,
        function ($elem) use ($indent): array {
            $key = $elem['key'];
            $formattedIndent = formatIndent($indent);

            switch ($elem['type']) {
                case 'nested':
                    $formattedValue = formatIter($elem['children'], $indent + 4);
                    return ["{$formattedIndent['begin']}  {$key}: {", ...$formattedValue, "{$formattedIndent['end']}}"];
                case 'replace':
                    $oldElem = getElem($key, $elem['oldValue'], $indent, '-');
                    $newElem = getElem($key, $elem['newValue'], $indent, '+');
                    return [$oldElem, $newElem];
                case 'add':
                    $newElem = getElem($key, $elem['newValue'], $indent, '+');
                    return [$newElem];
                case 'remove':
                    $oldElem = getElem($key, $elem['oldValue'], $indent, '-');
                    return [$oldElem];
                default:
                    $newElem = getElem($key, $elem['newValue'], $indent);
                    return [$newElem];
            }
        }
    );
}

/**
 * @param bool|int|string|object|null $value
 **/
function getElem(string $key, $value, int $indent, string $label = ' '): array
{
    $formattedIndent = formatIndent($indent);
    if (is_object($value)) {
        $formattedValue = formatValueObject($value, $indent + 4);
        return ["{$formattedIndent['begin']}{$label} {$key}: {", ...$formattedValue, "{$formattedIndent['end']}}"];
    } else {
        $formattedValue = formatValue($value);
        return ["{$formattedIndent['begin']}{$label} {$key}: {$formattedValue}"];
    }
}

function formatIndent(int $indent): array
{
    $beginIndent = str_repeat(' ', $indent);
    $endIndent = str_repeat(' ', $indent + 2);
    return [
        'begin' => $beginIndent,
        'end'   => $endIndent
    ];
}

function formatValueObject(object $valueObject, int $indent): array
{
    return flat_map(
        get_object_vars($valueObject),
        function ($value, $key) use ($indent): array {
            return getElem($key, $value, $indent);
        }
    );
}

/**
 * @param mixed $value
 **/
function formatValue($value): string
{
    if (is_string($value)) {
        return $value;
    } elseif (is_null($value)) {
        return 'null';
    } elseif (is_bool($value) || is_int($value)) {
        return var_export($value, true);
    } else {
        $type = gettype($value);
        throw new \Exception("Undefined value format in stylish format for value type {$type}");
    }
}
