<?php

namespace Differ\Formatters\Stylish;

function getPresentation(array $data, int $indent = 2): string
{
    return array_reduce(
        $data,
        function ($res, $item) use ($indent): string {
            if ($item['object'] !== null) {
                $value = getPresentation($item['value'], $indent + 4);
            } else {
                $value = getValuePresentation($item['value']);
            }
            $key = $item['key'];
            $label = $item['label'] < 0 ? '-' : ($item['label'] > 0 ? '+' : ' ');
            $res .= "\n" . str_repeat(' ', $indent) . "{$label} {$key}: $value";
            return $res;
        },
        '{'
    ) . "\n" . str_repeat(' ', $indent - 2) . '}';
}

/**
* @param bool|int|string|null $value
**/
function getValuePresentation($value): string
{
    $type = gettype($value);
    switch ($type) {
        case 'string':
            // Приведение к string - костыль для прохождения тестов
            // Он считает, что может вернуться тут другой тип, хотя выше проверка на тип string
            return $value;
        case 'NULL':
            return 'null';
        case 'boolean':
        case 'integer':
            return var_export($value, true);
        default:
            throw new \Exception("Undefined presentation in stylish format for value type {$type}");
    }
}
