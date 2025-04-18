<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

/**
 * @template T of EntitySerializableInterface
 * @extends EntitySerializer<T>
 */
class EntitySerializerCamelToSnake extends EntitySerializer
{
    public function serialize(object $entity): array
    {
        $array = parent::serialize($entity);

        return $this->transformArray($array, true);
    }

    public function unserialize(array $data): EntitySerializableInterface
    {
        return parent::unserialize($this->transformArray($data, false));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function transformArray(array $data, bool $direction): array
    {
        $transformData = [];
        foreach ($data as $key => $value) {
            $key = $direction ? self::camelToSnake($key) : self::snakeToCamel($key);
            if (is_array($value) && array_filter(array_keys($value), 'is_string') !== []) {
                /** @var array<string, mixed> $value */
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
    private function snakeToCamel(string $string, string $separator = '_'): string
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }

    /**
     * @return string camelCase to snake_case using words separator
     */
    private function camelToSnake(string $string, string $separator = '_'): string
    {
        $replacement = preg_replace('/(?<!^)[A-Z]/', "$separator$0", $string);

        return is_string($replacement) ? strtolower($replacement) : $string;
    }
}
