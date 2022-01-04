<?php

namespace jlcrud\CrudGenerator\Commands\Controllers;

use Illuminate\Console\GeneratorCommand;
use jlcrud\CrudGenerator\Commands\CommonCommands\Common;
use Illuminate\Support\Str;

class createControllerCommand extends GeneratorCommand
{
    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:controller
                            {name : Controller name.}
                            {--crud-name= : Crud name.}
                            {--model-name= : Model name.}
                            {--required-fields= : Fields that need to be validated. example value - \'name\' => \'required|min:10\', \'productId\' => \'required\',}
                            {--prefix= : Prefix for the route group.}
                            {--view-path= : The name of your view path.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new resource controller';

    protected function getStub()
    {
        return __DIR__ . '\Stubs\controller.stub';
    }

    protected function replaceCrudName(&$stub, $crudName)
    {
        $stub = str_replace(
            '{{crudName}}', $crudName, $stub
        );

        return $this;
    }

    protected function replaceCrudNameLowercase(&$stub, $crudName)
    {
        $stub = str_replace(
            '{{crudNameLowercase}}', strtolower($crudName), $stub
        );

        return $this;
    }

    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace(
            '{{modelName}}', $modelName, $stub
        );

        return $this;
    }

    protected function replaceNameSpacePrefix(&$stub, $prefix = '')
    {
        if($prefix != '') $prefix = "\\". $prefix;
        $stub = str_replace(
            '{{namespaceprefix}}', $prefix, $stub
        );

        return $this;
    }


    protected function replaceValidationRules(&$stub, $validationRules)
    {
        $stub = str_replace(
            '{{validationRules}}', $validationRules, $stub
        );

        return $this;
    }

    protected function replaceViewPath(&$stub, $viewPath)
    {
        if($viewPath != '') $viewPath = "." . $viewPath;
        $stub = str_replace(
            '{{viewPath}}', $viewPath, $stub
        );

        return $this;
    }

    protected function getPath($name, $prefix = '')
    {
        $path = app_path();
        if($prefix != '') $path = $path . "\Http\Controllers\\" . $prefix . '\\' . $name . "Controller.php";
        else $path = $path . "\Http\Controllers\\" . $name . "Controller.php";
        return $path;
    }

    public function handle()
    {
        $inputData = $this->getInput();

        $stub = $this->getStub();
        $stub = $this->getFileContent($stub);

        $destination = $this->getPath($inputData->modelName, $inputData->prefix);

        $validationRules = '';
        if ($inputData->reqFields != '') {
            $validationRules = "\$this->validate(\$request, [" . $inputData->reqFields . "]);\n";
        }

        return $this->replaceNameSpacePrefix($stub, $inputData->namespacePrefix)
            ->replaceCrudName($stub, $inputData->crudName)
            ->replaceCrudNameLowercase($stub, $inputData->crudName)
            ->replaceModelName($stub, $inputData->modelName)
            ->replaceViewPath($stub, $inputData->viewPath)
            ->replaceValidationRules($stub, $validationRules)
            ->replaceClassName($stub, $inputData->name)
            ->createFile($destination, $stub)
            ->info("A controller was crafted successfully.");
    }
    protected function replaceClassName(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub =  str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        return $this;
    }

    protected function getInput()
    {
        $name = trim($this->argument('name'));
        $modelName = trim($this->option('model-name'));
        $reqFields = trim($this->option('required-fields'));
        $viewPath = trim($this->option('view-path'));
        $namespacePrefix = trim($this->option('prefix'));
        $prefix = trim($this->option('prefix') ? $this->option('prefix').'\\' : '');
        $crudName = trim($this->option('crud-name'));

        return (object) compact(
            'name',
            'modelName',
            'reqFields',
            'prefix',
            'viewPath',
            'crudName',
            'namespacePrefix'
        );
    }
}
