<?php

namespace YassineDabbous\FileCast\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileCastTest extends BaseTestCase
{
    public function test_support_uploaded_file(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');

        Storage::disk('fake_disk')->assertExists($model->avatar);
    }

    public function test_support_remote_url(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = 'https://via.placeholder.com/150/FF0000/FFFFFF';

        Storage::disk('fake_disk')->assertExists($model->avatar);
    }

    public function test_support_base64_uri(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAANCSURBVEiJtZZPbBtFFMZ/M7ubXdtdb1xSFyeilBapySVU8h8OoFaooFSqiihIVIpQBKci6KEg9Q6H9kovIHoCIVQJJCKE1ENFjnAgcaSGC6rEnxBwA04Tx43t2FnvDAfjkNibxgHxnWb2e/u992bee7tCa00YFsffekFY+nUzFtjW0LrvjRXrCDIAaPLlW0nHL0SsZtVoaF98mLrx3pdhOqLtYPHChahZcYYO7KvPFxvRl5XPp1sN3adWiD1ZAqD6XYK1b/dvE5IWryTt2udLFedwc1+9kLp+vbbpoDh+6TklxBeAi9TL0taeWpdmZzQDry0AcO+jQ12RyohqqoYoo8RDwJrU+qXkjWtfi8Xxt58BdQuwQs9qC/afLwCw8tnQbqYAPsgxE1S6F3EAIXux2oQFKm0ihMsOF71dHYx+f3NND68ghCu1YIoePPQN1pGRABkJ6Bus96CutRZMydTl+TvuiRW1m3n0eDl0vRPcEysqdXn+jsQPsrHMquGeXEaY4Yk4wxWcY5V/9scqOMOVUFthatyTy8QyqwZ+kDURKoMWxNKr2EeqVKcTNOajqKoBgOE28U4tdQl5p5bwCw7BWquaZSzAPlwjlithJtp3pTImSqQRrb2Z8PHGigD4RZuNX6JYj6wj7O4TFLbCO/Mn/m8R+h6rYSUb3ekokRY6f/YukArN979jcW+V/S8g0eT/N3VN3kTqWbQ428m9/8k0P/1aIhF36PccEl6EhOcAUCrXKZXXWS3XKd2vc/TRBG9O5ELC17MmWubD2nKhUKZa26Ba2+D3P+4/MNCFwg59oWVeYhkzgN/JDR8deKBoD7Y+ljEjGZ0sosXVTvbc6RHirr2reNy1OXd6pJsQ+gqjk8VWFYmHrwBzW/n+uMPFiRwHB2I7ih8ciHFxIkd/3Omk5tCDV1t+2nNu5sxxpDFNx+huNhVT3/zMDz8usXC3ddaHBj1GHj/As08fwTS7Kt1HBTmyN29vdwAw+/wbwLVOJ3uAD1wi/dUH7Qei66PfyuRj4Ik9is+hglfbkbfR3cnZm7chlUWLdwmprtCohX4HUtlOcQjLYCu+fzGJH2QRKvP3UNz8bWk1qMxjGTOMThZ3kvgLI5AzFfo379UAAAAASUVORK5CYII=';

        Storage::disk('fake_disk')->assertExists($model->avatar);
    }

    public function test_support_json(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = '{"key1": "value1", "key2": "value2"}';

        Storage::disk('fake_disk')->assertExists($model->avatar);
        
        $this->assertStringEndsWith('.json', $model->avatar);
    }

    public function test_support_array_json(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();

        $array = ['key1' => 'value1', 'key2' => 'value2', 'key3' => ['1', '2', '3']];

        $model->avatar = $array;

        Storage::disk('fake_disk')->assertExists($model->avatar);
        
        $this->assertStringEndsWith('.json', $model->avatar);

        $this->assertEquals($model->avatar->toArray(), $array);
    }


    public function test_support_array_csv(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();

        $array = [
            ['value1', 'value2', 'value3'],
            ['value1', 'value2', 'value3'],
            ['value1', 'value2', 'value3'],
        ];

        $model->avatar = $array;

        Storage::disk('fake_disk')->assertExists($model->avatar);
        
        $this->assertStringEndsWith('.csv', $model->avatar);

        $this->assertEquals($model->avatar->toArray(), $array);
    }


    public function test_should_delete_file_when_column_updated(): void
    {
        Storage::fake('fake_disk');

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
        // disable auto delete
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