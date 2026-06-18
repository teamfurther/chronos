# Chronos CMS

A developer-friendly headless CMS built by [Further](https://gofurther.digital).

## Requirements

- PHP 8.3 or newer
- Laravel 13
- A database supported by Laravel
- The PHP GD extension for image processing

## Installation

### Install from Packagist

```bash
composer require further/chronos
```

### Register the service provider

Chronos does not currently use Laravel package auto-discovery. Add its service
provider to `bootstrap/providers.php`:

```php
<?php

use App\Providers\AppServiceProvider;
use Chronos\ChronosServiceProvider;

return [
    AppServiceProvider::class,
    ChronosServiceProvider::class,
];
```

Composer installs and discovers Chronos' third-party dependencies. Do not add
legacy Intervention Image or Laravel Collective providers and aliases to the
application.

## Configure Sanctum

Chronos uses Laravel Sanctum for API access tokens. Install Laravel's API
scaffolding:

```bash
php artisan install:api
```

If Artisan offers to run migrations, you may defer them until the migration
step below.

## Prepare the User model

Chronos expects `App\Models\User` to use Sanctum, notifications, and the
`ChronosUser` trait. A Laravel 13 User model can be configured as follows:

```php
<?php

namespace App\Models;

use Chronos\Traits\ChronosUser;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'email',
    'password',
    'firstname',
    'lastname',
    'picture',
    'role_id',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use ChronosUser;

    protected $appends = [
        'endpoints',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

Chronos' migration removes the default `users.name` column and adds
`firstname`, `lastname`, `picture`, and `role_id`. Remove `name` from the
model's fillable attributes before running the migrations. Update any
application factories, seeders, tests, or registration code that still writes
to `users.name`.

## Configure the Environment

Set the application's public URL and the absolute path to its public directory:

```dotenv
APP_URL=http://127.0.0.1:8000
UPLOAD_URL=/absolute/path/to/application/public
```

Chronos stores uploaded media below `UPLOAD_URL` and exposes it below
`APP_URL`.

## Publish Resources

Publish the package configuration:

```bash
php artisan vendor:publish --provider="Chronos\ChronosServiceProvider" --tag=config
```

Publish the browser assets:

```bash
php artisan vendor:publish --provider="Chronos\ChronosServiceProvider" --tag=public --force
```

The assets are published to `public/chronos`.

Views may optionally be published for customization:

```bash
php artisan vendor:publish --provider="Chronos\ChronosServiceProvider" --tag=views
```

After publishing, review `config/chronos.php`, especially `upload_paths`.

## Run Migrations and Seeders

Chronos automatically loads its package migrations. Ensure the application
already has Laravel's default `users` table, then run:

```bash
php artisan migrate
php artisan db:seed --class="Chronos\Database\Seeders\DatabaseSeeder"
```

For a disposable test application, starting with a clean database is safer:

```bash
php artisan migrate:fresh
php artisan db:seed --class="Chronos\Database\Seeders\DatabaseSeeder"
```

## Clear Caches

After installation or after changing the local package:

```bash
composer dump-autoload
php artisan optimize:clear
```

## Open the Admin Interface

Start the Laravel development server:

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000/admin/login
```

## Task Scheduling

Chronos registers a scheduled task that publishes content when its scheduled
time is reached.

During local development, run:

```bash
php artisan schedule:work
```

In production, run Laravel's scheduler every minute:

```cron
* * * * * cd /path-to-application && php artisan schedule:run >> /dev/null 2>&1
```

---

[https://gofurther.digital](https://gofurther.digital)

P.S.: You're awesome for being on this page
