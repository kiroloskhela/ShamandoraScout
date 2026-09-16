<?php

namespace Tests\Unit\Support;

use App\Support\PersonAvatar;
use Tests\TestCase;

class PersonAvatarTest extends TestCase
{
    public function test_defaults_to_male_avatar_when_gender_missing(): void
    {
        $url = PersonAvatar::url(null, null);

        $this->assertStringContainsString('default-male.png', $url);
    }

    public function test_uses_female_avatar_for_female_gender(): void
    {
        $this->assertStringContainsString('default-female.png', PersonAvatar::url(null, 'Female'));
        $this->assertStringContainsString('default-female.png', PersonAvatar::url(null, 'أنثى'));
    }

    public function test_uses_male_avatar_for_male_gender(): void
    {
        $this->assertStringContainsString('default-male.png', PersonAvatar::url(null, 'Male'));
    }

    public function test_prefers_stored_photo_path(): void
    {
        $url = PersonAvatar::url('person_images/demo.jpg', 'Female');

        $this->assertStringContainsString('storage/person_images/demo.jpg', $url);
        $this->assertStringNotContainsString('default-female.png', $url);
    }

    public function test_photo_url_is_null_without_a_stored_local_photo(): void
    {
        $this->assertNull(PersonAvatar::photoUrl(null));
        $this->assertNull(PersonAvatar::photoUrl(''));
        $this->assertNull(PersonAvatar::photoUrl('https://evil.test/x.jpg'));
    }

    public function test_local_file_rejects_http_and_path_traversal(): void
    {
        $this->assertNull(PersonAvatar::localFile('https://evil.test/x.jpg'));
        $this->assertNull(PersonAvatar::localFile('../../../etc/passwd'));
    }

    public function test_local_file_resolves_photo_under_public_storage(): void
    {
        $dir = storage_path('app/public/persons/personal');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir.'/export-avatar-test.png';
        file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        try {
            $this->assertSame(realpath($file), PersonAvatar::localFile('persons/personal/export-avatar-test.png'));
        } finally {
            @unlink($file);
        }
    }
}
