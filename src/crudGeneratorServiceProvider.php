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
            ['jlcrud\CrudGenerator\Commands\Views\createCreateViewCommand',
             'jlcrud\CrudGenerator\Commands\Models\createModelCommand',
             'jlcrud\CrudGenerator\Commands\Controllers\createControllerCommand',
             'jlcrud\CrudGenerator\Commands\Migrations\createMigrationCommand',
             'jlcrud\CrudGenerator\Commands\CommonCommands\crudCommand'];
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
