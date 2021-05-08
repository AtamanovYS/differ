<?php

namespace Differ\Formatters;

function format(array $data, string $format): string
{
    switch ($format) {
        case "stylish":
            return Stylish\format($data);
        case "plain":
            return Plain\format($data);
        case "json":
            return Json\format($data);
        case "json-plain":
            return JsonPlain\format($data);
        default:
            throw new \Exception("Unknown format {$format}");
    }
}
