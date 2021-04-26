<?php

namespace Differ\Formatters\Stylish;

use function Functional\{flat_map, flatten};

function format(array $data): string
{
    return implode(
        "\n",
        ['{', ...array_filter(flatten(formatIter($data)), fn ($elem) => !is_null($elem)), '}']
    );
}

function formatIter(array $data, int $indent = 2): array
{
    return flat_map(
        $data,
        function ($elem) use ($indent): array {

            $oldElem = formElement($elem, false, $indent);
            $newElem = formElement($elem, true, $indent);

            return [$oldElem, $oldElem === $newElem ? null : $newElem];
        }
    );
}

function formElement(array $elem, bool $isNew, int $indent): ?array
{
    if (($isNew && !$elem['newValueExist']) || (!$isNew && !$elem['oldValueExist'])) {
        return null;
    }

    $type = $elem['type'];
    $key = $elem['key'];
    $value = $isNew ? $elem['newValue'] : $elem['oldValue'];

    if (($type === 'add' || $type === 'replace') && $isNew) {
        $label = '+';
    } elseif (($type === 'remove' || $type === 'replace') && !$isNew) {
        $label = '-';
    } else {
        $label = ' ';
    }

    $spaces = str_repeat(' ', $indent);

    if (gettype($value) === 'object') {
        // Здесь $children ?? [] поставил, чтобы тесты проходили
        // а так сюда null не попадает никогда
        // так как есть проверка gettype($value) === 'object', когда туда children передается
        // а в этом случае children точно заполнен
        $valuePresentation = formatIter($elem['children'] ?? [], $indent + 4);
        $spacesEnd = str_repeat(' ', $indent + 2);
        return ["{$spaces}{$label} {$key}: {" ,... $valuePresentation, "{$spacesEnd}}"];
    }

    $valuePresentation = formatValue($value);
    return ["{$spaces}{$label} {$key}: {$valuePresentation}"];
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
