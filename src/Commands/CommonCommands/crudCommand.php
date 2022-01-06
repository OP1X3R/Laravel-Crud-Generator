<?php

namespace jlcrud\CrudGenerator\Commands\CommonCommands;

use Illuminate\Support\Str;
use File;
use Illuminate\Console\Command;

class crudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:gen
                            {name : Name for the crud.}
                            {--fields= : Fields name for form and model.}
                            {--required-fields= : Fields that need to be validated. example value - \'name\' => \'required|min:10\', \'productId\' => \'required\',}
                            {--route=yes : Should we include Crud route to route.php? [yes|no].}
                            {--primary-key=id : Fields name for form and model.}
                            {--foreign= : Foreign keys seperated by ",". example - foreign(\'game_id\')->references(\'id\')->on(\'low_games\')->onDelete(\'cascade\')}
                            {--view-path= : Name for the view-path.}
                            {--route-group= : Route group prefix.}
                            {--relationship= : Relationship to other models, needs - relationsihp, class, foreign key, owner key, example  - belongsTo,User,userId,id:hasOne,Product,id,productId}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a crud - Controller, Model, Views and Migrations';

    protected $routeName = '';

    protected $controller = '';

    protected $allCrudInjectHTML = '';

    protected $allCrudsHTML = '';

    protected function getControllerPath($name, $controllerNamespace)
    {
        $path = ($controllerNamespace != '') ? $controllerNamespace . '\\' . $name . 'Controller' : $name . 'Controller';
        return $path;
    }


    public function handle()
    {
        $inputData = $this->getInput();
        /* Laravel naming*/
        $modelName = $inputData->name;
        $migrationName = str_plural(strtoupper($inputData->name));
        $tableName = $migrationName;
        $viewName = strtolower($inputData->name);
        /* Laravel naming*/

        $this->routeName = ($inputData->viewPath) ?  $inputData->viewPath . '/' . $viewName : $viewName;

        $controllerNamespace = ($inputData->namespace) ? ($inputData->namespace) . '\\' : '';

        $allFields = explode(',', $inputData->fields);

        foreach($allFields as $field)
        {
            $fillableArray[] = preg_replace("/(.*?):(.*)/", "$1", trim($field));
        }
        $viewsDestination = base_path('resources/views/crud/');

        $crudModelsPath = $viewsDestination . "models.json"; //a file with with all cruds in it. [JSON]

        $allModels = [];

        if(File::exists($crudModelsPath)) {
            $allModels = json_decode(File::get($crudModelsPath));
            File::delete($crudModelsPath);
        }
        array_push($allModels, $modelName);
        $this->createFile($crudModelsPath, json_encode($allModels));
        $routeFile =  base_path('routes/web.php');
        $loginControllerPath = $this->getLoginControllerPath("Login");

        $loginCreated = 0;
        if(!File::exists($loginControllerPath)) { //Creating login.
            $loginCreated = 1;
            if($inputData->prefix != '') $loginController = $inputData->prefix . "\LoginController";
            else $loginController = "LoginController";
            $loginStub = $this->getFileContent($this->getControllerStub("LoginController.stub"));
            $this->replaceNameSpacePrefix($loginStub, $inputData->prefix);
            $this->createFile($loginControllerPath, $loginStub);

            $loginViewPath = $viewsDestination . "/login.blade.php";
            $loginViewStub = $this->getFileContent($this->getStub("login"));
            $this->createFile($loginViewPath, $loginViewStub);

            $modelPath = $this->getModelPath("User");
            $modelStub = $this->getFileContent($this->getStub("user"));
            $this->createFile($modelPath, $modelStub);

            $registerViewPath = $viewsDestination . "/register.blade.php";
            $registerViewStub = $this->getFileContent($this->getStub("register"));
            $this->createFile($registerViewPath, $registerViewStub);
            $this->call('create:migration', ['name' => 'jlCrudUsers', '--schema' => "name:string, password:text, remember_token:text, isAdmin:boolean", '--create-users' => 'yes']);
            File::append($routeFile, "\nRoute::get('/logout', '" . $loginController . "@logout');");
            File::append($routeFile, "\nRoute::get('/allcruds', '" . $loginController . "@cruds');");
            File::append($routeFile, "\nRoute::post('/login', '" . $loginController . "@login');");
            File::append($routeFile, "\nRoute::post('/register', '" . $loginController . "@register');");
            File::append($routeFile, "\nRoute::get('/login', ['as' => 'login', 'uses' => '" . $loginController . "@index']);");
            File::append($routeFile, "\nRoute::get('/register', ['as' => 'register', 'uses' => '" . $loginController . "@register_index']);");
        }

        $allcrudsViewPath = $viewsDestination . "allcruds.blade.php";
        $allcrudsViewStub = $this->getFileContent($this->getStub("allcruds"));
        $this->createFile($allcrudsViewPath, $allcrudsViewStub);
        $this->changeAllcrudsVariables($allcrudsViewPath, $allModels, $inputData->viewPath);

        $seperatedString = implode("', '", $fillableArray);
        $fillable = "['" . $seperatedString . "']";

        $this->call('create:controller', ['name' => $controllerNamespace . $inputData->name . 'Controller', '--crud-name' => $inputData->name , '--model-name' => $modelName, '--required-fields' => $inputData->reqFields, '--prefix' => $inputData->prefix,'--view-path' => $inputData->viewPath]);
        $this->call('create:model', ['model-name' => $modelName, '--table-name' => $tableName, '--fillable' => $fillable, '--relationship' => $inputData->relationship]);
        $this->call('create:migration', ['name' => $migrationName, '--schema' => $inputData->fields, '--primary-key' => $inputData->pk, '--foreign' => $inputData->foreign]);
        $this->call('create:create-view', ['name' => $viewName, '--crud-name' => $inputData->name, '--fields' => $inputData->fields, '--view-path' => $inputData->viewPath]);


        if (file_exists($routeFile) && (strtolower($this->option('route')) === 'yes')) {
            $this->controller = $this->getControllerPath($inputData->name, $controllerNamespace);

            $isAdded = File::append($routeFile, "\n".implode("\n\t", $this->addRoutes()));

            if ($isAdded) {
                $this->info('Crud resource routes added to ' . $routeFile);
            } else {
                $this->info('Unable to add the route to ' . $routeFile);
            }
        }
        $this->info("You can view your newly generated crud at: http://localhost/allcruds");
        $this->info("Don't forget to run php artisan migrate first!");
        if($loginCreated)
        {
            $this->info('Login pages were created successfully.');
            $this->info('Admin -  Login: crudAdmin Password: dwawdmwa8nmWm432nm');
            $this->info('User -  Login: crudUser Password: Kwa9dm4932n43242mm');
        }
    }

    public function changeAllcrudsVariables($viewPath, $allModels, $prefix)
    {
        $x = 1;
        foreach($allModels as $model)
        {
            $this->allCrudInjectHTML .= "@inject('" . $model . "', 'App\\" . $model ."')\n";
            $this->allCrudsHTML .= "<tr><th scope=\"row\">" . $x . "</th>
                        <td>" . $model . "</td>
						<td>{{ $" . $model . "->count() }}</td>
                        <td><a href=\"" . $prefix . "\\" . strtolower($model) . "\"><i class=\"fa fa-chevron-right\" style=\"font-size:20px\"></i></a></td></tr>";
            $x++;
        }

        File::put($viewPath, str_replace('{{injects}}', $this->allCrudInjectHTML, File::get($viewPath)));
        File::put($viewPath, str_replace('{{crudshtml}}', $this->allCrudsHTML, File::get($viewPath)));
    }

    protected function addRoutes() {
        return ["Route::resource('" . $this->routeName . "', '" . $this->controller . "');"];
    }

    protected function getInput()
    {
        $name = trim($this->argument('name'));
        $fields = trim($this->option('fields'));
        $route = trim($this->option('route'));
        $pk = trim($this->option('primary-key'));
        $viewPath = trim($this->option('view-path'));
        $foreign = trim($this->option('foreign'));
        $reqFields = trim($this->option('required-fields'));
        $namespace = trim($this->option('route-group'));
        $prefix = trim($this->option('route-group'));
        $relationship = trim($this->option('relationship'));

        return (object) compact(
            'name',
            'fields',
            'route',
            'pk',
            'viewPath',
            'namespace',
            'prefix',
            'foreign',
            'relationship',
            'reqFields'
        );
    }

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

        if(!$this->FileExists($path)) {
            File::makeDirectory($path, 0775, true);
        }

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

    protected function getLoginControllerPath($name)
    {
        $path = app_path();
        $path = $path . "/Http/Controllers/" . $name . "Controller.php";
        return $path;
    }

    protected function replaceNameSpacePrefix(&$stub, $prefix = '')
    {
        if($prefix != '') $prefix = "\\". $prefix;
        $stub = str_replace(
            '{{namespaceprefix}}', $prefix, $stub
        );

        return $this;
    }

    protected function getModelPath($name)
    {
        $path = app_path();
        $path = $path . $name . ".php";
        return $path;
    }

    protected function getStub($name)
    {
        return __DIR__ . '/Stubs/' . $name . '.blade.stub';
    }

    protected function getControllerStub($name)
    {
        return __DIR__ . '/Stubs/' . $name;
    }

}
