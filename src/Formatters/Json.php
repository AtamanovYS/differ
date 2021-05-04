<?php

namespace Differ\Formatters\Json;

use function _\flatMapDepth;

function format(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(
        array_values(array_filter(formatIter($data))),
        JSON_UNESCAPED_SLASHES
    );
}

function formatIter(array $data): array
{
    return flatMapDepth(
        $data,
        function ($elem): array {
            $path = '/' . implode('/', $elem['path']);

            if (count($elem['children']) > 0) {
                return formatIter($elem['children']);
            } else {
                switch ($elem['type']) {
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
        }
    );
}
