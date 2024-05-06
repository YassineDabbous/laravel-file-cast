
![](art/file-cast-logo.jpg)

# Laravel File Cast

Easily link your uploads with their db columns


**Table of Contents**

[TOC]

### Features

- Map your uploaded files from the **Request** to the **Model** with ease.
- Old files are automatically cleaned.
- No need for more tables!

### Installation

    composer require yaseen/laravel-file-cast
    
### Usage
Just cast any of your table columns with FileCast::class like that:
```php
use Yaseen\FileCast\FileCast;

class User extends Model
{
    protected $casts = [
        'avatar' => FileCast::class,
    ];
}
```
This will cast the *avatar* column.


Assuming your request form has a file/image named *avatar*, you can do that:
```php
$model->avatar = $request->avatar;
$model->save();
```
or even that:
```php
Model::create( $request->validated() );
// or
$model->update( $request->validated() );
```

What about deleting files ?
```php
$model->avatar = null;
```
It has never been easier!

### Configuration
    php artisan vendor:publish --tag=file-cast-config



