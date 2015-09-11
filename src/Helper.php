<?php

namespace Valkyrie\Packageek;

use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem as Filesystem;

class Helper
{

    public function __construct()
    {
        $this->file = new Filesystem;
    }

    public function fileExists($argument1)
    {
        if($this->file->exists($argument1))
        {
           return true;
        }
        return false;
    }

    public function makeDir($path, $mode = 0755, $recursive = false, $force = false)
    {
        return $this->file->makeDirectory($path, $mode, $recursive, $force);
    }

    public function replaceAndSave($oldFile, $search, $replace, $newFile = null)
    {
        $newFile = ($newFile == null) ? $oldFile : $newFile ;
        $file = $this->file->get($oldFile);
        $replacing = str_replace($search, $replace, $file);
        $this->file->put($newFile, $replacing);
    }


}
