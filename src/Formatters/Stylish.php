<?php

namespace Differ\Formatters\Stylish;

use function Functional\{flat_map, flatten};

function getPresentation(array $data): string
{
    $getPresentationIter = function (array $data, int $indent = 2) use (&$getPresentationIter): array {
        return flat_map(
            $data,
            function ($elem) use ($getPresentationIter, $indent): array {

                $formElement = function (
                    int $label,
                    string $key,
                    $value,
                    ?array $children
                ) use (
                    $getPresentationIter,
                    $indent
                ): array {
                    $labelPresentation = $label < 0 ? '-' : ($label > 0 ? '+' : ' ');
                    $spaces = str_repeat(' ', $indent);
                    if (gettype($value) === 'object') {
                        // Здесь $children ?? [] поставил, чтобы тесты проходили
                        // а так сюда null не попадает никогда
                        // так как есть проверка gettype($value) === 'object', когда туда children передается
                        // а в этом случае children точно заполнен
                        $valuePresentation = $getPresentationIter($children ?? [], $indent + 4);
                        $spacesEnd = str_repeat(' ', $indent + 2);
                        return ["{$spaces}{$labelPresentation} {$key}: {" ,... $valuePresentation, "{$spacesEnd}}"];
                    }

                    $valuePresentation = getValuePresentation($value);
                    return ["{$spaces}{$labelPresentation} {$key}: {$valuePresentation}"];
                };

                $bothValuesObjects = gettype($elem['oldData']['value'] ?? null) === 'object' &&
                                     gettype($elem['newData']['value'] ?? null) === 'object';

                if ($bothValuesObjects) {
                    return $formElement(0, $elem['key'], $elem['oldData']['value'], $elem['children']);
                }

                if (!is_null($elem['oldData'])) {
                    $oldElem = $formElement(
                        $elem['oldData']['label'],
                        $elem['key'],
                        $elem['oldData']['value'],
                        $elem['children']
                    );
                }

                if (!is_null($elem['newData'])) {
                    $newElem = $formElement(
                        $elem['newData']['label'],
                        $elem['key'],
                        $elem['newData']['value'],
                        $elem['children']
                    );
                }

                return array_unique(flatten([$oldElem ?? null, $newElem ?? null]));
            }
        );
    };

    return implode(
        "\n",
        ['{', ...array_filter($getPresentationIter($data), fn ($elem) => !is_null($elem)), '}']
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
