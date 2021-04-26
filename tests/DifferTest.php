<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private function getFixturePath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @dataProvider additionProvider
     */
    public function testGenDiff($file1, $file2, $format, $exptected): void
    {
        self::assertEquals(genDiff($file1, $file2, $format), $exptected);
    }

    /**
     * @dataProvider additionProviderExceptions
     */
    public function testGenDiffException($file1, $file2, $format, $exptected): void
    {
        $this->expectExceptionMessage($exptected);
        genDiff($file1, $file2, $format);
    }

    public function additionProvider(): array
    {
        $expectedStylish = file_get_contents($this->getFixturePath('diff.stylish'));
        $expectedPlain = file_get_contents($this->getFixturePath('diff.plain'));
        $expectedJson = json_encode(
            json_decode(file_get_contents($this->getFixturePath('diff.json')), false),
            JSON_UNESCAPED_SLASHES
        );

        $file1Json = $this->getFixturePath('file1.json');
        $file2Json = $this->getFixturePath('file2.json');
        $file1Yaml = $this->getFixturePath('file1.yml');
        $file2Yaml = $this->getFixturePath('file2.yaml');

        return [
            [$file1Json, $file2Json, 'stylish', $expectedStylish],
            [$file1Yaml, $file2Yaml, 'stylish', $expectedStylish],
            [$file1Yaml, $file2Json, 'stylish', $expectedStylish],

            [$file1Json, $file2Json, 'plain', $expectedPlain],
            [$file1Yaml, $file2Yaml, 'plain', $expectedPlain],
            [$file1Yaml, $file2Json, 'plain', $expectedPlain],

            [$file1Json, $file2Json, 'json', $expectedJson],
            [$file1Yaml, $file2Yaml, 'json', $expectedJson],
            [$file1Yaml, $file2Json, 'json', $expectedJson],
        ];
    }

    public function additionProviderExceptions(): array
    {
        $withoutExtensionFile = $this->getFixturePath('withoutExtension');
        $unknownExtensionFile = $this->getFixturePath('unknownExtension.undefined');
        $nonexistentFile = $this->getFixturePath('unknownFile.json');
        $wrongJsonFile = $this->getFixturePath('wrong.json');
        $UndefinedValueFormatInStylishFile = $this->getFixturePath('withArray.json');
        $correctFile = $this->getFixturePath('file1.json');

        return [
            [$withoutExtensionFile, $correctFile, 'stylish', "No extension found"],
            [$unknownExtensionFile, $correctFile, 'stylish', "Unknown extension"],
            [$nonexistentFile, $correctFile, 'stylish', "doesn't exist or doesn't available"],
            [$wrongJsonFile, $correctFile, 'stylish', "cannot be decoded to Json"],
            [$UndefinedValueFormatInStylishFile, $correctFile, 'stylish', "Undefined value format in stylish format for value type"],
            [$correctFile, $correctFile, 'unknownFormat', "Unknown format"],
        ];
    }
}
