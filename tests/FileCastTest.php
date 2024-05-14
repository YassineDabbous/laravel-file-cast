<?php

namespace YassineDabbous\FileCast\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileCastTest extends BaseTestCase
{
    public function test_file_should_be_saved_to_disk(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');
        // $model->save();
        Storage::disk('fake_disk')->assertExists($model->avatar);
    }

    
    public function test_should_delete_file_when_column_updated(): void
    {
        Storage::fake('fake_disk');

        // $model = TestModel::first();
        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');
        $model->save();
        $model->fresh();


        $path = $model->avatar;

        Storage::disk('fake_disk')->assertExists($path);

        $model->avatar = null;
        
        Storage::disk('fake_disk')->assertMissing($path);
    }

    
    
    public function test_should_delete_file_when_model_deleted(): void
    {
        Storage::fake('fake_disk');

        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');
        $model->save();
        $model->fresh();

        $path = $model->avatar;

        Storage::disk('fake_disk')->assertExists($path);

        $model->delete();
        
        Storage::disk('fake_disk')->assertMissing($path);
    }


    public function test_should_not_delete_file_when_config_turned_off(): void
    {
        // enable auto delete
        config([
            'file-cast.auto_delete' => false 
        ]);

        Storage::fake('fake_disk');

        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');
        $model->save();
        $model->fresh();

        $path = $model->avatar;

        Storage::disk('fake_disk')->assertExists($path);

        $model->delete();
        
        Storage::disk('fake_disk')->assertExists($path);
    }
}