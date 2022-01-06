<?php

namespace jlcrud\CrudGenerator\Commands\Models;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use jlcrud\CrudGenerator\Commands\CommonCommands\Common;

class createModelCommand extends GeneratorCommand
{

    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:model
                            {model-name : Model name.}
                            {--table-name= : Database table name.}
                            {--fillable= : Fillable collumn names.}
                            {--relationship= : Relationship to other models, needs - relationsihp, class, foreign key, owner key, example  - belongsTo,User,userId,id:hasOne,Product,id,productId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new model.';

    protected $relationshipText = '';

    /**
     * Execute the console command.
     *
     * @return int
     */

    protected function getStub()
    {
        return __DIR__ . '/Stubs/model.stub';
    }


    public function handle()
    {
        $inputData = $this->getInput();

        $destination = $this->getPath($inputData->modelName);


        if($this->alreadyExists($destination)) return false;

        $relationships = explode(':', $inputData->relationship);

        foreach($relationships as $relationship)
        {
            $relationshipArray = explode(',', $relationship);
            if(count($relationshipArray) > 3) $this->makeRelationshipText($relationshipArray, $inputData->modelName);
        }

        $stub = $this->getStub();
        $stub = $this->getFileContent($stub);

        return $this->replaceNamespace($stub, $inputData->modelName)
            ->replaceTableName($stub, $inputData->tableName)
            ->replaceFillable($stub, $inputData->fillable)
            ->replaceClassName($stub, $inputData->modelName)
            ->replaceRelationships($stub, $this->relationshipText)
            ->createFile($destination, $stub)
            ->info("A model was crafted successfully.");
    }

    protected function getInput()
    {
        $modelName = trim($this->argument('model-name'));
        $tableName = trim($this->option('table-name'));
        $fillable = trim($this->option('fillable'));
        $relationship = trim($this->option('relationship'));

        return (object) compact(
            'modelName',
            'tableName',
            'fillable',
            'relationship'
        );
    }

    protected function getPath($name)
    {
        $path = app_path();
        $path = $path . $name . ".php";
        return $path;
    }

    protected function replaceTableName(&$stub, $table)
    {
        $stub = str_replace(
            '{{tableName}}', $table, $stub
        );

        return $this;
    }

    protected function replaceRelationships(&$stub, $relationship)
    {
        $stub = str_replace(
            '{{relationships}}', $relationship, $stub
        );

        return $this;
    }

    protected function replaceClassName(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub =  str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        return $this;
    }

    protected function replaceFillable(&$stub, $fillable)
    {
        $stub = str_replace(
            '{{fillable}}', $fillable, $stub
        );

        return $this;
    }

    protected function makeRelationshipText($relationship,$name)
    {
        $this->relationshipText .= "\npublic function " . $name . "_" . $relationship[0] . "()" .
            "{
            return \$this->". $relationship[0] . "('App\\" . $relationship[1] ."', '" . $relationship[2] . "', '" . $relationship[3] . "');
         }\n";
    }
}
