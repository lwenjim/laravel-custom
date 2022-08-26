<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 2019-07-19
 * Time: 12:21
 */

namespace App\Console\Library;


trait Instance
{
    protected static $instance = null;

    /**
     * @param mixed ...$parameters
     * @return mixed
     */
    public static function getInstance(...$parameters)
    {
        if (!isset(static::$instance[static::class])) {
            static::$instance[static::class] = new static(...$parameters);
        }
        return static::$instance[static::class];
    }
}
