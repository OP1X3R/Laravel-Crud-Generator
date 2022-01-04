<?php

namespace jlcrud\CrudGenerator\Commands\Bases;

use Illuminate\Console\Command;
use jlcrud\CrudGenerator\Commands\CommonCommands\Common;
use jlcrud\CrudGenerator\Models\Input;

abstract class CommonBase extends Command {

    use Common;

    protected function getInput()
    {
        return new Input($this->arguments(), $this->options());
    }
}
