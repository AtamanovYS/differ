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

/*
function getPresentationIter(array $data, string $parent = ''): array
{
    return array_reduce(
        $data,
        function ($res, $item) use ($parent): array {
            $key = $item['key'];
            $path = "{$parent}/{$key}";

            $updatedNestedStructure = $item['object'] !== null && $item['label'] === 0 ? true : false;

            if ($updatedNestedStructure) {
                $res = [...$res, ...getPresentationIter($item['value'], $path)];
            } elseif ($item['label'] !== 0) {
                if (gettype($item['value']) === 'array' && $item['object'] !== null) {
                    $value = $item['object'];
                } else {
                    $value = $item['value'];
                }

                if ($item['updated'] && $item['label'] === 1) {
                    $res[array_key_last($res)]['value'] = $value;
                } else {
                    $newElem = [];
                    if ($item['updated']) {
                        $newElem['status'] = 'replace';
                        $newElem['prevValue'] = $value;
                    } elseif ($item['label'] === -1) {
                        $newElem['status'] = 'remove';
                    } else {
                        $newElem['status'] = 'add';
                        $newElem['value'] = $value;
                    }
                    $newElem['path'] = $path;
                    $res[] = $newElem;
                }
            }
            return $res;
        },
        []
    );
}*/
