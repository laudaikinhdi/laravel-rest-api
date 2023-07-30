<?php

namespace Lomkit\Rest\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lomkit\Rest\Contracts\QueryBuilder;
use Lomkit\Rest\Http\Requests\DestroyRequest;
use Lomkit\Rest\Http\Requests\ForceDestroyRequest;
use Lomkit\Rest\Http\Requests\RestoreRequest;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Http\Requests\SearchRequest;
use Lomkit\Rest\Http\Requests\MutateRequest;

trait PerformsRestOperations
{
    public function search(SearchRequest $request) {
        $resource = static::newResource();

        $query = app()->make(QueryBuilder::class, ['resource' => $resource, 'query' => null])
            ->search($request->all());

        return $resource::newResponse()
            ->resource($resource)
            ->responsable(
                $resource->paginate($query, $request)
            );
    }

    public function mutate(MutateRequest $request) {
        $resource = static::newResource();

        DB::beginTransaction();

        $operations = app()->make(QueryBuilder::class, ['resource' => $resource, 'query' => null])
            ->tap(function ($query) use ($request) {
                self::newResource()->mutateQuery($request, $query->toBase());
            })
            ->mutate($request->all());

        DB::commit();

        return $operations;
    }

    public function destroy(DestroyRequest $request) {
        $resource = static::newResource();

        $query = $resource->destroyQuery($request, $resource::newModel()::query());

        $models = $query
            ->whereIn($resource::newModel()->getKeyName(), $request->input('resources'))
            ->get();

        foreach ($models as $model) {
            $this->authorizeTo('delete', $model);

            $resource->performDelete($request, $model);
        }

        //@TODO: il faut prévoir de pouvoir load des relations ici ?
        return $resource::newResponse()
            ->resource($resource)
            ->responsable($models);
    }

    public function restore(RestoreRequest $request) {
        $resource = static::newResource();

        $query = $resource->restoreQuery($request, $resource::newModel()::query());

        $models = $query
            ->withTrashed()
            ->whereIn($resource::newModel()->getKeyName(), $request->input('resources'))
            ->get();

        foreach ($models as $model) {
            $this->authorizeTo('restore', $model);

            $resource->performRestore($request, $model);
        }

        return $resource::newResponse()
            ->resource($resource)
            ->responsable($models);
    }

    public function forceDelete(ForceDestroyRequest $request) {
        $resource = static::newResource();

        $query = $resource->forceDeleteQuery($request, $resource::newModel()::query());

        $models = $query
            ->withTrashed()
            ->whereIn($resource::newModel()->getKeyName(), $request->input('resources'))
            ->get();

        foreach ($models as $model) {
            $this->authorizeTo('forceDelete', $model);

            $resource->performForceDelete($request, $model);
        }

        return $resource::newResponse()
            ->resource($resource)
            ->responsable($models);
    }
}