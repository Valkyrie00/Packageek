# Packageek

## Install

You can install this package via composer using:

``` bash
composer require valkyrie/packageek
```

You must also install this service provider.
**Laravel 5**

```php

//config/app.php

'providers' => [
    ...
    Valkyrie\Packageek\PackageekServiceProvider::class,
    ...
];
```