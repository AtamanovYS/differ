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

            switch ($elem['type']) {
                case 'nested':
                    $formattedIndentBegin = formatIndent($indent);
                    $formattedIndentEnd = formatIndent($indent + 2);
                    $formattedValue = formatIter($elem['children'], $indent + 4);
                    return ["{$formattedIndentBegin}  {$key}: {", ...$formattedValue, "{$formattedIndentEnd}}"];
                case 'replace':
                    $oldElem = formatElement($key, $elem['oldValue'], $indent, '-');
                    $newElem = formatElement($key, $elem['newValue'], $indent, '+');
                    return [$oldElem, $newElem];
                case 'add':
                    $newElem = formatElement($key, $elem['newValue'], $indent, '+');
                    return [$newElem];
                case 'remove':
                    $oldElem = formatElement($key, $elem['oldValue'], $indent, '-');
                    return [$oldElem];
                case 'unchanged':
                    $newElem = formatElement($key, $elem['newValue'], $indent);
                    return [$newElem];
                default:
                    throw new \Exception("unknown node type: \"{$elem['type']}\"");
            }
        }
    );
}

/**
 * @param mixed $value
 **/
function formatElement(string $key, $value, int $indent, string $label = ' '): array
{
    $formattedIndentBegin = formatIndent($indent);
    if (is_object($value)) {
        $formattedIndentEnd = formatIndent($indent + 2);
        $formattedValue = formatValueObject($value, $indent + 4);
        return ["{$formattedIndentBegin}{$label} {$key}: {", ...$formattedValue, "{$formattedIndentEnd}}"];
    } else {
        $formattedValue = formatValue($value);
        return ["{$formattedIndentBegin}{$label} {$key}: {$formattedValue}"];
    }
}

function formatIndent(int $indent): string
{
    return str_repeat(' ', $indent);
}

function formatValueObject(object $valueObject, int $indent): array
{
    return flat_map(
        get_object_vars($valueObject),
        function ($value, $key) use ($indent): array {
            return formatElement($key, $value, $indent);
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
    }
    if (is_null($value)) {
        return 'null';
    }
    if (is_bool($value) || is_int($value)) {
        return var_export($value, true);
    }
    $type = gettype($value);
    throw new \Exception("Undefined value format in stylish format for value type {$type}");
}
