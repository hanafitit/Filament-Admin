<?php

namespace Tests\Feature;

use App\Support\Uploads\OrderAttachmentUpload;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Tests\CreatesApplication;

class FileUploadValidationTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test/upload-too-large', function () {
            throw new PostTooLargeException('Payload too large.');
        })->middleware('web');
    }

    public function test_web_requests_get_human_readable_validation_error_for_oversized_uploads(): void
    {
        $response = $this
            ->from('http://localhost/admin/orders/create')
            ->post('/test/upload-too-large');

        $response
            ->assertRedirect('http://localhost/admin/orders/create')
            ->assertSessionHas('errors', function (ViewErrorBag $errors): bool {
                return $errors->getBag('default')->has('file');
            });
    }

    public function test_json_requests_get_validation_payload_for_oversized_uploads(): void
    {
        $response = $this->postJson('/test/upload-too-large');

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => OrderAttachmentUpload::uploadTooLargeMessage(),
                'errors' => [
                    'file' => [OrderAttachmentUpload::uploadTooLargeMessage()],
                ],
            ]);
    }

    public function test_invalid_file_type_message_lists_allowed_extensions(): void
    {
        $this->assertSame(
            'Недопустимый формат файла. Разрешены: pdf, zip, doc, docx, xls, xlsx, jpg, jpeg, png, txt.',
            OrderAttachmentUpload::invalidTypeMessage(),
        );
    }
}
