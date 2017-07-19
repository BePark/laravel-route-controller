# Laravel Route Controller

Get back the possibility to define Route::controller() inside laravel routes.

## Usage
```php
// in the routes/web.php

Route::group(['middleware' => 'auth'], function()
{
	Route::controller('stuff', App\Controller\StuffController::class);
});
```

## Installation using composer

```bash
composer require bepark/laravel-route-controller
```

Setup the service provider:

```php
'providers' => [
    ...
    BePark\Libs\Laravel\RouteController\Provider\RouteControllerServiceProvider::class,
];
```

That's all

## Todo

* Automated tests
* Add more docs than a simple readme

## License

The MIT License (MIT). See the [license](LICENSE) file for more information.
