<?php

declare(strict_types=1);

namespace App\Modules\Core\Common\Contracts;

interface UserData
{
    public function getName(): string;
    public function getEmail(): string;
}
