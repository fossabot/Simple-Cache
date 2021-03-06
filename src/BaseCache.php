<?php
/**
 * Created by PhpStorm.
 * User: jaredchu
 * Date: 03/08/2017
 * Time: 17:17
 */

namespace JC;

use ReflectionClass;

/**
 * Class SimpleCache
 * @package JC
 */
class BaseCache
{
    /**
     * @param $key
     * @param $data
     * @return bool
     *
     * Add object to cache list and save object as json file
     */
    public static function add($key, $data, $ttl = 0)
    {
        $tempFilePath = static::getTempFile($key) ?: static::createTempFile($key, $ttl);
        return (bool)file_put_contents($tempFilePath, static::encode($data));
    }

    /**
     * @param $key
     * @param string $className
     * @return object|bool
     *
     * Fetch object from cache
     */
    public static function fetch($key, $className)
    {
        if (Manager::has($key)) {
            $dataString = file_get_contents(static::getTempFile($key));

            $mapper = new JCMapper();
            return $mapper->map(static::decode($dataString), (new ReflectionClass($className))->newInstanceWithoutConstructor());
        }

        return false;
    }

    /**
     * @param $key
     * @return bool
     *
     * Remove object from cache
     */
    public static function remove($key)
    {
        if (Manager::has($key)) {
            unlink(Manager::get($key));
            return Manager::remove($key);
        }

        return false;
    }

    /**
     * @param $key
     * @return bool
     *
     * Check object is cached or not
     */
    public static function exists($key)
    {
        return file_exists(static::getTempFile($key));
    }

    /**
     * @param $key
     * @return bool|string
     */
    protected static function getTempFile($key)
    {
        if (Manager::has($key)) {
            return Manager::get($key);
        }

        return false;
    }

    /**
     * @param $key
     * @return bool|string
     */
    protected static function createTempFile($key, $ttl)
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), $key);
        Manager::set($key, $tempFilePath, $ttl);

        return $tempFilePath;
    }

    /**
     * @param object $object
     * @return string
     */
    protected static function encode($object)
    {
        return json_encode($object);
    }

    /**
     * @param string $string
     * @return object
     */
    protected static function decode($string)
    {
        return json_decode($string);
    }
}