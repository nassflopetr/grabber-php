<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Models;

use NassFloPetr\Grabber\Grabbers\Grabber;
use NassFloPetr\Grabber\Observers\Observer;

abstract class Model
{
    protected Grabber $grabber;
    protected array $observers;
    protected \DateTime $timestamp;

    public function __construct(Grabber $grabber, array $observers = [], ?\DateTime $timestamp = null)
    {
        $this->observers = [];

        $this->attachObservers($observers);

        $this->grabber = $grabber;
        $this->timestamp = \is_null($timestamp) ? new \DateTime() : $timestamp;
    }

    public function __serialize(): array
    {
        return [
            'grabber' => \serialize($this->grabber),
            'observers_class_names' => \array_keys($this->observers),
            'timestamp' => \serialize($this->timestamp),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(
            \unserialize($data['grabber'], ['allowed_classes' => [Grabber::class]]),
            $data['observers_class_names'],
            \unserialize($data['timestamp'], ['allowed_classes' => [\DateTime::class]]),
        );
    }

    public function __clone(): void
    {
        $this->__construct($this->grabber, $this->observers, $this->timestamp);
    }

    abstract public function update(Model $model): void;

    public function getGrabber(): Grabber
    {
        return $this->grabber;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function attachObservers(array $observers): void
    {
        foreach ($observers as $observer) {
            if (!\is_string($observer) && !\is_object($observer)) {
                throw new \TypeError(
                    \sprintf('Array item must be instance of %s class.', Observer::class)
                );
            }

            if (\is_string($observer)) {
                if (!\class_exists($observer) || !\is_subclass_of($observer, Observer::class)) {
                    throw new \ValueError(
                        \sprintf('%s is not instance of %s class.', $observer, Observer::class)
                    );
                }

                $this->observers[$observer] = new $observer;
            } else {
                if (!($observer instanceof Observer)) {
                    throw new \ValueError(
                        \sprintf(
                            '%s object is not instance of %s class.',
                            \get_class($observer),
                            Observer::class
                        )
                    );
                }

                $this->observers[\get_class($observer)] = $observer;
            }
        }
    }

    public function detachObservers(?array $observers = null): void
    {
        if (\is_null($observers)) {
            $this->observers = [];
        } else {
            foreach ($observers as $observer) {
                if (!\is_string($observer) && !\is_object($observer)) {
                    throw new \TypeError(
                        \sprintf('Array item must be instance of %s class.', Observer::class)
                    );
                }

                if (\is_string($observer)) {
                    if (!\class_exists($observer) || !\is_subclass_of($observer, Observer::class)) {
                        throw new \ValueError(
                            \sprintf('%s is not instance of %s class.', $observer, Observer::class)
                        );
                    }

                    unset($this->observers[$observer]);
                } else {
                    if (!($observer instanceof Observer)) {
                        throw new \ValueError(
                            \sprintf(
                                '%s object is not instance of %s class.',
                                \get_class($observer),
                                Observer::class
                            )
                        );
                    }

                    unset($this->observers[\get_class($observer)]);
                }
            }
        }
    }

    public function notifyCreated(): void
    {
        foreach ($this->observers as $observer) {
            $observer->created($this);
        }
    }

    public function notifyUpdated(Model $preModel): void
    {
        foreach ($this->observers as $observer) {
            $observer->updated($preModel, $this);
        }
    }

    public function notifyChanged(Model $preModel): void
    {
        foreach ($this->observers as $observer) {
            $observer->changed($preModel, $this);
        }
    }

    public function notifyDeleted(): void
    {
        foreach ($this->observers as $observer) {
            $observer->deleted($this);
        }
    }
}