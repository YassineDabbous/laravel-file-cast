<?php

namespace YassineDabbous\FileCast\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileFieldTest extends BaseTestCase
{
    public function test_can_call_storage_facade_methods(): void
    {
        Storage::fake('fake_disk');
        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg', 10, 10);

        $this->assertEquals($model->avatar->size(), 695);
        $this->assertEquals($model->avatar->url(), Storage::disk('fake_disk')->url($model->avatar));
        $this->assertEquals($model->avatar->toBase64(), '/9j/4AAQSkZJRgABAQEAYABgAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2ODApLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgACgAKAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A+f6KKKAP/9k=');
    }


    public function test_can_delete_file_with_delete_method(): void
    {
        Storage::fake('fake_disk');

        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo1.jpg');
        $model->save();
        $model->fresh();

        $path = $model->avatar;

        Storage::disk('fake_disk')->assertExists($path);

        $model->avatar->delete();
        
        Storage::disk('fake_disk')->assertMissing($path);
    }

    
    public function test_can_move_file(): void
    {
        Storage::fake('fake_disk');

        $model = new TestModel();
        $model->avatar = UploadedFile::fake()->image('photo10.jpg');

        $oldPath = $model->avatar;

        Storage::disk('fake_disk')->assertExists($oldPath);

        $model->avatar->move('photo20.jpg');

        Storage::disk('fake_disk')->assertMissing($oldPath);

        Storage::disk('fake_disk')->assertExists($model->avatar);
    }
}