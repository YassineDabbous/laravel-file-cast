
![](art/file-cast-logo.jpg)

# Laravel File Cast 

Easily link your uploads with their table columns



## Table of Content
- [Features](#-features)
- [Installation](#-installation)
- [Usage](#--usage)
    - [Supported data formats](#%EF%B8%8F-supported-data-formats)
        - [Uploaded file](#-uploaded-file)
        - [Local file path](#-local-file-path)
        - [Remote file url](#-remote-file-url)
        - [Base64 URI](#--base64-uri)
        - [Json string](#--json-string)
        - [Arrays (Json & CSV)](#--array-as-json-or-csv)
        - [NULL ?](#--null-)
    - [Functionalities](#%EF%B8%8F-functionalities)
        - [Storage Proxy](#-storage-proxy)
        - [Additional Methods](#-additional-methods)
        - [File Manipulation (Move & Delete)](#-file-manipulation)
        - [Extending](#-extending)
- [Configuration](#%EF%B8%8F-configuration)



<br>


## ✨ Features 

- Mapping uploaded files from the **Request** to the **Model** with ease.
- Dealing with **multiple forms of data** (File instance, Local path, Remote url, Base64 URI, Array values ...).
- Old files are **automatically cleaned** on update and delete events.
- No extra code & No media table!







<br>

## 🔻 Installation

    composer require yassinedabbous/laravel-file-cast
    









<br>
    
## 🧑‍💻  Usage

Just cast any of your table columns with *FileCast::class* like that:
```php
use YassineDabbous\FileCast\FileCast;

class User extends Model
{
	# Laravel<11.x
    protected $casts = [
        'avatar' => FileCast::class,                    // use default disk
        'avatar' => FileCast::class.':s3,photo.png',    // use "S3" disk and "photo.png" as default value
    ];

    # OR

    # Laravel >=11.x
    public function casts(): array
    {
        return [
            'avatar' => FileCast::class,                                    // use default disk
            'avatar' => FileCast::using(disk: 's3', default: 'photo.png'),  // use S3 disk and "photo.png" as default value
            'avatar' => FileCast::using(disk: $this->disk_column),          // use column value as a disk name
        ];
    }
}
```
This will cast the *avatar* column.


#### For Old Laravel Versions (< 11.x):

If for any reason you want to customize the disk with a dynamic value, you should modify your model with a **public** method that returns an array containing the disk name for each column:


```php
class Post extends Model
{
    ... 
    
	/** Set a disk for each column */
    public function disks(): array {
        return [
            'photo' => $this->disk_column,              # use column value as a disk
            'video' => $migrated ? 's3' : 'public',     # conditional disk 
        ];
    }

    ...
}
```








<br>

### 🗃️ Supported data formats

#### • Uploaded file:
Assuming your request form has a file/image named *"avatar"*, you can assign the file to it's column:
```php
$model->avatar = $request->avatar;
$model->save();
```
Or even fill the model with request input:
```php
Model::create( $request->validated() );
// or
$model->update( $request->validated() );
```

It accept any type of file, so you can do that also:

#### • Local file path:
```php
$model->avatar = '/full/path/to/local/file.ext';

$model->avatar; // /disk/folder/file.ext
```

#### • Remote file url:
```php
$model->avatar = 'https://via.placeholder.com/150/FF0000/FFFFFF';

$model->avatar; // /disk/folder/file.png
```

#### •  Base64 URI:
```php
$model->avatar = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAAWSURBVBhXY/zPwABEDAxMIIKBgYEBAB8XAgIcVRdpAAAAAElFTkSuQmCC';

$model->avatar; // /disk/folder/file.png
```

#### •  JSON string:
```php
$model->avatar = '{"key1": "value1"}';

$model->avatar; // /disk/folder/file.json
```


#### •  Array as JSON or CSV:
```php
# Store associative array as *json* file
# => folder/file.json
$model->avatar = ['key1' => 'value1', 'key2' => 'value2'];

# Store multi list array as *csv* file
# => folder/file.csv
$model->avatar = [
            ['value1', 'value2', 'value3'],
            ['value1', 'value2', 'value3'],
            ['value1', 'value2', 'value3'],
        ];
```



#### •  NULL ?
Null value will cause automatic file deletion (configurable):
```php
$model->avatar = null; # == $model->avatar->delete();
```

It has never been easier!

<br>
<br>


### 🛠️ Functionalities:
To provide more functionalities to file caster, this package uses a wrapper class name FileField, this class works as a proxy to the Storage facade with some additional methods.


#### • Storage Proxy
As a proxy to the Laravel [*Storage*](https://laravel.com/docs/master/filesystem) facade, you can call any method from *Storage* directly on the file field without providing the file path:
  
```php
use Illuminate\Support\Facades\Storage;

# using Laravel Storage Facade:
Storage::disk('disk')->url(path: 'path.ext');
Storage::disk('disk')->json(path: 'path.ext');
Storage::disk('disk')->put(path: 'path.ext', contents: 'data');
Storage::disk('disk')->temporaryUrl(path: 'path.ext', expiration: now()->addMinutes(5));
...

# using File Cast
$model->avatar->url();
$model->avatar->json();
$model->avatar->put(contents: 'data');
$model->avatar->temporaryUrl(expiration: now()->addMinutes(5));
...
```

<br>

#### • Additional Methods
In addition to *Storage* methods, *FileField* comes with some useful methods:

```php

$model->avatar->toBase64();             # Base64 string: 'iVBORw0KGgoAAAANS...'
$model->avatar->toBase64URI();          # Base64 URI: 'data:image/png;base64,iVBORw0KGgoAAAANS...'


$model->avatar->toArray();              # returns *json* and *csv* file's content as array

```

<br>

#### • File Manipulation

- Deleting file:

Old files are cleaned automatically when column value updated.
```php
$model->avatar; 
# 'old_file.png';

$model->avatar = $newFileUploaded;
# 'old_file.png' deleted!
```

To automatically delete old files on Model *deleted* event, add **HasFileCast** trait to your model.
```php
use YassineDabbous\FileCast\HasFileCast;

class User extends Model
{
    use HasFileCast;
    ...
}
```

To delete files manually:

```php
$model->avatar = NULL;                                      # Delete the file without updating table.
$model->avatar->delete();                                   # Delete the file without updating table.
$model->avatar->delete(persist: TRUE);                      # Delete the file & save changes to DB.
```  
  
<br>
<br>
  
- Moving file to new path:

```php
$model->avatar; 
# 'folder/old_file.png';

$model->avatar = '@new_folder/new_file.png';                           # Move the file without updating table. (path should start with "@")
$model->avatar->move(to: 'new_folder/new_file.png');                   # Move the file without updating table.
$model->avatar->move(to: 'new_folder/new_file.png', persist: TRUE);    # Move the file & save changes to DB.
```

<br>
<br>

#### • Extending
File Cast is "macroable", which allows you to add additional methods to *FileField* class at run time. The *FileField* class' macro method accepts a closure that will be executed when your macro is called. The macro closure may access the FileField's other methods via *$this*, just as if it were a real method of the FileField class. For example, the following code adds a *resize* method to the FileField class:

```php
use YassineDabbous\FileCast\FileField;
 
FileField::macro('resize', function ($with, $height) {
    $image = imagecreatefrompng($this->path());
    $imgResized = imagescale($image , $with, $height);
    imagejpeg($imgResized, $this->path()); 
    return $this;
});
 
# resize uploaded image 
$model->photo = $request->file('image');
$model->photo->resize(500, 500);
```







<br>
<br>





## ⚙️ Configuration

You can optionally publish the [config file](src/config.php) with:

    php artisan vendor:publish --tag=file-cast-config


These are the contents of the default config file that will be published:

```php
<?php

return [
    /** Default value when no file uploaded. */
    'default' => env('FILE_CAST_DEFAULT'),

    /** Default storage disk */
    'disk' => env('FILE_CAST_DISK'),

    /** Default storage folder. If NULL, the Model's table name will be used. */
    'folder' => env('FILE_CAST_FOLDER'),

    /** Automatically clean files on column value updated. */
    'auto_delete' => env('FILE_CAST_AUTO_DELETE', TRUE),
];

```


