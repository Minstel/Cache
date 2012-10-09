<?php

/**
 * This file is part of the D2Cache proyect.
 * 
 * Description of File
 *
 * @author : Daniel González <daniel.gonzalez@freelancemadrid.es> 
 * @file : File.php , UTF-8
 * @date : Sep 4, 2012 , 1:00:09 AM
 */

namespace Desarrolla2\Cache\Adapter;

use Desarrolla2\Cache\Adapter\AdapterInterface;
use Desarrolla2\Cache\Exception\FileCacheException;

class File implements AdapterInterface
{

    /**
     * @var int
     */
    protected $ttl = 3600;

    /**
     * @var string
     */
    protected $cacheDir = '/tmp';

    /**
     * {@inheritdoc } 
     */
    public function delete($key)
    {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * {@inheritdoc } 
     */
    public function get($key)
    {
        if ($this->has($key)) {
            $cacheFile = $this->getCacheFile($key);
            if (!$data = unserialize(file_get_contents($cacheFile))) {
                throw new FileCacheException('Error reading data with the key ' . $key . ' from the cache file.');
            }
            return $data;
        }
        return null;
    }

    /**
     * {@inheritdoc } 
     */
    public function has($key)
    {
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            $time = filemtime($cacheFile);
            if ($time) {
                if ($time + $this->ttl >= time()) {
                    return true;
                }
            }
        }
        $this->delete($key);
        return false;
    }

    /**
     * {@inheritdoc } 
     */
    public function set($key, $value, $ttl = null)
    {
        $cacheFile = $this->getCacheFile($key);
        if (!file_put_contents($cacheFile, serialize($value))) {
            throw new FileCacheException('Error saving data with the key ' . $key . ' to the cache file.');
        }
    }

    /**
     * {@inheritdoc } 
     */
    public function setOption($key, $value)
    {
        switch ($key) {
            case 'ttl':
                $value = (int) $value;
                if ($value < 1) {
                    throw new FileCacheException('ttl cant be lower than 1');
                }
                $this->ttl = $value;
                break;
            case 'cacheDir':
                $this->cacheDir = (string) $value;
                break;
            default :
                throw new FileCacheException('option not valid ' . $key);
        }
    }

    /**
     * Get the specified cache file
     */
    protected function getCacheFile($key)
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.php.cache';
    }

}
