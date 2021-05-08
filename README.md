<h1 align="center">Differ</h1>

[![Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Github Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/PHP%20CI/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/maintainability)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/test_coverage)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/test_coverage)
------

## Description

Differ shows the difference between two data structures.
Utility features:
* Support different input formats: yaml (yml) and json
* Generating a report in various formats: stylish, plain, json, json-flat

Use `./bin/gendiff -h` to get instructions how to use
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
[![asciicast](https://asciinema.org/a/LUGeCy0bxKqb0k20uXPyPSYlq.svg)](https://asciinema.org/a/LUGeCy0bxKqb0k20uXPyPSYlq)

Comparing flat yml files in stylish format
[![asciicast](https://asciinema.org/a/V70C575Xehd7c8NR75zjt3f30.svg)](https://asciinema.org/a/V70C575Xehd7c8NR75zjt3f30)

Comparing with nested structures in stylish format
[![asciicast](https://asciinema.org/a/rnZHbKoLQWl3GzONKj9wRnNGv.svg)](https://asciinema.org/a/rnZHbKoLQWl3GzONKj9wRnNGv)

Comparing with nested structures in plain format
[![asciicast](https://asciinema.org/a/h0Yshdmj110wb79t6AUpCTRsn.svg)](https://asciinema.org/a/h0Yshdmj110wb79t6AUpCTRsn)

Comparing with nested structures in json format
[![asciicast](https://asciinema.org/a/e9MX75cPadqnb0lsiWAVuZ13o.svg)](https://asciinema.org/a/e9MX75cPadqnb0lsiWAVuZ13o)

Comparing with nested structures in json-flat format
[![asciicast](https://asciinema.org/a/raocXqyLxNBM4399GzbOCBtSy.svg)](https://asciinema.org/a/raocXqyLxNBM4399GzbOCBtSy)