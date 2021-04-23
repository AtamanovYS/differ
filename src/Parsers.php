<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $content, string $extension, string $path): object
{
    switch ($extension) {
        case "json":
            return parseJson($content, $path);
        case "yaml":
        case "yml":
            return parseYml($content);
        default:
            if ($extension === "") {
                throw new \Exception("No extension found in file {$path}");
            } else {
                throw new \Exception("Unknown extension {$extension} in file {$path}");
            }
    }
}

function parseJson(string $content, string $path): object
{
    $jsonData = json_decode($content, false);

    if (is_null($jsonData)) {
        throw new \Exception("file {$path} cannot be decoded to Json");
    }

    return $jsonData;
}

function parseYml(string $content): object
{
    return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
}
