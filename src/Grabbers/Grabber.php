<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Grabbers;

interface Grabber
{
    public function getModels(?Filter $filter = null): iterable;
}