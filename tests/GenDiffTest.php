<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class GenDiffTest extends TestCase
{
    private const TEST_FILES_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;

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
            genDiff(self::TEST_FILES_DIR . 'flatJson1.json', self::TEST_FILES_DIR . 'flatJson2.json')
        );
    }
}
