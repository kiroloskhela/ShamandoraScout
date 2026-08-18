<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class FormLabelPlaceholderTest extends TestCase
{
    public function test_views_do_not_use_hashed_form_label_placeholders(): void
    {
        $hits = [];
        $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

        foreach ($views as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $text = file_get_contents($file->getPathname());
            if ($text !== false && preg_match('/Form label [a-f0-9]{8}/', $text)) {
                $hits[] = $file->getPathname();
            }
        }

        $this->assertSame([], $hits, 'Hashed i18n placeholders leaked into form-card labels.');
    }
}
