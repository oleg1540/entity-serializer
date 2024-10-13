<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

/**
 * @template T of EntitySerializableInterface
 */
interface EntitySerializerInterface
{
    /**
     * @param array<string, mixed> $data
     * @return T
     */
    public function unserialize(array $data): EntitySerializableInterface;

    /**
     * @param T $entity
     * @return array<string, mixed>
     */
    public function serialize(object $entity): array;
}
