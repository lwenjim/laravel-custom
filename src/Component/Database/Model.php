<?php

namespace Jim\SportsLiveStreamLibrary\Component\Database;

use App\Console\Library\CacheConstant;
use App\Console\Library\ObjectArray;
use App\Console\Library\Vo\VoModel;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BasicModel;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Str;
use Jim\SportsLiveStreamLibrary\Common\Tools;

/**
 * 数据库模型基类
 * @method static static|mixed find(...$param)
 * @method static static findOrFail(...$param)
 * @method static static firstOrCreate(...$param)
 * @method static static updateOrCreate(...$param)
 * @method static static|QueryBuilder where(...$param)
 * @method static static create(...$param)
 * @method static static firstOrFail(...$param)
 * @method static static first(...$param)
 * @method static static makeModel(...$param)
 */
class Model extends BasicModel
{
    use HasFactory;

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $builder = null;

    public static function updateOrCreateFixed(array $attributes, array $values = [])
    {
        return parent::__callStatic('updateOrCreate', [$attributes, $values]);
    }

    public function getTable()
    {
        return $this->table ?? rtrim(Str::snake(Str::pluralStudly(class_basename($this))), 's');
    }

    public static function createFixed(array $attributes = [])
    {
        $create_time = date('Y-m-d H:i:s');
        $update_time = date('Y-m-d H:i:s');
        return parent::__callStatic('create', [$attributes + compact('create_time', 'update_time',)]);
    }

    public function __get($key)
    {
        return $this->getAttribute(Tools::isContainUpperChar($key) ? Tools::uncamelize($key) : $key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute(Tools::isContainUpperChar($key) ? Tools::uncamelize($key) : $key, $value);
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute(Tools::isContainUpperChar($offset) ? Tools::uncamelize($offset) : $offset);
    }

    protected function newBaseQueryBuilder()
    {
        $_this = $this->getConnection();
        return (function () use ($_this) {
            return new QueryBuilder(
                $_this, $_this->getQueryGrammar(), $_this->getPostProcessor()
            );
        })->call($_this);
    }

    public function __call($method, $parameters)
    {
        try {
            return parent::__call($method, $parameters);
        } catch (\Throwable $exception) {
            $this->getAttributes();
            if ($exception instanceof BadMethodCallException) {
                $method = Tools::uncamelize(trim($method));
                $field  = preg_replace("#(get_|set_)#", '', $method);
                if (!isset($this->getAttributes()[$field])) {
                    throw $exception;
                }
                $func = '__' . substr($method, 0, 3);
                return $this->{$func}($field, ...$parameters);
            }
        }
    }

    /**
     * 缓存Base实例和实例数组到Redis
     * @param string $key
     * @param callable $func
     * @param RedisCache $redis
     * @param $class
     * @param null $expires
     * @param bool $more
     * @return array|mixed|Base|null
     */
    public static function findModelAndJson2Redis(string $key, callable $func, RedisManager $redis, $class, $expires = null, bool $more = false)
    {
        if (!$redis->exists($key)) {
            $data = $result = $func();
            if (empty($result) || !$more && is_a($result, Model::class) && $result->empty()) {
                return null;
            }
            if ($more && empty($result)) {
                return null;
            }
            if ($more) {
                $result = array_map(function ($datum) {
                    if (is_object($datum) && method_exists($datum, 'toArray')) {
                        return $datum->toArray();
                    }
                    return self::sureArray($datum);
                }, $result);
            } else {
                if (is_object($result) && method_exists($result, 'toArray')) {
                    $result = $result->toArray();
                } else {
                    $result = self::sureArray($result);
                }
            }
            $redis->set($key, json_encode($result, JSON_UNESCAPED_UNICODE), $expires > 0 ? $expires : CacheConstant::ONE_DAY);
        } else {
            $data = $redis->get($key);
            $data = json_decode($data, true);
            if (!empty($class)) {
                $isModel = false;
                if ($isModel = is_a($class, $base = Model::class, true) || is_a($class, $base = VoModel::class, true)) {
                    $func = 'make';
                    if ($isModel) {
                        $func = 'makeModel';
                    }
                    if ($more) {
                        $arr = [];
                        foreach ($data as $datum) {
                            $arr[] = $base::{$func}($datum, $class);
                        }
                        $data = $arr;
                    } else {
                        $data = $base::{$func}($data, $class);
                    }
                }
            }
        }
        return $data;
    }

    public static function sureArray($responseData)
    {
        if (is_object($responseData)) {
            if (is_a($responseData, \stdClass::class)) {
                return $responseData;
            }
            if ($responseData instanceof Model) {
                return $responseData->toArray();
            }
            return ObjectArray::objectToArray($responseData);
        }
        if (is_array($responseData)) {
            return array_map(function ($item) {
                return self::sureArray($item);
            }, $responseData);
        }
        return $responseData;
    }

    public static function make($arr, $class)
    {
        if (!is_a($class, self::class, true)) {
            throw new \Exception('factoryModel error!');
        }
        $obj = new $class;
        $obj->_setData($arr);
        return $obj;
    }

    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }
}
