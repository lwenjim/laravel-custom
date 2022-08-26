<?php


namespace Jim\SportsLiveStreamLibrary\Component\Database;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Jim\SportsLiveStreamLibrary\Common\Tools;

class QueryBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres(Tools::convertArrayUnCamelizeKey($column), $boolean);
        }
        return parent::where($column, $operator, $value, $boolean);
    }

    public function update(array $values)
    {
        return parent::update(Tools::convertArrayUnCamelizeKey($values));
    }

    public function fetch($columns = ['*'])
    {
        return $this->first($columns);
    }

    public function getArray()
    {
        return array_map(function ($item) {
            return Tools::convertUncamelize2camelize((array)$item);
        }, parent::get(['*'])->toArray());
    }

    protected function onceWithColumns($columns, $callback)
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }
        $result = $callback;
        if (is_callable($callback)){
            $result = $callback();
        }

        $this->columns = $original;

        return $result;
    }

    public function makeModel($data)
    {
        return collect($this->onceWithColumns(Arr::wrap(['*']), $data));
    }
}
