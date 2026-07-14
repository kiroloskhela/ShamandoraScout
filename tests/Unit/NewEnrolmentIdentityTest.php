<?php

namespace Tests\Unit;

use App\Support\NewEnrolmentIdentity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Package A surrogate-id guard used by liveform enrolment inserts.
 */
class NewEnrolmentIdentityTest extends TestCase
{
    public function test_throws_when_surrogate_id_column_is_missing(): void
    {
        Schema::dropIfExists('NewUsersInformation_test_guard');
        Schema::create('NewUsersInformation_test_guard', function (Blueprint $table) {
            $table->integer('PersonID');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing Package A surrogate column');

        try {
            NewEnrolmentIdentity::assertSurrogateAutoIncrementId('NewUsersInformation_test_guard');
        } finally {
            Schema::dropIfExists('NewUsersInformation_test_guard');
        }
    }

    public function test_passes_when_id_column_exists_on_sqlite(): void
    {
        Schema::dropIfExists('NewUsersInformation_test_guard');
        Schema::create('NewUsersInformation_test_guard', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('PersonID');
        });

        try {
            NewEnrolmentIdentity::assertSurrogateAutoIncrementId('NewUsersInformation_test_guard');
            $this->assertTrue(true);
        } finally {
            Schema::dropIfExists('NewUsersInformation_test_guard');
        }
    }
}
