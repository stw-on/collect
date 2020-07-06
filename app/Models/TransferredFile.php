<?php


namespace App\Models;

use Illuminate\Support\Facades\Storage;

/**
 * Class TransferTemplate
 * @package App\Models
 * @property string id
 * @property Transfer transfer
 * @property TransferTemplateField templateField
 * @property string filename
 * @property string mime
 * @property int size
 */
class TransferredFile extends BaseModel
{
    protected $with = [
        'templateField'
    ];

    protected $touches = [
        'transfer',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function templateField()
    {
        return $this->belongsTo(TransferTemplateField::class, 'transfer_template_field_id');
    }

    public function getStoragePath()
    {
        return $this->transfer->getStoragePath() . '/' . $this->id;
    }

    public function purge()
    {
        if (Storage::exists($this->getStoragePath())) {
            Storage::delete($this->getStoragePath());
        }
    }
}
