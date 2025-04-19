<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

interface EntitySerializerInterface
{
    /**
     * @template T of EntitySerializableInterface
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $data
     * @return T
     */
    public function deserialize(string $entityClass, array $data): EntitySerializableInterface;

    /**
     * @return array<string, mixed>
     */
    public function serialize(EntitySerializableInterface $entity): array;
}
