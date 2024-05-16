
![](art/file-cast-logo.jpg)

# Laravel File Cast 

Easily link your uploads with their db columns

### ✨ Features 

- Mapping uploaded files from the **Request** to the **Model** with ease.
- Old files are automatically cleaned on **column update** or **model deleted**.
- No extra code & No media table!

### 🔻 Installation

    composer require yassinedabbous/laravel-file-cast
    
### 🧑‍💻  Usage
Just cast any of your table columns with *FileCast::class* like that:
```php
use YassineDabbous\FileCast\FileCast;

class User extends Model
{
	# Laravel<11.x
    protected $casts = [
        'avatar' => FileCast::class,        // use default disk
        'avatar' => FileCast::class.':s3',  // use S3 disk
    ];

    # OR

    # Laravel >=11.x
    public function casts(): array
    {
        return [
            'avatar' => FileCast::class,                            // use default disk
            'avatar' => FileCast::using(disk: 's3'),                // use S3 disk
            'avatar' => FileCast::using(disk: $this->column_disk),  // use column value as a disk name
        ];
    }
}
```
This will cast the *avatar* column.


Assuming your request form has a file/image named *avatar*, you can assign the file to it's column:
```php
$model->avatar = $request->avatar;
$model->save();
```
Or fill the model:
```php
Model::create( $request->validated() );
// or
$model->update( $request->validated() );
```

It accept any type of file, so you can do that also:
```php
$model->avatar = '/full/path/to/your/file.ext';
$model->save();
```


What about deleting files ?
```php
$model->avatar = null;
```
It has never been easier!

### ⚙️ Configuration
    php artisan vendor:publish --tag=file-cast-config

You can set the default disk from the *file-cast* config file:

    'disk'  => 's3'

Or disable auto deleting:

    'auto_delete'  => FALSE



###### For Old Laravel Versions (< 11.x):

If you want to customize the disk for each column, you should modify your model with a **public** property/method that return an array containing the disk for each column:


```php
class Post extends Model
{
    ... 
    protected $casts = [
            'photo' => FileCast::class,
            'video' => FileCast::class,
    ];
    
	/** Set a disk for each column */
    public function disks(): array {
        return [
            'photo' => $this->column_disk ?? 'public', // use column value as disk
            'video' => 's3',
        ];
    }

    ...
}
```