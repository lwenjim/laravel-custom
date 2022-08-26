<?php


namespace App\Console\Library;

use Illuminate\Support\Facades\Log;
use Jim\SportsLiveStreamLibrary\Component\Database\Model;
use Reflection;

class BeanUtils
{
    public static function copyProperties($source, &$target)
    {
        foreach (self::forEach($source) as $item) {
            if (is_array($target)) {
                if (isset($target[$item['key']])) {
                    $target[$item['key']] = $item['value'];
                }
            } elseif (is_object($target)) {
                if (property_exists($target, $item['key'])) {
                    $func = "set" . ucfirst($item['key']);
                    $target->$func($item['value']);
                }
            }
        }
    }

    protected static function forEach($source)
    {
        try {
            if (is_object($source)) {
                if ($source instanceof Model) {
                    foreach ($source->toArray() as $key => $value) {
                        yield compact('key', 'value');
                    }
                } else {
                    $ref = new \ReflectionClass($source);
                    foreach ($ref->getProperties() as $property) {
                        $key    = $property->getName();
                        $modify = Reflection::getModifierNames($property->getModifiers());
                        $modify = current($modify);
                        if (in_array($modify, ['protected', 'private'])) {
                            $func  = 'get' . ucfirst($key);
                            $value = call_user_func([$source, $func]);
                        } else {
                            $value = $property->getValue();
                        }
                        yield compact('key', 'value');
                    }
                }
            } else if (is_array($source)) {
                foreach ($source as $key => $value) {
                    yield compact('key', 'value');
                }
            }
        } catch (\ReflectionException $e) {
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }
}
