<?php
namespace EvolutionCMS\ParamsToViewAndSphinx\Models;

use Illuminate\Database\Eloquent;

/**
 * EvolutionCMS\Ddafilters\Models\ActivateSms
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 *
 * @mixin \Eloquent
 */
class CategoryToView extends Eloquent\Model
{
    protected $table = 'category_to_view';

    protected $fillable = [
        'resource_id',
        'category_id',
        'view_name',
        'check',
    ];
}

