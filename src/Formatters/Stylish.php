<?php

namespace Differ\Formatters\Stylish;

use function Functional\{flat_map, flatten};

function getPresentation(array $data): string
{
    return implode(
        "\n",
        ['{', ...array_filter(flatten(getPresentationIter($data)), fn ($elem) => !is_null($elem)), '}']
    );
}

function getPresentationIter(array $data, int $indent = 2): array
{
    return flat_map(
        $data,
        function ($elem) use ($indent): array {

            $formElement = function (?array $elem, string $key, ?array $children) use ($indent): ?array {
                if ($elem === null) {
                    return null;
                }

                $labelPresentation = $elem['label'] < 0 ? '-' : ($elem['label'] > 0 ? '+' : ' ');
                $spaces = str_repeat(' ', $indent);

                if ($elem['isObject']) {
                    // Здесь $children ?? [] поставил, чтобы тесты проходили
                    // а так сюда null не попадает никогда
                    // так как есть проверка gettype($value) === 'object', когда туда children передается
                    // а в этом случае children точно заполнен
                    $valuePresentation = getPresentationIter($children ?? [], $indent + 4);
                    $spacesEnd = str_repeat(' ', $indent + 2);
                    return ["{$spaces}{$labelPresentation} {$key}: {" ,... $valuePresentation, "{$spacesEnd}}"];
                }

                $valuePresentation = getValuePresentation($elem['value']);
                return ["{$spaces}{$labelPresentation} {$key}: {$valuePresentation}"];
            };

            $oldElem = $formElement($elem['oldData'], $elem['key'], $elem['children']);
            if ($elem['type'] !== 'unchanged') {
                $newElem = $formElement($elem['newData'], $elem['key'], $elem['children']);
            }

            return [$oldElem, $newElem ?? null];
        }
    );
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
            return (string) $value;
        case 'NULL':
            return 'null';
        case 'boolean':
        case 'integer':
            return var_export($value, true);
        default:
            throw new \Exception("Undefined presentation in stylish format for value type {$type}");
    }
}
