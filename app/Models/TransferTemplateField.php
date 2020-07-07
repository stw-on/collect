<?php


namespace App\Models;

/**
 * Class TransferTemplate
 * @package App\Models
 * @property string id
 * @property string name
 * @property string description
 * @property array allowed_mimes
 * @property int min_count
 * @property int max_count
 * @property int max_size_kb
 * @property TransferTemplate template
 */
class TransferTemplateField extends BaseModel
{
    protected $casts = [
        'allowed_mimes' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'allowed_mimes',
        'min_count',
        'max_count',
        'max_size_kb',
    ];

    public function template()
    {
        return $this->belongsTo(TransferTemplate::class, 'transfer_template_id');
    }
}
