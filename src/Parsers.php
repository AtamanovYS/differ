<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $content, string $extension): object
{
    switch ($extension) {
        case "json":
            return parseJson($content);
        case "yaml":
        case "yml":
            return parseYml($content);
        default:
            if ($extension === "") {
                throw new \Exception("No extension found");
            } else {
                throw new \Exception("Unknown extension {$extension}");
            }
    }
}

function parseJson(string $content): object
{
    $jsonData = json_decode($content, false);

    if (is_null($jsonData)) {
        throw new \Exception("file cannot be decoded to Json");
    }

    return $jsonData;
}

function parseYml(string $content): object
{
    return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
}
