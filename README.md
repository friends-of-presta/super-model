# PrestaShop Super Model

## Requirements

You need a Shop with PrestaShop 1.6.1+, Composer and PHP 7.1 at least.

## Installation

Get the module here and move it into the `modules` folder of your Shop.

Then, you need to install the vendors of the module:

``
cd modules/products && composer install
``

Then install it using the command line (if you're using PrestaShop 1.7.5+):

``
php bin/console prestashop:module install products
``

Or using the Back Office.

## Features demonstrated in the module

### Object Model self validation

If your Object Model extends the `SuperModel` abstract class, you don't need
to override `Validate` class in order to implement a new validation rule.

This is an exemple of what you can do:

```php
<?php

/**
 * Implements the Validation Rules into your
 * models !
 */
class Person extends SuperModel
{
    public $id;
    public $name;
    public $age;
    public $gender;
 
    public static $definition = [
        'table' => 'a',
        'primary' => 'id',
        'fields' => [
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'Person::isString'
            ],
            'age' => [
                'type' => self::TYPE_INT,
                'validate' => 'Person::isAdult'
            ],
            'gender' => [
                'type' => self::TYPE_STRING,
                'validate' => 'PersonValidator::isValidGender',
            ]
        ]
    ];

    public static function isAdult($value)
    {
        return $value >= 18;
    }
}

/**
 * Or create your own Validator classes !
 */
class PersonValidator
{
    public static function isValidGender($value)
    {
        return in_array($value, [
            'MALE',
            'FEMALE',
            'NOT_BINARY',
        ]);
    }
}
```

Feel free to contribute and let me know if you need more improvements !
