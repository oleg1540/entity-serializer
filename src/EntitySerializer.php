<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer;

use BackedEnum;
use DateTime;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * @template T of EntitySerializableInterface
 * @implements EntitySerializerInterface<T>
 */
class EntitySerializer implements EntitySerializerInterface
{
    /** @var class-string<T> */
    private string $entityClass;
    private string $dateTimeFormat;

    /** @var EntitySerializerInterface<EntitySerializableInterface>[] */
    private array $serializers = [];

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        string $entityClass,
        string $dateTimeFormat = 'Y-m-d H:i:s',
    )
    {
        $this->entityClass = $entityClass;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * @return T
     * @throws Exception
     */
    public function unserialize(array $data): EntitySerializableInterface
    {
        $obj = new $this->entityClass();
        foreach ($data as $key => $value) {
            if (property_exists($obj, $key)) {
                $v = $value;

                $property = new ReflectionProperty($obj, $key);
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
                    // && $property->getType() instanceof ReflectionNamedType
                    if ($propertyClass->isSubclassOf(DateTimeInterface::class) && is_string($value)) {
                        $date = DateTime::createFromFormat($this->dateTimeFormat, $value) ?: new DateTime($value);
                        $v = $date;
                    } else if (enum_exists($propertyClassName) && (is_string($value) || is_int($value))) {
                        /** @var BackedEnum $propertyClassName */
                        $v = $propertyClassName::tryFrom($value);
                    } else {
                        if (empty($value)) {
                            continue;
                        }

                        if ($propertyClass->isSubclassOf(EntitySerializableInterface::class) && is_array($value)) {
                            if (!isset($this->serializers[$propertyClass->name])) {
                                $this->serializers[$propertyClass->name] = new EntitySerializer($propertyClass->name);
                            }
                            $v = $this->serializers[$propertyClass->name]->unserialize($value);
                        }
                    }
                } else {
                    if ($propertyClassName === 'array' && is_array($value) && $value !== [] && $property->getDocComment() !== false) {
                        if (preg_match('/@var\s+([^\s]+)\[]\s+/i', $property->getDocComment(), $matches) === 1 && class_exists($matches[1])) {
                            // @todo not work with classes without namespace
                            try {
                                $arrayClass = new ReflectionClass($matches[1]);
                                if ($arrayClass->isSubclassOf(EntitySerializableInterface::class)) {
                                    if (!isset($this->serializers[$arrayClass->name])) {
                                        $this->serializers[$arrayClass->name] = new EntitySerializer($arrayClass->name);
                                    }
                                    $v = array_map(fn(array $vItem) => $this->serializers[$arrayClass->name]->unserialize($vItem), $value);
                                }
                            } catch (ReflectionException) {
                            }
                        }
                    }
                }

                $obj->$key = $v;
            }
        }

        return $obj;
    }

    /**
     * @throws ReflectionException
     */
    public function serialize(object $entity): array
    {
        $array = [];
        $reflection = new ReflectionClass($entity);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
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
                    $value = $entity->{$property->name}->format($this->dateTimeFormat);
                } else if (enum_exists($propertyClassName)) {
                    $value = $entity->{$property->name}->value;
                } else if ($propertyClass->isSubclassOf(EntitySerializableInterface::class)) {
                    if (!isset($this->serializers[$propertyClass->name])) {
                        $this->serializers[$propertyClass->name] = new EntitySerializer($propertyClass->name);
                    }
                    $value = $this->serializers[$propertyClass->name]->serialize($entity->{$property->name});
                }
            } else {
                if ($propertyClassName === 'array' && $entity->{$property->name} !== [] && $property->getDocComment() !== false) {
                    if (preg_match('/@var\s+([^\s]+)\[]\s+/i', $property->getDocComment(), $matches) === 1 && class_exists($matches[1])) {
                        // @todo not work with classes without namespace
                        try {
                            $arrayClass = new ReflectionClass($matches[1]);
                            if ($arrayClass->isSubclassOf(EntitySerializableInterface::class)) {
                                if (!isset($this->serializers[$arrayClass->name])) {
                                    $this->serializers[$arrayClass->name] = new EntitySerializer($arrayClass->name);
                                }
                                $value = array_map(fn(array $vItem) => $this->serializers[$arrayClass->name]->unserialize($vItem), $entity->{$property->name});
                            }
                        } catch (ReflectionException) {
                        }
                    }
                }
            }

            $array[$property->name] = $value;
        }

        return $array;
    }
}
