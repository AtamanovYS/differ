<?php

namespace Differ\Formatters\Json;

function getPresentation(array $data): string
{
    return json_encode(getPresentationIter($data), JSON_UNESCAPED_SLASHES);
}

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
}
