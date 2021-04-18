<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GenDiffTest extends TestCase
{
    private string $exptectedStylish;
    private string $expectedPlain;

    protected function setUp(): void
    {
        $this->exptectedStylish = <<<RES
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

        $this->expectedPlain = <<<RES
        Property 'common.follow' was added with value: false
        Property 'common.setting2' was removed
        Property 'common.setting3' was updated. From true to null
        Property 'common.setting4' was added with value: 'blah blah'
        Property 'common.setting5' was added with value: [complex value]
        Property 'common.setting6.doge.wow' was updated. From '' to 'so much'
        Property 'common.setting6.ops' was added with value: 'vops'
        Property 'group1.baz' was updated. From 'bas' to 'bars'
        Property 'group1.nest' was updated. From [complex value] to 'str'
        Property 'group2' was removed
        Property 'group3' was added with value: [complex value]
        RES;
    }

    private function getFixturePath(string $filename): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $filename;
    }

    public function testGenDiffStylish(): void
    {
        self::assertEquals(
            $this->exptectedStylish,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.json'), 'stylish')
        );

        self::assertEquals(
            $this->exptectedStylish,
            genDiff($this->getFixturePath('data1.yml'), $this->getFixturePath('data2.yaml'), 'stylish')
        );

        self::assertEquals(
            $this->exptectedStylish,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.yaml'), 'stylish')
        );
    }

    public function testGenDiffPlain(): void
    {
        self::assertEquals(
            $this->expectedPlain,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.json'), 'plain')
        );

        self::assertEquals(
            $this->expectedPlain,
            genDiff($this->getFixturePath('data1.yml'), $this->getFixturePath('data2.yaml'), 'plain')
        );

        self::assertEquals(
            $this->expectedPlain,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.yaml'), 'plain')
        );
    }

    public function testGenDiffExceptionNoExtensionInFile(): void
    {
        $this->expectExceptionMessage("No extension found in file");
        genDiff($this->getFixturePath('withoutExtension'), $this->getFixturePath('data2.json'));
    }

    public function testGenDiffExceptionUnknownExtension(): void
    {
        $this->expectExceptionMessage("Unknown extension");
        genDiff($this->getFixturePath('unknownExtension.undefined'), $this->getFixturePath('data2.json'));
    }

    public function testGenDiffExceptionUnknownFile(): void
    {
        $this->expectExceptionMessage("doesn't exist or doesn't available");
        genDiff($this->getFixturePath('unknownFile.json'), $this->getFixturePath('data2.json'));
    }

    public function testGenDiffExceptionsWrongJson(): void
    {
        $this->expectExceptionMessage("cannot be decoded to Json");
        genDiff($this->getFixturePath('wrong.json'), $this->getFixturePath('data2.json'));
    }

    public function testGenDiffExceptionsUknownValueTypeStylish(): void
    {
        $this->expectExceptionMessage("Undefined presentation in stylish format for value type");
        genDiff($this->getFixturePath('withArray.json'), $this->getFixturePath('data2.json'), 'stylish');
    }
}
