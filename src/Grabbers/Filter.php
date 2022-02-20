<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Grabbers;

use NassFloPetr\Grabber\Models\Model;

interface Filter
{
    public function __invoke(Model $model): bool;
}