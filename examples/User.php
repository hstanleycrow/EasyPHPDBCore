<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Examples;

use hstanleycrow\EasyPHPDBCore\Model;

/**
 * A minimal model: just declare the table and inherit the CRUD methods.
 * Add your own domain queries on top of getRecords()/query() when needed.
 */
final class User extends Model
{
    protected ?string $table = 'users';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActive(): array
    {
        return $this->query('SELECT id, name, username FROM users WHERE active = ? ORDER BY id')
            ->getRecords(['S']);
    }
}
