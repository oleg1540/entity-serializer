# Entity serializer
The library helps to quickly and easily serialize an object to an array and deserialize an object from an array.
All you need is to create a class with an entity description and implement `EntitySerializableInterface`.
For example, it can be very useful for JSON APIs.

## Requirements
PHP 8.3 and above. No extensions required!

## Installation
This package can be installed as a [Composer](https://getcomposer.org/) dependency.
```shell
composer install oleg1540/entity-serializer
```

## Usage
1. Describe entity.
```php
class ResponseObj
{
    public int $id;
    public string $name;
    public \DateTime $dateAdded;
}
```
2. Get array data for example from REST API in JSON format.
```php
$data = json_decode('{"id": 123, "name": "Example", "dateAdded": "2025-04-04T10:46:31.158+00:00"}', true);
```
3. Create instance of `EntitySerializerInterface`.
```php
$serializer = new \Ovksoft\EntitySerializer\EntitySerializer();
```
4. Deserialize and work with clean object instead of array.
```php
$response = $serializer->deserialize(ResponseObj::class, $data);
```

More examples you can see in [examples](https://github.com/oleg1540/entity-serializer/blob/master/examples/) directory.

## Versioning
This library follows the [semver](http://semver.org/) semantic versioning specification.

## Contributing
Feel free to open issues with bugs or questions and create PRs with fixes or suggested improvements.

**Checklist:**
1. PHPStan should pass without errors.
2. All changes must be covered with unit tests.
3. PHPUnit should pass without errors.
4. Code style [PSR-12](https://www.php-fig.org/psr/psr-12/).

## Code quality
### PHPStan
Run PHPStan using
```shell
./vendor/bin/phpstan
```

### PHPUnit
Run PHPUnit using
```shell
./vendor/bin/phpunit
```

## License
This package is distributed under [MIT license](https://github.com/oleg1540/entity-serializer/blob/master/LICENSE).