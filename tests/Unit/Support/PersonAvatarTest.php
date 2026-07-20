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
}
