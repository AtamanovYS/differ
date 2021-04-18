<h1 align="center">Differ</h1>

[![Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Github Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/PHP%20CI/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/maintainability)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/test_coverage)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/test_coverage)
------

## Setup

```sh
$ git clone https://github.com/AtamanovYS/php-project-lvl2.git

$ make install
```
## Description

Differ shows the difference between two data structures.
Utility features:
⋅⋅*Support for different input formats: yaml and json
⋅⋅*Generating a report in plain text, stylish and json format

Use ./bin/gendiff -h to get instructions how to use
```
Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: stylish]
```

## Examples

Comparing flat json files in stylish format
[![asciicast](https://asciinema.org/a/SeFljsEr6fFLvsDw7TOkTTCH5.svg)](https://asciinema.org/a/SeFljsEr6fFLvsDw7TOkTTCH5)

Comparing flat yml files in stylish format
[![asciicast](https://asciinema.org/a/quy8Wk0XG6qnqJmyz3mZFgjNU.svg)](https://asciinema.org/a/quy8Wk0XG6qnqJmyz3mZFgjNU)

Comparing files with nested structures in stylish format
[![asciicast](https://asciinema.org/a/bapcvh4qpfhcUNHdZokQHyAMN.svg)](https://asciinema.org/a/bapcvh4qpfhcUNHdZokQHyAMN)

Comparing files with nested structures in plain format
[![asciicast](https://asciinema.org/a/8CAuXOCZGM0sjuc5NrYI86w11.svg)](https://asciinema.org/a/8CAuXOCZGM0sjuc5NrYI86w11)

Comparing files with nested structures in json format
[![asciicast](https://asciinema.org/a/8OuCAuNUzHQ500KfbrMCQXcVo.svg)](https://asciinema.org/a/8OuCAuNUzHQ500KfbrMCQXcVo)