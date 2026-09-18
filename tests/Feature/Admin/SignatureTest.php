<?php

namespace Tests\Feature\Admin;

use App\Models\Upload;
use App\Models\User;
use App\Support\CompanySignature;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('private');
    }

    public function test_user_can_upload_signature_in_profile(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('sig.png', 200, 100);

        $this->actingAs($user)
            ->post(route('profile.signature.update'), ['signature' => $file])
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh()->signature);
        $this->assertDatabaseHas('uploads', [
            'uploadable_type' => User::class,
            'uploadable_id' => $user->id,
            'collection' => 'signature',
        ]);
    }

    public function test_company_signature_current_returns_latest(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        Upload::create([
            'uploadable_type' => User::class,
            'uploadable_id' => $admin->id,
            'collection' => 'company_signature',
            'disk' => 'private',
            'path' => 'signatures/company/sig.png',
            'original_name' => 'sig.png',
            'mime_type' => 'image/png',
            'size' => 1024,
            'uploaded_by' => $admin->id,
        ]);

        $this->assertNotNull(CompanySignature::current());
        $this->assertEquals('company_signature', CompanySignature::current()->collection);
    }
}
