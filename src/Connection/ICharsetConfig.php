<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

interface ICharsetConfig
{
    public function getCharset(): string;
}
