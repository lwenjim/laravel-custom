<?php


namespace Jim\SportsLiveStreamLibrary\Component\Database;

use Illuminate\Database\Eloquent\Builder;

class EloquentBuilder extends Builder
{
    protected $passthru = [
        'aggregate',
        'average',
        'avg',
        'count',
        'dd',
        'doesntExist',
        'dump',
        'exists',
        'explain',
        'getBindings',
        'getConnection',
        'getGrammar',
        'insert',
        'insertGetId',
        'insertOrIgnore',
        'insertUsing',
        'max',
        'min',
        'raw',
        'sum',
        'toSql',
        'getArray',
    ];

    public function makeModel($data)
    {
        $builder = $this->applyScopes();
        if (count($models = $this->model->hydrate([$data])->all()) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }
        return $builder->getModel()->newCollection($models)->first();
    }
}
