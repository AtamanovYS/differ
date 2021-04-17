<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GenDiffTest extends TestCase
{
    private string $exptectedFlat;
    private string $exptectedComplex;

    protected function setUp(): void
    {
        $this->exptectedFlat = <<<RES
        {
          - follow: false
            host: hexlet.io
          - proxy: 123.234.53.22
          - timeout: 50
          + timeout: 20
          + verbose: true
        }
        RES;

        $this->exptectedComplex = <<<RES
        {
            common: {
              + follow: false
                setting1: Value 1
              - setting2: 200
              - setting3: true
              + setting3: null
              + setting4: blah blah
              + setting5: {
                    key5: value5
                }
                setting6: {
                    doge: {
                      - wow: 
                      + wow: so much
                    }
                    key: value
                  + ops: vops
                }
            }
            group1: {
              - baz: bas
              + baz: bars
                foo: bar
              - nest: {
                    key: value
                }
              + nest: str
            }
          - group2: {
                abc: 12345
                deep: {
                    id: 45
                }
            }
          + group3: {
                deep: {
                    id: {
                        number: 45
                    }
                }
                fee: 100500
            }
        }
        RES;
    }

    private function getFixturePath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $filename;
    }

    public function testGenDiffFlattJson(): void
    {
        self::assertEquals(
            $this->exptectedFlat,
            genDiff($this->getFixturePath('flat1.json'), $this->getFixturePath('flat2.json'))
        );
    }

    public function testGenDiffComplexJson(): void
    {
        self::assertEquals(
            $this->exptectedComplex,
            genDiff($this->getFixturePath('complex1.json'), $this->getFixturePath('complex2.json'))
        );
    }

    public function testGenDiffFlattYml(): void
    {
        self::assertEquals(
            $this->exptectedFlat,
            genDiff($this->getFixturePath('flat1.yml'), $this->getFixturePath('flat2.yaml'))
        );
    }

    public function testExceptionNoExtensionInFile(): void
    {
        $this->expectExceptionMessage("No extension found in file");
        genDiff($this->getFixturePath('withoutExtension'), $this->getFixturePath('flat2.json'));
    }

    public function testExceptionUnknownExtension(): void
    {
        $this->expectExceptionMessage("Unknown extension");
        genDiff($this->getFixturePath('unknownExtension.undefined'), $this->getFixturePath('flat2.json'));
    }

    public function testExceptionUnknownFile(): void
    {
        $this->expectExceptionMessage("doesn't exist or doesn't available");
        genDiff($this->getFixturePath('unknownFile.json'), $this->getFixturePath('flat2.json'));
    }

    public function testExceptionsWrongJson(): void
    {
        $this->expectExceptionMessage("cannot be decoded to Json");
        genDiff($this->getFixturePath('wrong.json'), $this->getFixturePath('flat2.json'));
    }

    public function testExceptionsUknownValueType(): void
    {
        $this->expectExceptionMessage("Undefined presentation for value type");
        genDiff($this->getFixturePath('complex1WithArray.json'), $this->getFixturePath('flat2.json'));
    }
}
