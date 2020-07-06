<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class TransferTemplate
 * @package App\Models
 * @property string id
 * @property bool is_complete
 * @property bool completed
 * @property Carbon expires_at
 * @property string access_token
 * @property TransferTemplate template
 * @property TransferredFile[]|Collection files
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Transfer extends BaseModel
{
    protected $with = [
        'files',
        'template',
    ];

    protected $visible = [
        'id',
        'is_complete',
        'completed',
        'expires_at',
        'template',
        'files',
        'created_at',
    ];

    protected $dates = [
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'is_complete',
    ];

    /**
     * Transfer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->access_token = Str::random(32);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function (Transfer $transfer) {
            $transfer->purge();
        });
    }

    public function template()
    {
        return $this->belongsTo(TransferTemplate::class, 'transfer_template_id');
    }

    public function files()
    {
        return $this->hasMany(TransferredFile::class);
    }

    public function getStoragePath()
    {
        return 'files/' . $this->id;
    }

    private function purge()
    {
        if (Storage::exists($this->getStoragePath())) {
            Storage::deleteDirectory($this->getStoragePath());
        }
    }

    public function getIsCompleteAttribute()
    {
        foreach ($this->template->fields as $field) {
            // Everything else (MIME, size, max files etc. is checked at upload-time)
            $fileCount = TransferredFile::query()->where('transfer_template_field_id', $field->id)->count();
            if ($fileCount < $field->min_count) {
                return false;
            }
        }

        return true;
    }
}
