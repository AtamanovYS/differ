<?php

namespace Differ\Formatters\Json;

use function _\flatMapDepth;

function getPresentation(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(
        array_values(array_filter(getPresentationIter($data), fn ($elem) => !is_null($elem))),
        JSON_UNESCAPED_SLASHES
    );
}

function getPresentationIter(?array $data, string $prevPath = ''): array
{
    if ($data === null) {
        return [null];
    }

    return flatMapDepth(
        $data,
        function ($elem) use ($prevPath): array {
            $key = $elem['key'];
            $path = "{$prevPath}/{$key}";

            switch ($elem['type']) {
                case 'unchanged':
                    return getPresentationIter($elem['children'], $path);
                case 'replace':
                    return [
                        'status' => 'replace',
                        'value' => $elem['newData']['value'],
                        'prevValue' => $elem['oldData']['value'],
                        'path' => $path
                    ];
                case 'add':
                    return [
                        'status' => 'add',
                        'value' => $elem['newData']['value'],
                        'path' => $path
                    ];
                case 'remove':
                    return [
                        'status' => 'remove',
                        'prevValue' => $elem['oldData']['value'],
                        'path' => $path
                    ];
                default:
                    return [null];
            }
        }
    );
}
