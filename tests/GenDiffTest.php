<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GenDiffTest extends TestCase
{
    private function getFixturePath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $filename;
    }

    public function testGenDiffFlattJson(): void
    {
        $expected = <<<RES
        {
          - follow: false
            host: hexlet.io
          - proxy: 123.234.53.22
          - timeout: 50
          + timeout: 20
          + verbose: true
        }
        RES;

        self::assertEquals(
            $expected,
            genDiff($this->getFixturePath('flat1.json'), $this->getFixturePath('flat2.json'))
        );
    }

    public function testExceptionUnknownFile(): void
    {
        $this->expectExceptionMessage("doesn't exist or doesn't available");
        genDiff($this->getFixturePath('unknownFile.json'), $this->getFixturePath('flat2.json'));
    }

    public function testExceptionsWrongJson(): void
    {
        $this->expectExceptionMessage("cannot be decoded to JSON or it has high level of nesting");
        genDiff($this->getFixturePath('wrong.json'), $this->getFixturePath('flat2.json'));
    }
}
