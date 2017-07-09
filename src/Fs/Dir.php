<?php

namespace Runn\Fs;

use Runn\Fs\Exceptions\DirAlreadyExists;
use Runn\Fs\Exceptions\DirNotReadable;
use Runn\Fs\Exceptions\InvalidDir;
use Runn\Fs\Exceptions\MkDirError;

class Dir
    extends FileAbstract
{

    /**
     * @param string $path
     * @param string $prefix
     * @return $this
     * @throws \Runn\Fs\Exceptions\InvalidDir
     */
    public function setPath(string $path, string $prefix = '')
    {
        $path = $prefix . $path;
        if (file_exists($path) && !is_dir($path)) {
            throw new InvalidDir;
        }
        $this->path = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return false;
    }

    /**
     * @param int $createMode
     * @return $this
     * @throws \Runn\Fs\Exceptions\EmptyPath
     * @throws \Runn\Fs\Exceptions\DirAlreadyExists
     * @throws \Runn\Fs\Exceptions\MkDirError
     */
    public function create(int $createMode = 0755)
    {
        if ($this->exists()) {
            throw new DirAlreadyExists();
        }
        $res = @mkdir($this->getPath(), $createMode, true);
        if (false === $res) {
            throw new MkDirError;
        }
        return $this;
    }

    /**
     * @param int $createMode
     * @return $this
     */
    public function make(int $createMode = 0755)
    {
        if ($this->exists()) {
            return $this;
        }
        $this->create($createMode);
        return $this;
    }

    /**
     * @param boolean $recursive
     * @param string $prefix
     * @return FileCollection
     * @throws DirNotReadable
     */
    public function list(bool $recursive = false, string $prefix = '')
    {
        if (!$this->isReadable()) {
            throw new DirNotReadable;
        }
        if (!empty($prefix)) {
            $prefix = (string)@realpath($prefix);
        }
        $path = realpath($this->getPath());
        if (!empty($prefix) && 0 === strpos($path, $prefix)) {
            $path = substr($path, strlen($prefix));
        }

        $list = array_values(array_map(
            function ($f) use ($path) {
                return $path . DIRECTORY_SEPARATOR . $f;
            },
            array_diff(scandir($prefix . $path), ['.', '..'])
        ));

        $ret = new FileCollection();
        foreach ($list as $file) {
            $ret[] = FileAbstract::instance($file, is_dir($prefix . $file) ? Dir::class : File::class);
            if ($recursive && is_dir($prefix . $file)) {
                $ret->merge((new static($prefix . $file, $prefix))->list($recursive));
            }
        }

        return $ret;
    }

    /**
     * @param bool $clearstatcache
     * @return int
     */
    public function mtime($clearstatcache = true)
    {
        $list = $this->list(true);
        if ($list->empty()) {
            if ($clearstatcache) {
                clearstatcache(true, $this->getPath());
            }
            return @filemtime($this->getPath() . DIRECTORY_SEPARATOR . '.');
        } else {
            return $list->reduce(0, function ($acc, FileAbstract $el) use ($clearstatcache) {
                $mtime = $el->mtime($clearstatcache);
                return $mtime > $acc ? $mtime : $acc;
            });
        }
    }

}