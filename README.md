# Laravel Repository pattern creator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)
[![Total Downloads](https://img.shields.io/packagist/dt/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)

## !!! Warning !!!

If you are used older version than: v1.1.5 maybe you encounter 
some problems with the new version. Make sure you update your old files for the new abstraction!

## Installation

You can install the package via composer:

```bash
composer require pentacom/repgenerator
```

## Usage

To generate the files run 

```bash
php artisan pattern:generate 
    {name : Class (singular) for example User} 
    {--M|model : Whether the generator should generate a model}
    {--m|migration : Whether the generator should generate a migration}
    {--P|pivot : Whether the generator should generate a pivot repo or default to model}
    {--I|inertia : Whether the generator should generate a inertia controller or default to blade}
```

## Example


```bash
php artisan pattern:generate Book --model --migration
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


### Security

If you discover any security related issues, please email perlusz.david@pentacom.hu instead of using the issue tracker.

## Credits

- [Perlusz Dávid](https://github.com/pentacom)
- [Simon Tamás](https://github.com/pentacom)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
