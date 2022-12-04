<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 10.12.2018
 */

namespace Pavher\Sdao\Tests\_files;


use Nette\Caching\IStorage;

class TestStorage implements IStorage
{
    private $data = [];


    public function read(string $key)
    {
        return $this->data[$key] ?? null;
    }


    public function write(string $key, $data, array $dependencies): void
    {
        $this->data[$key] = [
            'data' => $data,
            'dependencies' => $dependencies,
        ];
    }


    public function lock(string $key): void
    {
    }


    public function remove(string $key): void
    {
    }


    public function clean(array $conditions): void
    {
    }
}