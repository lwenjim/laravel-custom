<?php


namespace App\Console\Library\Vo;


use App\Console\Library\BeanUtils;
use App\Console\Library\Instance;
use App\Console\Library\ObjectArray;
use Jim\SportsLiveStreamLibrary\Common\Tools;

class VoModel
{
    use Instance;
    use ObjectArray;

    public static function make($arr, $class)
    {
        if (!is_a($class, self::class, true)) {
            throw new \Exception('factoryModel error!');
        }
        if (is_string($arr)) {
            $arr = json_decode($arr, true);
        }
        if (is_array($arr)) {
            $arr = Tools::convertUncamelize2camelize($arr);
        }
        $obj = new $class;
        BeanUtils::copyProperties($arr, $obj);
        return $obj;
    }

    /**
     * @param  $arr
     * @return static
     * @throws \Exception
     */
    public static function makeSelf($arr)
    {
        return self::make($arr, static::class);
    }

    public static function builder()
    {
        return new static();
    }
}
