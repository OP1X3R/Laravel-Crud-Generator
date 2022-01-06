<?php

namespace jlcrud\CrudGenerator\Commands\Migrations;

use Illuminate\Console\GeneratorCommand;
use jlcrud\CrudGenerator\Commands\CommonCommands\Common;

class createMigrationCommand extends GeneratorCommand
{
    use Common;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:migration
                            {name : Migration name.}
                            {--schema= : Migration Schema.}
                            {--primary-key=id : The name of the primary key.}
                            {--foreign= : Foreign keys seperated by ",". example - foreign(\'game_id\')->references(\'id\')->on(\'low_games\')->onDelete(\'cascade\')}
                            {--create-users= : Used by the system to create users in the database.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new migration';

    protected $foreignKeysText = '';

    protected function getStub()
    {
        return __DIR__ . '/Stubs/migration.stub';
    }

    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        $datePrefix = date('Y_m_d_His');

        return database_path('/migrations/') . $datePrefix . '_create_' . $name . '_table.php';
    }

    protected function replaceSchemaUp(&$stub, $schemaUp)
    {
        $stub = str_replace(
            '{{schema_up}}', $schemaUp, $stub
        );

        return $this;
    }

    protected function replaceSchemaDrop(&$stub, $schemaDrop)
    {
        $stub = str_replace(
            '{{schema_drop}}', $schemaDrop, $stub
        );

        return $this;
    }

    public function handle()
    {
        $inputData = $this->getInput();

        $stub = $this->getStub();
        $stub = $this->getFileContent($stub);

        $destination = $this->getPath($inputData->name);

        $className = 'Create' . ucwords($inputData->name) . 'Table';
        $fields = explode(',', $inputData->schema);
        $foreignKeys = explode(',', $inputData->foreign);


        if ($inputData->schema) {
            $x = 0;
            foreach ($fields as $field) {
                $fieldArray = explode(':', $field);
                $data[$x]['name'] = trim($fieldArray[0]);
                $data[$x]['type'] = trim($fieldArray[1]);
                $x++;
            }
        }

        $schemaFields = '';

        foreach ($data as $item) {
            switch ($item['type']) {
                case 'char':
                    $schemaFields .= "\$table->char('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'date':
                    $schemaFields .= "\$table->date('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'foreign':
                    $model=explode("_", $item['name']);
                    $schemaFields .= "\$table->foreign('" . $item['name'] . "')->references('id')->on('" . $model[0]. "s')->onDelete('cascade');\n";
                    $schemaFields .= "\$table->integer('" . $item['name'] . "')->unsigned();\n";
                    break;

                case 'file':
                    $schemaFields .= "\$table->dateTime('" . $item['name'] . "', 512)->nullable();\n";
                    break;

                case 'datetime':
                    $schemaFields .= "\$table->dateTime('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'time':
                    $schemaFields .= "\$table->time('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'timestamp':
                    $schemaFields .= "\$table->timestamp('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'text':
                    $schemaFields .= "\$table->text('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'mediumtext':
                    $schemaFields .= "\$table->mediumText('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'longtext':
                    $schemaFields .= "\$table->longText('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'json':
                    $schemaFields .= "\$table->json('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'jsonb':
                    $schemaFields .= "\$table->jsonb('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'binary':
                    $schemaFields .= "\$table->binary('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'number':
                case 'integer':
                    $schemaFields .= "\$table->integer('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'bigint':
                    $schemaFields .= "\$table->bigInteger('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'mediumint':
                    $schemaFields .= "\$table->mediumInteger('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'tinyint':
                    $schemaFields .= "\$table->tinyInteger('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'smallint':
                    $schemaFields .= "\$table->smallInteger('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'boolean':
                    $schemaFields .= "\$table->boolean('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'decimal':
                    $schemaFields .= "\$table->decimal('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'double':
                    $schemaFields .= "\$table->double('" . $item['name'] . "')->nullable();\n";
                    break;

                case 'float':
                    $schemaFields .= "\$table->float('" . $item['name'] . "')->nullable();\n";
                    break;

                default:
                    $schemaFields .= "\$table->string('" . $item['name'] . "')->nullable();\n";
                    break;
            }
        }

        if($inputData->foreign) {
            foreach ($foreignKeys as $foreignKey) {
                $this->foreignKeysText .= "\t\$table->" . $foreignKey . ";\n";
            }
        }

        $schemaUp = "
            Schema::create('" . $inputData->name . "', function(Blueprint \$table) {
                \$table->increments('" . $inputData->primaryKey . "');
                " . $schemaFields . "
                " . $this->foreignKeysText . "
                \$table->timestamps();
                \$table->softDeletes();
            });
            ";
        if($inputData->createUsers == "yes") {
            $dbInsert = "
            DB::table('" . $inputData->name . "')->insert(
            array(
                'name' => 'crudAdmin',
                'password' => '" . bcrypt('dwawdmwa8nmWm432nm') . "',
                'isAdmin' => '1'
            ));
            DB::table('" . $inputData->name . "')->insert(
            array(
                'name' => 'crudUser',
                'password' => '" . bcrypt('Kwa9dm4932n43242mm') . "'
            ));
        ";
        }
        else
        {
            $dbInsert = '';
        }

        $schemaDrop = "Schema::dropIfExists('" . $inputData->name . "');";

        return $this->replaceSchemaUp($stub, $schemaUp)
            ->replaceSchemaDrop($stub, $schemaDrop)
            ->replaceClassName($stub, $className)
            ->replacedbInsert($stub, $dbInsert)
            ->createFile($destination, $stub)
            ->info("A migration was crafted successfully.");

    }

    protected function replaceClassName(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub =  str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);

        return $this;
    }

    protected function replacedbInsert(&$stub, $schemaUp)
    {
        $stub = str_replace(
            '{{dbInsert}}', $schemaUp, $stub
        );

        return $this;
    }

    protected function getInput()
    {
        $name = trim($this->argument('name'));
        $schema = trim($this->option('schema'));
        $primaryKey = trim($this->option('primary-key'));
        $createUsers = trim($this->option('create-users'));
        $foreign = trim($this->option('foreign'));

        return (object) compact(
            'name',
            'schema',
            'primaryKey',
            'createUsers',
            'foreign'
        );
    }
}
