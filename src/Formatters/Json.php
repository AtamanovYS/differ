<?php

namespace Differ\Formatters\Json;

use function _\flatMapDepth;

function getPresentation(array $data): string
{
    $getPresentationIter = function (array $data, string $parent = '') use (&$getPresentationIter): array {
        return flatMapDepth(
            $data,
            function ($elem) use ($getPresentationIter, $parent): array {
                $key = $elem['key'];
                $path = "{$parent}/{$key}";

                if (!(is_null($elem['oldData']) || is_null($elem['newData']))) {
                    if ($elem['oldData']['label'] !== 0) {
                        return [
                            'status' => 'replace',
                            'value' => $elem['newData']['value'],
                            'prevValue' => $elem['oldData']['value'],
                            'path' => $path
                        ];
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
                        return [
                            'status' => 'remove',
                            'prevValue' => $elem['oldData']['value'],
                            'path' => $path
                        ];
                    } else {
                        return [
                            'status' => 'add',
                            'value' => $elem['newData']['value'],
                            'path' => $path
                        ];
                    }
                }
            }
        );
    };

    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(
        array_values(array_filter($getPresentationIter($data), fn ($elem) => !is_null($elem))),
        JSON_UNESCAPED_SLASHES
    );
}
