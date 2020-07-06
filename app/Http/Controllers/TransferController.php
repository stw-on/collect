<?php


namespace App\Http\Controllers;


use App\Mail\TransmissionCompleteMail;
use App\Models\Transfer;
use App\Models\TransferredFile;
use App\Models\TransferTemplate;
use App\Models\TransferTemplateField;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use SoareCostin\FileVault\Facades\FileVault;
use SoareCostin\FileVault\FileEncrypter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransferController extends Controller
{
    public function create(string $templateId)
    {
        /** @var TransferTemplate $template */
        $template = TransferTemplate::query()->findOrFail($templateId);

        $transfer = new Transfer();
        $transfer->template()->associate($template);
        $transfer->expires_at = Carbon::now()->addMinutes($template->retention_minutes);
        $transfer->save();

        return response()->json([
            'transfer' => $transfer,
            'key' => base64url_encode(FileVault::generateKey()),
        ]);
    }

    /**
     * @param string $id
     * @param string $fieldId
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function upload(string $id, string $fieldId)
    {
        $this->validateWith([
            'file' => 'file|required',
            'key' => 'string|required',
        ]);

        $virusValidator = $this->getValidationFactory()->make(request()->all(), [
            'file' => 'clamav',
        ]);

        if ($virusValidator->fails()) {
            throw new BadRequestHttpException('File contains virus', null, 0, [
                'X-Collect-Error' => 'virus',
            ]);
        }

        /** @var Transfer $transfer */
        $transfer = Transfer::query()->findOrFail($id);

        if ($transfer->completed) {
            throw new BadRequestHttpException('Cannot push files to a completed transfer');
        }

        /** @var TransferTemplateField $templateField */
        $templateField = $transfer->template->fields()->findOrFail($fieldId);

        $existingFieldCount = $transfer->files()
            ->where('transfer_template_field_id', $templateField->id)
            ->count();

        if ($existingFieldCount >= $templateField->max_count) {
            throw new BadRequestHttpException('Maximum file count reached for this field');
        }

        $file = new TransferredFile();

        DB::transaction(function () use ($templateField, $transfer, &$file) {
            $file->transfer()->associate($transfer);
            $file->templateField()->associate($templateField);

            $uploadedFile = request()->file('file');

            if (!empty($templateField->allowed_mimes)) {
                if (!in_array($uploadedFile->getMimeType(), $templateField->allowed_mimes)) {
                    throw new BadRequestHttpException('Invalid MIME type', null, 0, [
                        'X-Collect-Error' => 'invalid_mime',
                    ]);
                }
            }

            if ($uploadedFile->getClientOriginalName() === null) {
                throw new BadRequestHttpException('The client needs to supply an original filename');
            }

            $file->filename = $uploadedFile->getClientOriginalName();
            $file->mime = $uploadedFile->getMimeType();
            $file->size = $uploadedFile->getSize();

            if ($uploadedFile->getSize() / 1024 > $templateField->max_size_kb) {
                throw new BadRequestHttpException('File is too big for this field', null, 0, [
                    'X-Collect-Error' => 'file_too_big',
                ]);
            }

            $file->save();

            $destinationFilename = $file->getStoragePath();
            Storage::put($destinationFilename, 'this is a non-finished upload');
            $destinationPath = Storage::path($destinationFilename);

            $key = base64url_decode(request('key'));

            $encrypter = new FileEncrypter($key, 'AES-256-CBC');
            if (!$encrypter->encrypt($uploadedFile->getRealPath(), $destinationPath)) {
                throw new Exception('Error while encrypting file.');
            }
        });

        return response()->json($file);
    }

    public function get(string $id)
    {
        /** @var Transfer $transfer */
        $transfer = Transfer::query()
            ->where('completed', false)
            ->findOrFail($id);

        return response()->json($transfer);
    }

    public function getWithAccessToken(string $id, string $accessToken)
    {
        /** @var Transfer $transfer */
        $transfer = Transfer::query()
            ->where('completed', true)
            ->where('access_token', $accessToken)
            ->findOrFail($id);

        return response()->json($transfer);
    }

    public function complete(string $id)
    {
        $this->validateWith([
            'key' => 'string|required',
        ]);

        DB::transaction(function () use ($id) {
            /** @var Transfer $transfer */
            $transfer = Transfer::query()->findOrFail($id);

            if (!$transfer->is_complete) {
                throw new BadRequestHttpException('This transfer has incomplete fields');
            }

            $transfer->completed = true;
            $transfer->save();

            Mail::to($transfer->template->recipient_mail)
                ->send(new TransmissionCompleteMail($transfer, request('key')));
        });

        return response()->json();
    }

    public function download(string $id, string $accessToken, string $fileId)
    {
        $this->validateWith([
            'key' => 'string|required',
        ]);

        /** @var Transfer $transfer */
        $transfer = Transfer::query()
            ->where('completed', true)
            ->where('access_token', $accessToken)
            ->findOrFail($id);

        /** @var TransferredFile $file */
        $file = $transfer->files()->findOrFail($fileId);

        $key = base64url_decode(request('key'));
        $encrypter = new FileEncrypter($key, 'AES-256-CBC');

        return response()->streamDownload(function () use ($file, $encrypter) {
            $encrypter->decrypt(Storage::path($file->getStoragePath()), 'php://output');
        }, $file->filename, [
            'X-Collect-File-Size' => $file->size,
        ]);
    }
}
