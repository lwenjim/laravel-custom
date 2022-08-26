<?php


namespace App\Console\Library;

use Illuminate\Support\Facades\Log;
use Jim\SportsLiveStreamLibrary\Common\Tools;
use ReflectionClass;

trait ObjectArray
{
    public function toArray()
    {
        return self::objectToArray($this);
    }

    public static function arrayToObject($arr, $class, $normal = true)
    {
        $obj = new $class();
        if ($normal) {
            $arr = self::arrKeyToCamelize($arr);
        }
        $ref = new ReflectionClass($obj);
        foreach ($ref->getProperties() as $property) {
            $name  = $property->getName();
            $func  = 'set' . ucfirst($name);
            $value = $arr[$name] ?? '';
            if (method_exists($obj, $func)) {
                call_user_func([$obj, $func], $value);
            }
        }
        return $obj;
    }

    public static function arrKeyToCamelize($arr)
    {
        $temp = [];
        foreach ($arr as $key => $item) {
            $temp[Tools::camelize($key)] = $item;
        }
        return $temp;
    }

    public static function objectToArray($obj)
    {
        try {
            $ref = new ReflectionClass($obj);
            $arr = array_map(function ($property) use ($obj) {
                try {
                    $name = $property->getName();
                    if ($name == 'instance') {
                        return [];
                    }
                    $func  = 'get' . ucfirst($property->getName());
                    $value = call_user_func([$obj, $func]);
                    if (is_object($value)) {
                        $value = self::objectToArray($value);
                    }
                    if (is_array($value)) {
                        foreach ($value as $key => $item) {
                            if (is_object($item)) {
                                $value[$key] = self::objectToArray($item);
                            }
                        }
                    }
                    return compact('name', 'value');
                } catch (\Throwable $exception) {
                    Log::error($exception->getTraceAsString());
                }
            }, $ref->getProperties());
            return array_column($arr, 'value', 'name');
        } catch (\ReflectionException $e) {
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
