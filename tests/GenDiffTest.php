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

        $expectedJson = <<<RES
        [
           {
              "status":"add",
              "value":false,
              "path":"/common/follow"
           },
           {
              "status":"remove",
              "path":"/common/setting2"
           },
           {
              "status":"replace",
              "prevValue":true,
              "path":"/common/setting3",
              "value":null
           },
           {
              "status":"add",
              "value":"blah blah",
              "path":"/common/setting4"
           },
           {
              "status":"add",
              "value":{
                 "key5":"value5"
              },
              "path":"/common/setting5"
           },
           {
              "status":"replace",
              "prevValue":"",
              "path":"/common/setting6/doge/wow",
              "value":"so much"
           },
           {
              "status":"add",
              "value":"vops",
              "path":"/common/setting6/ops"
           },
           {
              "status":"replace",
              "prevValue":"bas",
              "path":"/group1/baz",
              "value":"bars"
              
           },
           {
              "status":"replace",
              "prevValue":{
                "key":"value"
             },
              "path":"/group1/nest",
              "value":"str"
           },
           {
              "status":"remove",
              "path":"/group2"
           },
           {
              "status":"add",
              "value":{
                 "deep":{
                    "id":{
                       "number":45
                    }
                 },
                 "fee":100500
              },
              "path":"/group3"
           }
        ]
        RES;

        $expectedJson = json_decode($expectedJson, false);
        if ($expectedJson === null) {
            throw new \Exception('$expectedJson cannot be decoded to Json');
        }

        $this->expectedJson = json_encode($expectedJson, JSON_UNESCAPED_SLASHES);
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

    public function testGenDiffJson(): void
    {
        self::assertEquals(
            $this->expectedJson,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.json'), 'json')
        );

        self::assertEquals(
            $this->expectedJson,
            genDiff($this->getFixturePath('data1.yml'), $this->getFixturePath('data2.yaml'), 'json')
        );

        self::assertEquals(
            $this->expectedJson,
            genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.yaml'), 'json')
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

    public function testGenDiffExceptionsUknownPresentationFormat(): void
    {
        $this->expectExceptionMessage("Unknown presentation format");
        genDiff($this->getFixturePath('data1.json'), $this->getFixturePath('data2.json'), 'unknownFormat');
    }
}
