<?php

namespace Differ\Formatters;

function getPresentation(array $data, string $format, int $indent = 4): string
{
    $getPresent = __NAMESPACE__ . '\\' . ucfirst($format) . '\\getPresentation';

    if (!function_exists($getPresent)) {
        throw new \Exception("Unknown presentation format {$format}");
    }

    return $getPresent($data);
}
