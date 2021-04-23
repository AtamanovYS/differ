<?php

namespace Differ\Formatters;

function getPresentation(array $data, string $format): string
{
    switch ($format) {
        case "stylish":
            return Stylish\getPresentation($data);
        case "plain":
            return Plain\getPresentation($data);
        case "json":
            return Json\getPresentation($data);
        default:
            throw new \Exception("Unknown presentation format {$format}");
    }
}
