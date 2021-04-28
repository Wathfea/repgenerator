# Laravel Repository pattern creator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)
[![Total Downloads](https://img.shields.io/packagist/dt/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)



## Installation

You can install the package via composer:

```bash
composer require pentacom/repgenerator
```

## Usage

To generate the files run 

`php artisan pattern:generate {name : Class (singular) for example User} {--M|model : Whether the generator should generate a model}'`

## TODOS
* base migration for resource
* rollback command to delete all files
* tests

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email perlusz.david@pentacom.hu instead of using the issue tracker.

## Credits

- [Perlusz Dávid](https://github.com/pentacom)
- [Simon Tamás](https://github.com/pentacom)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
