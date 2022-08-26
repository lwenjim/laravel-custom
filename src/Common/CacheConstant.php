<?php


namespace App\Console\Library;


class CacheConstant
{
    /**
     * 一分钟
     */
    const ONE_MINUTE = 60;

    /**
     * 两分钟
     */
    const TOWER_MINUTE = 2 * self::ONE_MINUTE;

    /**
     * 五分钟
     */
    const FIVE_MINUTE = 5 * self::ONE_MINUTE;


    /**
     * 一小时
     */
    const ONE_HOUR = 60 * self::ONE_MINUTE;


    /**
     * 两小时
     */
    const TWO_HOUR = 2 * self::ONE_HOUR;

    /**
     * 一天
     */
    const ONE_DAY = 24 * self::ONE_HOUR;

    /**
     * 两天
     */
    const TWO_DAY = 2 * self::ONE_DAY;

    /**
     * 七天
     */
    const SEVEN_DAY = 7 * self::ONE_DAY;

    /**
     * 一个月
     */
    const ONE_MONTH = 30 * self::ONE_DAY;
}
