<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Observers;

use NassFloPetr\Grabber\Models\Model;

interface Observer
{
    public function created(Model $model): void;

    public function updated(Model $previousModel, Model $model): void;

    public function changed(Model $previousModel, Model $model): void;

    public function deleted(Model $model): void;
}