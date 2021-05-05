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
        $this->assertEquals($exptected, genDiff($file1, $file2, $format));
    }

    /**
     * @dataProvider additionProviderExceptions
     */
    public function testGenDiffException($file1, $file2, $format): void
    {
        $this->expectException(\Exception::class);
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
        $withoutExtension = $this->getFixturePath('withoutExtension');
        $unknownExtension = $this->getFixturePath('unknownExtension.undefined');
        $nonexistent = $this->getFixturePath('unknownFile.json');
        $wrongJson = $this->getFixturePath('wrong.json');
        $undefinedValueFormatInStylish = $this->getFixturePath('withArray.json');
        $correct = $this->getFixturePath('file1.json');

        return [
            [$withoutExtension, $correct, 'stylish'],
            [$unknownExtension, $correct, 'stylish'],
            [$nonexistent, $correct, 'stylish'],
            [$wrongJson, $correct, 'stylish'],
            [$undefinedValueFormatInStylish, $correct, 'stylish'],
            [$correct, $correct, 'unknownFormat'],
        ];
    }
}
