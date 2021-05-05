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

            if (count($elem['children']) > 0) {
                $formattedValue = formatIter($elem['children'], $indent + 4);
                $spaces = str_repeat(' ', $indent);
                $spacesEnd = str_repeat(' ', $indent + 2);
                return ["{$spaces}  {$key}: {" ,...$formattedValue, "{$spacesEnd}}"];
            } else {
                switch ($elem['type']) {
                    case 'replace':
                        $oldElem = formElem($key, $elem['oldValue'], $indent, '-');
                        $newElem = formElem($key, $elem['newValue'], $indent, '+');
                        return [$oldElem, $newElem];
                    case 'add':
                        $newElem = formElem($key, $elem['newValue'], $indent, '+');
                        return [$newElem];
                    case 'remove':
                        $oldElem = formElem($key, $elem['oldValue'], $indent, '-');
                        return [$oldElem];
                    default:
                        $newElem = formElem($key, $elem['newValue'], $indent);
                        return [$newElem];
                }
            }
        }
    );
}

/**
* @param bool|int|string|object|null $value
**/
function formElem(string $key, $value, int $indent, string $label = ' '): array
{
    $spaces = str_repeat(' ', $indent);
    if (is_object($value)) {
        $formattedValue = formatValueObject($value, $indent + 4);
        $spacesEnd = str_repeat(' ', $indent + 2);
        return ["{$spaces}{$label} {$key}: {" ,...$formattedValue, "{$spacesEnd}}"];
    } else {
        $formattedValue = formatValue($value);
        return ["{$spaces}{$label} {$key}: {$formattedValue}"];
    }
}

function formatValueObject(object $valueObject, int $indent): array
{
    return flat_map(
        get_object_vars($valueObject),
        function ($value, $key) use ($indent): array {
            return formElem($key, $value, $indent);
        }
    );
}

/**
* @param bool|int|string|null $value
**/
function formatValue($value): string
{
    $type = gettype($value);
    switch ($type) {
        case 'string':
            // Приведение к string - костыль для прохождения тестов
            // Он считает, что может вернуться тут другой тип, хотя выше проверка на тип string
            return (string) $value;
        case 'NULL':
            return 'null';
        case 'boolean':
        case 'integer':
            return var_export($value, true);
        default:
            throw new \Exception("Undefined value format in stylish format for value type {$type}");
    }
}
