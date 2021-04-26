<?php

namespace Differ\Formatters\Json;

use function _\flatMapDepth;

function format(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(
        array_values(array_filter(formatIter($data), fn ($elem) => !is_null($elem))),
        JSON_UNESCAPED_SLASHES
    );
}

function formatIter(?array $data, string $prevPath = ''): array
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
                    return formatIter($elem['children'], $path);
                case 'replace':
                    return [
                        'status' => 'replace',
                        'value' => $elem['newValue'],
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                case 'add':
                    return [
                        'status' => 'add',
                        'value' => $elem['newValue'],
                        'path' => $path
                    ];
                case 'remove':
                    return [
                        'status' => 'remove',
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                default:
                    return [null];
            }
        }
    );
}
