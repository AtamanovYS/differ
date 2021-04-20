<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GenDiffTest extends TestCase
{
    private string $exptectedStylish;
    private string $expectedPlain;
    private string $expectedJson;

    protected function setUp(): void
    {
        $this->exptectedStylish = file_get_contents($this->getFixturePath('diff.stylish'));
        $this->expectedPlain = file_get_contents($this->getFixturePath('diff.plain'));
        $this->expectedJson = json_encode(
            json_decode(file_get_contents($this->getFixturePath('diff.json')), false),
            JSON_UNESCAPED_SLASHES
        );
    }

    private function getFixturePath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $filename;
    }

    public function testGenDiffStylish(): void
    {
        self::assertEquals(
            $this->exptectedStylish,
            genDiff($this->getFixturePath('file1.json'), $this->getFixturePath('file2.json'), 'stylish')
        );

        self::assertEquals(
            $this->exptectedStylish,
            genDiff($this->getFixturePath('file1.yml'), $this->getFixturePath('file2.yaml'), 'stylish')
        );
    }

    public function testGenDiffPlain(): void
    {
        self::assertEquals(
            $this->expectedPlain,
            genDiff($this->getFixturePath('file1.json'), $this->getFixturePath('file2.json'), 'plain')
        );

        self::assertEquals(
            $this->expectedPlain,
            genDiff($this->getFixturePath('file1.yml'), $this->getFixturePath('file2.yaml'), 'plain')
        );
    }

    public function testGenDiffJson(): void
    {
        self::assertEquals(
            $this->expectedJson,
            genDiff($this->getFixturePath('file1.json'), $this->getFixturePath('file2.json'), 'json')
        );

        self::assertEquals(
            $this->expectedJson,
            genDiff($this->getFixturePath('file1.yml'), $this->getFixturePath('file2.yaml'), 'json')
        );
    }

    public function testGenDiffExceptionNoExtensionInFile(): void
    {
        $this->expectExceptionMessage("No extension found in file");
        genDiff($this->getFixturePath('withoutExtension'), $this->getFixturePath('file2.json'));
    }

    public function testGenDiffExceptionUnknownExtension(): void
    {
        $this->expectExceptionMessage("Unknown extension");
        genDiff($this->getFixturePath('unknownExtension.undefined'), $this->getFixturePath('file2.json'));
    }

    public function testGenDiffExceptionUnknownFile(): void
    {
        $this->expectExceptionMessage("doesn't exist or doesn't available");
        genDiff($this->getFixturePath('unknownFile.json'), $this->getFixturePath('file2.json'));
    }

    public function testGenDiffExceptionsWrongJson(): void
    {
        $this->expectExceptionMessage("cannot be decoded to Json");
        genDiff($this->getFixturePath('wrong.json'), $this->getFixturePath('file2.json'));
    }

    public function testGenDiffExceptionsUknownValueTypeStylish(): void
    {
        $this->expectExceptionMessage("Undefined presentation in stylish format for value type");
        genDiff($this->getFixturePath('withArray.json'), $this->getFixturePath('file2.json'), 'stylish');
    }

    public function testGenDiffExceptionsUknownPresentationFormat(): void
    {
        $this->expectExceptionMessage("Unknown presentation format");
        genDiff($this->getFixturePath('file1.json'), $this->getFixturePath('file2.json'), 'unknownFormat');
    }
}
