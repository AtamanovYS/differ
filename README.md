<h1 align="center">Differ</h1>

[![Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Github Actions Status](https://github.com/AtamanovYS/php-project-lvl2/workflows/PHP%20CI/badge.svg)](https://github.com/AtamanovYS/php-project-lvl2/actions)
[![Maintainability](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/maintainability)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/7aa6113cad34d1b55339/test_coverage)](https://codeclimate.com/github/AtamanovYS/php-project-lvl2/test_coverage)
------

## Описание

Вычислитель отличий (Differ) – это консольная утилита, показывающая разницу между двумя структурами данных.
Возможности:
* Поддержка разных входных форматов: yaml (yml) и json
* Генерация отчета в различном виде: stylish, plain, json, json-flat

Используйте команду `./bin/gendiff -h`, чтобы получить инструкцию
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

## Технологии, методики

* Функциональный стиль
* PHP (7.4), Composer
* Декларативное описание CLI посредством Docopt
* Тесты (PHPUnit)
* Линтер PHP_CodeSniffer (PSR-12), статический анализатор PHPStan
* GitHub Actions, Code Climate

## Примеры

Сравнение плоских json файлов в формате stylish
[![asciicast](https://asciinema.org/a/LUGeCy0bxKqb0k20uXPyPSYlq.svg)](https://asciinema.org/a/LUGeCy0bxKqb0k20uXPyPSYlq)

Сравнение плоских yml файлов в формате stylish
[![asciicast](https://asciinema.org/a/V70C575Xehd7c8NR75zjt3f30.svg)](https://asciinema.org/a/V70C575Xehd7c8NR75zjt3f30)

Сравнение файлов с вложенными структурами в формате stylish
[![asciicast](https://asciinema.org/a/rnZHbKoLQWl3GzONKj9wRnNGv.svg)](https://asciinema.org/a/rnZHbKoLQWl3GzONKj9wRnNGv)

Сравнение файлов с вложенными структурами в формате plain
[![asciicast](https://asciinema.org/a/h0Yshdmj110wb79t6AUpCTRsn.svg)](https://asciinema.org/a/h0Yshdmj110wb79t6AUpCTRsn)

Сравнение файлов с вложенными структурами в формате json
[![asciicast](https://asciinema.org/a/e9MX75cPadqnb0lsiWAVuZ13o.svg)](https://asciinema.org/a/e9MX75cPadqnb0lsiWAVuZ13o)

Сравнение файлов с вложенными структурами в формате json-flat
[![asciicast](https://asciinema.org/a/raocXqyLxNBM4399GzbOCBtSy.svg)](https://asciinema.org/a/raocXqyLxNBM4399GzbOCBtSy)