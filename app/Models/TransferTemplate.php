<?php


namespace App\Models;

use Ramsey\Collection\Collection;

/**
 * Class TransferTemplate
 * @package App\Models
 * @property string id
 * @property string name
 * @property string description
 * @property int retention_minutes
 * @property string recipient_mail
 * @property TransferTemplateField[]|Collection fields
 */
class TransferTemplate extends BaseModel
{
    protected $with = [
        'fields',
    ];

    protected $fillable = [
        'name',
        'description',
        'retention_minutes',
        'recipient_mail',
    ];

    public function fields()
    {
        return $this->hasMany(TransferTemplateField::class);
    }
}
