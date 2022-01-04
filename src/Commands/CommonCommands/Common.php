<?php

namespace jlcrud\CrudGenerator\Commands\CommonCommands;

use File;

trait Common
{

    protected function FileExists($file)
    {
        return File::exists($file);
    }

    protected function createDirectory($path)
    {

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return $this;
    }

    protected function putTextInFile($file, $content)
    {
        $path = dirname($file);

        File::put($file, $content);

        return $this;
    }

    public function createFile($dir, $content)
    {
        $path = dirname($dir);
        $this->createDirectory($path);
        $this->putTextInFile($dir, $content);

        return $this;
    }

    protected function getFileContent($file)
    {
        return File::get($file);
    }

}
