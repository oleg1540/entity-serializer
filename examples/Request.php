<?php

declare(strict_types=1);

namespace Ovksoft\EntitySerializer\Examples;

use Ovksoft\EntitySerializer\EntitySerializableInterface;

final class Request implements EntitySerializableInterface
{
    public string $query;
    public int $limit;
}
