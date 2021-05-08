<?php

namespace Differ\Formatters\JsonFlat;

use function _\flatMapDepth;

function format(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode(formatIter($data));
}

function formatIter(array $data, array $prevPath = []): array
{
    return flatMapDepth(
        $data,
        function ($elem) use ($prevPath): array {
            $path = [...$prevPath, $elem['key']];

            switch ($elem['type']) {
                case 'nested':
                    return formatIter($elem['children'], $path);
                case 'replace':
                    return [
                        'status' => 'replace',
                        'newValue' => $elem['newValue'],
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                case 'add':
                    return [
                        'status' => 'add',
                        'newValue' => $elem['newValue'],
                        'path' => $path
                    ];
                case 'remove':
                    return [
                        'status' => 'remove',
                        'prevValue' => $elem['oldValue'],
                        'path' => $path
                    ];
                case 'unchanged':
                    return [
                        'status' => 'unchanged',
                        'value' => $elem['oldValue'],
                        'path' => $path
                    ];
                default:
                    throw new \Exception("unknown node type: \"{$elem['type']}\"");
            }
        }
    );
}
