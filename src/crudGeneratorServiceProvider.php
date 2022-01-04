<?php

namespace jlcrud\CrudGenerator;

use Illuminate\Support\ServiceProvider;

class crudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $commands =
            ['jlcrud\crud-generator\Commands\Views\createCreateViewCommand',
             'jlcrud\crud-generator\Commands\Models\createModelCommand',
             'jlcrud\crud-generator\Commands\Controllers\createControllerCommand',
             'jlcrud\crud-generator\Commands\Migrations\createMigrationCommand',
             'jlcrud\crud-generator\Commands\CommonCommands\crudCommand'];
        $this->commands($commands);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/crudcfg.php' => config_path('crudcfg.php'),
        ]);
    }
}
