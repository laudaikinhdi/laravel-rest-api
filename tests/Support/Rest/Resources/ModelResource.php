<?php

namespace Lomkit\Rest\Tests\Support\Rest\Resources;

use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Http\Resource;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\BelongsToMany;
use Lomkit\Rest\Relations\HasMany;
use Lomkit\Rest\Relations\HasManyThrough;
use Lomkit\Rest\Relations\HasOne;
use Lomkit\Rest\Relations\HasOneThrough;
use Lomkit\Rest\Tests\Support\Models\Model;

class ModelResource extends Resource
{
    public static $model = Model::class;

    public function relations(RestRequest $request)
    {
        return [
            HasOne::make('hasOneRelation', HasOneResource::class),
            BelongsTo::make('belongsToRelation', BelongsToResource::class),
            HasMany::make('hasManyRelation', HasManyResource::class),
            BelongsToMany::make('belongsToManyRelation', BelongsToManyResource::class)
                ->withPivotFields(['created_at']),
            HasOneThrough::make('hasOneThroughRelation', HasOneThroughResource::class),
            HasManyThrough::make('hasManyThroughRelation', HasManyThroughResource::class)
        ];
    }

    public function exposedFields(RestRequest $request)
    {
        return [
            'id',
            'name',
            'number'
        ];
    }

    public function exposedScopes(RestRequest $request)
    {
        return [
            'numbered'
        ];
    }

    public function exposedLimits(RestRequest $request) {
        return [
            1,
            10,
            25,
            50
        ];
    }

    public function defaultOrderBy(RestRequest $request)
    {
        return [
            'id' => 'asc'
        ];
    }
}