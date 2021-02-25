# Laravel Repository pattern creator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)
[![Build Status](https://img.shields.io/travis/pentacom/repgenerator/master.svg?style=flat-square)](https://travis-ci.org/pentacom/repgenerator)
[![Quality Score](https://img.shields.io/scrutinizer/g/pentacom/repgenerator.svg?style=flat-square)](https://scrutinizer-ci.com/g/pentacom/repgenerator)
[![Total Downloads](https://img.shields.io/packagist/dt/pentacom/repgenerator.svg?style=flat-square)](https://packagist.org/packages/pentacom/repgenerator)



## Installation

You can install the package via composer:

```bash
composer require pentacom/repgenerator
```

## Usage

To generate the files run 

`php artisan pattern:generator {name : Class (singular) for example User}`

New files will be:

* app/Models/{$name}.php
* app/Http/Controllers/Web/{$name}Controller.php
* app/Http/Controllers/Api/{$version}/{$name}ApiController.php
* app//Http/Requests/{$name}Request.php
* app/Http/Requests/{$name}UpdateRequest.php
* app/Domain/{$name}/Repositories/Eloquent{$name}Repository.php
* app/Domain/{$name}/Repositories/Interfaces/{$name}RepositoryInterface.php
* app/Domain/{$name}/Services/{$name}Service.php
* app/Domain/{$name}/ViewModel/{$name}.php
* app/Domain/{$name}/ViewModel/Transformers/{$name}Transformer.php
* views/{$name}/index.blade.php
* views/{$name}/edit.blade.php
* views/{$name}/create.blade.php

Also 2 new routes will register in the api.php and web.php

>Web.php:
`Route::resource('{$name}', {$name}Controller::class)->only(['index', 'create', 'edit']);`

>Api.php:
`Route::resource('{$name}', {$name}ApiController::class)->only(['store', 'update', 'destroy']);`

## TODOS
* base migration for resource
* rollback command to delete all files
* tests
### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email perlusz.david@pentacom.hu instead of using the issue tracker.

## Credits

- [Perlusz Dávid](https://github.com/pentacom)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
