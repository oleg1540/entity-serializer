<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

use BackedEnum;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

class EntitySerializer implements EntitySerializerInterface
{
    public function __construct(
        private readonly SerializerParams $params = new SerializerParams(),
    )
    {
    }

    /**
     * @throws Exception
     */
    public function deserialize(string $entityClass, array $data): object
    {
        $obj = new $entityClass();
        foreach ($data as $key => $value) {
            if (!is_string($key) || !property_exists($obj, $key)) {
                continue;
            }
            $v = $value;

            $property = new ReflectionProperty($obj, $key);
            if (!$property->isPublic()) {
                continue;
            }
            $propertyType = $property->getType();
            if (!$propertyType instanceof ReflectionNamedType) {
                continue;
            }
            $propertyClassName = $propertyType->getName();
            $isBuiltIn = $propertyType->isBuiltin();

            if (!$isBuiltIn) {
                if (!class_exists($propertyClassName)) {
                    continue;
                }
                $propertyClass = new ReflectionClass($propertyClassName);
                if ($propertyClass->isSubclassOf(DateTimeInterface::class) && is_string($value)) {
                    $date = DateTime::createFromFormat($this->params->dateTimeFormat, $value) ?: new DateTime($value);
                    $v = $date;
                } else if (enum_exists($propertyClassName) && ((is_string($value) && $value !== '') || is_int($value))) {
                    $v = is_subclass_of($propertyClassName, BackedEnum::class)
                        ? $propertyClassName::tryFrom($value)
                        : (is_string($value) ? $propertyClassName::{$value} : null);
                } else {
                    if (empty($value)) {
                        continue;
                    }

                    if ($propertyClass->isSubclassOf(EntitySerializableInterface::class) && is_array($value)) {
                        /** @var array<string, mixed> $value */
                        $v = $this->deserialize($propertyClass->name, $value);
                    }
                }
            } else if ($propertyClassName === 'array' && is_array($value) && $value !== []) {
                $arrayClass = null;
                $reflectionAttribute = current($property->getAttributes(SerializeAs::class));
                if ($reflectionAttribute !== false) {
                    $arrayClass = $reflectionAttribute->getArguments()[0] ?? null;
                }
                if ($arrayClass !== null && class_exists($arrayClass)) {
                    $arrayReflectionClass = new ReflectionClass($arrayClass);
                    if ($arrayReflectionClass->isSubclassOf(EntitySerializableInterface::class)) {
                        /** @var array<array<string, mixed>> $value */
                        $v = array_map(fn(array $vItem) => $this->deserialize($arrayClass, $vItem), $value);
                    }
                }
            }

            $obj->$key = $v;
        }

        return $obj;
    }

    /**
     * @throws ReflectionException
     */
    public function serialize(EntitySerializableInterface $entity): array
    {
        $array = [];
        $reflection = new ReflectionClass($entity);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if (!$property->isInitialized($entity)) {
                continue;
            }
            $value = $entity->{$property->name};
            $propertyType = $property->getType();
            if (!$propertyType instanceof ReflectionNamedType) {
                continue;
            }
            $isBuiltIn = $propertyType->isBuiltin();
            $propertyClassName = $propertyType->getName();

            if (!$isBuiltIn) {
                if (!class_exists($propertyClassName)) {
                    continue;
                }
                $propertyClass = new ReflectionClass($propertyClassName);
                if ($propertyClass->isSubclassOf(DateTimeInterface::class)) {
                    $value = $entity->{$property->name}->format($this->params->dateTimeFormat);
                } else if (enum_exists($propertyClassName)) {
                    $value = is_subclass_of($propertyClassName, BackedEnum::class)
                        ? $entity->{$property->name}->value
                        : $entity->{$property->name}->name;
                } else if ($propertyClass->isSubclassOf(EntitySerializableInterface::class)) {
                    $value = $this->serialize($entity->{$property->name});
                }
            } else if ($propertyClassName === 'array' && $entity->{$property->name} !== []) {
                $arrayClass = null;
                $reflectionAttribute = current($property->getAttributes(SerializeAs::class));
                if ($reflectionAttribute !== false) {
                    $arrayClass = $reflectionAttribute->getArguments()[0] ?? null;
                }

                if ($arrayClass !== null && class_exists($arrayClass)) {
                    $arrayClass = new ReflectionClass($arrayClass);
                    if ($arrayClass->isSubclassOf(EntitySerializableInterface::class)) {
                        $value = array_map(
                            fn(EntitySerializableInterface $vItem) => $this->serialize($vItem),
                            $entity->{$property->name},
                        );
                    }
                }
            }

            $array[$property->name] = $value;
        }

        return $array;
    }
}
