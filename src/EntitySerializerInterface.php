<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

interface EntitySerializerInterface
{
    /**
     * @template T of object
     * @param class-string<T> $entityClass
     * @param array<string|int, mixed> $data
     * @return T
     */
    public function deserialize(string $entityClass, array $data): object;

    /**
     * @return array<string|int, mixed>
     */
    public function serialize(EntitySerializableInterface $entity): array;
}
