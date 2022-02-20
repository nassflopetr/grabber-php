<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Grabbers;

use NassFloPetr\Grabber\Models\Model;
use NassFloPetr\Grabber\Exceptions\SomethingWentChanged;

abstract class JSONWebGrabber extends WebGrabber
{
    abstract protected function getModel(array $decodedJSONItem): Model;

    abstract protected function getDecodedJSONItems(?string $response = null): array;

    public function getModels(?Filter $filter = null, ?string $response = null): iterable
    {
        $decodedJSONItems = $this->getDecodedJSONItems($response);

        foreach ($decodedJSONItems as $decodedJSONItem) {
            $model = $this->getModel($decodedJSONItem);

            if (!\is_null($filter) && !\call_user_func($filter, $model)) {
                continue;
            }

            yield $model;
        }
    }

    protected function getDecodedJSON(?string $response = null): array
    {
        if (\is_null($response)) {
            $response = $this->getResponse();
        }

        try {
            return \json_decode($response, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SomethingWentChanged('JSON decoding failed. ' . $e->getMessage());
        }
    }
}