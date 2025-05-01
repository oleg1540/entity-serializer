<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

use Exception;
use ReflectionException;

class EntitySerializerCamelToSnake implements EntitySerializerInterface
{
    private const DEFAULT_WORD_SEPARATOR  = '_';

    public function __construct(
        private readonly EntitySerializerInterface $serializer = new EntitySerializer(),
    )
    {
    }

    /**
     * @throws ReflectionException
     */
    public function serialize(EntitySerializableInterface $entity): array
    {
        return $this->transformArray($this->serializer->serialize($entity), true);
    }

    /**
     * @throws Exception
     */
    public function deserialize(string $entityClass, array $data): object
    {
        return $this->serializer->deserialize($entityClass, $this->transformArray($data, false));
    }

    /**
     * @param array<string|int, mixed> $data
     * @return array<string|int, mixed>
     */
    private function transformArray(array $data, bool $direction): array
    {
        $transformData = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $key = $direction ? self::camelToSnake($key) : self::snakeToCamel($key);
            }
            if (is_array($value)) {
                /** @var array<string|int, mixed> $value */
                $transformData[$key] = $this->transformArray($value, $direction);
            } else {
                $transformData[$key] = $value;
            }
        }

        return $transformData;
    }

    /**
     * @return string snake_case to camelCase using words separator
     */
    private function snakeToCamel(string $string, string $separator = self::DEFAULT_WORD_SEPARATOR): string
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }

    /**
     * @return string camelCase to snake_case using words separator
     */
    private function camelToSnake(string $string, string $separator = self::DEFAULT_WORD_SEPARATOR): string
    {
        $replacement = preg_replace('/(?<!^)[A-Z]/', "$separator$0", $string);

        return is_string($replacement) ? strtolower($replacement) : $string;
    }
}
