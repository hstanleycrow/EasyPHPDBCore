<?php

declare(strict_types=1);

namespace hstanleycrow\EasyPHPDBCore\Tests\Support;

use hstanleycrow\EasyPHPDBCore\Model;

final class UserModel extends Model
{
    protected ?string $table = 'users';
}
