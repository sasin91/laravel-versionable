# Enable versioning of your eloquent models.

This package makes it a breeze to version your eloquent models, allowing the developer(s) to easily revert to a previous state or even ressurect a deleted model!

## Installation

You can install the package via composer:

```bash
composer require sasin91/laravel-versionable
php artisan vendor:publish --provider="Sasin91\LaravelVersionable\VersionableServiceProvider"
```

## Configuration
Edit the versionable.php file in your config directory, after publishing.

## Usage
Use the ```Sasin91\LaravelVersionable\Versionable``` trait in your eloquent models.

### example
``` php
use Illuminate\Database\Eloquent\Model as Eloquent;
use Sasin91\LaravelVersionable\Versionable;

class YourModel extends Eloquent 
{
	use Versionable;
	//
}
```

### Testing

``` bash
composer test
```

### Security

If you discover any security related issues, please email jonas.kerwin.hansen@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.