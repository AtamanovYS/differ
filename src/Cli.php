<?php

namespace Differ\Cli;

function run(): void
{
    $doc = <<<DOC
    Generate diff
    
    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
      gendiff [--format <fmt>] <firstFile> <secondFile>
    
    Options:
      -h --help                     Show this screen
      -v --version                  Show version
      --format <fmt>                Report format [default: stylish]
    DOC;

    $args = \Docopt::handle($doc, []);

    $compareResult = \Differ\Differ\genDiff($args['<firstFile>'], $args['<secondFile>'], $args['--format']);
    print_r($compareResult);
}
