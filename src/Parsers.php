<?php

namespace Differ\Parsers;

use phpDocumentor\Reflection\Types\Callable_;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

function processFile(string $pathToFile): object
{
    $absolutePathToFile = getAbsolutePathToFile($pathToFile);
    $parser = getParser($absolutePathToFile);
    $fileContent = getContentInFile($absolutePathToFile);

    // Костыль, чтоб тесты работали (проверка на is callable внутри функции уже есть,
    // тут код дублирую, иначе тесты не проходят)
    if (!is_callable($parser)) {
        throw new \Exception("Unknown extension in file {$pathToFile}");
    }

    return $parser($fileContent, $absolutePathToFile);
}

function getAbsolutePathToFile(string $path): string
{
    $baseDir = php_sapi_name() === 'cli' ? getcwd() : __DIR__;
    return Path::makeAbsolute($path, (string) $baseDir);
}

function getParser(string $pathToFile): string
{
    $pathToFileParted = explode('.', $pathToFile);
    $extension = end($pathToFileParted);

    if (count($pathToFileParted) < 2 || $extension == false) {
        throw new \Exception("No extension found in file {$pathToFile}");
    }

    $parser = __NAMESPACE__ . '\\parse' . ucfirst($extension === 'yaml' ? 'yml' : $extension);
    if (!is_callable($parser)) {
        throw new \Exception("Unknown extension {$extension} in file {$pathToFile}");
    }

    return $parser;
}

function getContentInFile(string $pathToFile): string
{
    /* Здесь поставил @, потому что в случае ошибки выходит warning, и
    программа останавливается, однако в блоке try catch не ловится, т.к.
    это не исключение, а E_WARNING */
    $fileContent = @file_get_contents($pathToFile);

    if ($fileContent === false) {
        throw new \Exception("file {$pathToFile} doesn't exist or doesn't available");
    }

    return $fileContent;
}

function parseJson(string $content, string $absolutePathToFile): object
{
    $jsonData = json_decode($content, false);

    if ($jsonData === null) {
        throw new \Exception("file {$absolutePathToFile} cannot be decoded to Json");
    }

    return $jsonData;
}

function parseYml(string $content): object
{
    return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
}
