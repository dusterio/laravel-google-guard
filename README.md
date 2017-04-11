# laravel-google-guard
[![Latest Stable Version](https://poser.pugx.org/dusterio/laravel-google-guard/v/stable)](https://packagist.org/packages/dusterio/laravel-google-guard)
[![Total Downloads](https://poser.pugx.org/dusterio/laravel-google-guard/downloads)](https://packagist.org/packages/dusterio/laravel-google-guard)
[![License](https://poser.pugx.org/dusterio/laravel-google-guard/license)](https://packagist.org/packages/dusterio/laravel-google-guard)

Auth guard for Laravel that completely relies on Google Login and doesn't persist

## Overview

Sometimes your small application doesn't need local persisted user repository at all. At times like that,
it's nice to rely on external account repository, eg. Google – nowadays practically everybody got a Google account?

This authentication guard relies on Google token. Every time your app needs to authenticate a user, it will redirect user
to Google, then back. If the token is valid, your user will be considered authenticated within your app.

No persistence at all, just some session caching.

And you can of course specify allowed Google users or domains if your application is private. 

## Dependencies

- Laravel Socialite
- Laravel 5.3.*

## Installation

First install the package using Packagist:
```
$ composer require dusterio/laravel-google-guard
```

Add the package service provider to your `config/app.php` to `providers` array:
```php
Dusterio\LaravelGoogleGuard\Integrations\LaravelServiceProvider::class,
```

You should see two extra routes after this:
```bash
$ php artisan route:list
| GET|HEAD | auth/google            |                   | Dusterio\LaravelGoogleGuard\Http\LoginController@redirectToProvider     | guest,web    |
| GET|HEAD | auth/google/callback   |                   | Dusterio\LaravelGoogleGuard\Http\LoginController@handleProviderCallback | guest,web    |
```

Configure the guard in ```config/auth.php```:
```php
    'guards' => [
        /// Your existing guards
        /// ...    
        'google' => [
            /*
             * For consistency, return a dummy (not persisted) class holder.
            */
            'userClass' => '\App\User',

            /*
             * Remember users for this number of seconds.
             */
            'timeout' => 3600,

            /*
             * Users that can use the app. If left empty, everybody is allowed.
             */
            'whitelist' => [
                'admin@.',
            ]
        ]              
    ]
```

Make it a default guard in the same file ```config/auth.php```:
```php
        'web' => [
            'driver' => 'google',
            'provider' => 'users',
        ],
```

Add your google key and secret to ```config/services.php```, eg.:
```php
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL') . '/auth/google/callback',
    ],
```

That's it – you are ready to go!

## License

MIT License

Copyright (c) 2017 Denis Mysenko

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
