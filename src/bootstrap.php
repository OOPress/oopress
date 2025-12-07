<?php

use OOPress\Core\Config;

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}
