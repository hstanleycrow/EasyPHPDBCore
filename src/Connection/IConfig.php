<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Connection;

interface IConfig
{
    public function getUser(): string;

    public function getPassword(): string;

    public function getDatabaseName(): string;

    public function getPort(): string;

    public function getHost(): string;
}
