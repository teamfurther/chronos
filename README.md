# Chronos CMS

---

A developer friendly headless CMS built by [Further](https://gofurther.digital).

---

## Installation

It's as easy as:

    composer require further/chronos

After composer has run add following line to the providers[] array in ```app/config/app.php```:

```php
...
Chronos\ChronosServiceProvider::class,
...
```


### Install dependencies

You also need to add the service providers for all the dependencies in ```app/config/app.php```:

```php
...
Collective\Html\HtmlServiceProvider::class,
Intervention\Image\ImageServiceProvider::class,
Lavary\Menu\ServiceProvider::class,
...
```

And also add the class aliases in the ```$aliases[]``` array:

```php
...
'Form' => Collective\Html\FormFacade::class,
'Html' => Collective\Html\HtmlFacade::class,
'Image' => Intervention\Image\Facades\Image::class,
'Menu' => Lavary\Menu\Facade::class,
...
```


### Publish assets

Next we need to publish all the assets belonging to Chronos:

	php artisan vendor:publish --tag=public

Note 1: if you would like to overwrite existing files, use the --force switch
Note 2: if you wish to only publish Chronos assets, you might want to use the --provider flag.


### Prepare User model

Next we need to prepare the User model to work with Chronos.

1. Add the ChronosUser trait to our model:

```php
...
use ChronosUser;
...
```

2. Next, add some values to the appends[] array:

```php
...
/**
 * The accessors to append to the model's array form.
 *
 * @var array
 */
protected $appends = ['endpoints', 'name'];
...
```


### Set APP_URL

Chronos requires you to set APP_URL in your .env file

```
APP_URL=https://chronos.ro
```


### Run migrations

Almost done. We need to run the migrations and seed our database:

```
php artisan migrate
php artisan db:seed --class=\\Chronos\\Database\\Seeders\\DatabaseSeeder
```


### Set up task scheduling

Chronos runs a couple of tasks in the background, so you will need to set up task scheduling by adding the following to your Cron entries on your server:

```
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```


### Install and configure Sanctum

1. Publish Sanctum configuration

```
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

2. Run migration if you haven't done so yet

```
php artisan migrate
```

---
[https://gofurther.digital](https://gofurther.digital)

P.S.: You're awesome for being on this page