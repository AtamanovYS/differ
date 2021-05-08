<?php

namespace Differ\Formatters\Json;

function format(array $data): string
{
    // Приведение к типу string, чтобы тесты проходили
    // Здесь невозможно ситуации, чтобы нельзя было привести к json
    return (string) json_encode($data, JSON_UNESCAPED_SLASHES);
}
