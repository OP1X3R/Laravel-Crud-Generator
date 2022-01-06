<?php

namespace jlcrud\CrudGenerator\Commands\Views;

use Illuminate\Console\Command;
use File;
use jlcrud\CrudGenerator\Commands\Bases\CommonBase;

class createCreateViewCommand extends CommonBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:create-view
                            {name : Name of the view.}
                            {--crud-name= : Name of the crud.}
                            {--fields= : Fields used in the form.}
                            {--view-path= : View path for the views.}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new view for the crud.';

    protected $indexTableHeaderText = '';

    protected $indexBodyHeaderText = '';

    protected $cardBodyText = '';

    protected $formGroupRowsText = '';

    protected $editFormGroupRowsText = '';

    protected $jqueryText = '';

    protected $crudFields = array();

    protected $possibleTypes = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'file' => 'file',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'foreign' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'boolean' => 'checkbox',
    ];


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inputData = $this->getInput();

        $fieldsArr = explode(',', $inputData->fields);

        if($inputData->fields)
        {
            $x = 0;
            foreach ($fieldsArr as $item) {
                $itemArray = explode(':', $item);
                $this->crudFields[$x]['name'] = trim($itemArray[0]);
                $this->crudFields[$x]['type'] = trim($itemArray[1]);
                $x++;
            }
        }

        foreach($this->crudFields as $field)
        {
            $this->createFormRow($field);
            $this->createEditFormRow($field, $inputData->crudName);
            $this->addJquery($field);
        }

        $destination = base_path('resources/views/crud/');

        $layoutpath = $destination . "layout.blade.php";
        $layoutstub = $this->getFileContent($this->getStub("layout"));

        $this->createFile($layoutpath, $layoutstub); //createFile will not create if already exists, so no need to check here.

        if ($this->option('view-path')) $destination =  $destination . '/' . $this->option('view-path') . '/';

        $createpath = $destination . "create_" . $inputData->viewName . ".blade.php";
        $createstub = $this->getFileContent($this->getStub("create"));

        $this->createFile($createpath, $createstub);
        $this->changeCreateVariables($createpath, $inputData);

        $indexpath = $destination . "index_" . $inputData->viewName . ".blade.php";
        $indexstub = $this->getFileContent($this->getStub("index"));

        $this->createFile($indexpath, $indexstub);
        $this->changeIndexVariables($indexpath, $inputData);

        $showpath = $destination . "show_" . $inputData->viewName . ".blade.php";
        $showstub = $this->getFileContent($this->getStub("show"));

        $this->createFile($showpath, $showstub);
        $this->changeShowVariables($showpath, $inputData);

        $editpath = $destination . "edit_" . $inputData->viewName . ".blade.php";
        $editstub = $this->getFileContent($this->getStub("edit"));

        $this->createFile($editpath, $editstub);
        $this->changeEditVariables($editpath, $inputData);

        $this->info("Views were crafted successfully.");
    }

    public function changeIndexVariables($indexpath, $inputData)
    {
        $this->createIndexTableHeader();
        $this->createIndexTableBody($inputData);
        File::put($indexpath, str_replace('{{modelName}}', $inputData->crudName, File::get($indexpath)));
        File::put($indexpath, str_replace('{{modelNameLowercase}}', strtolower($inputData->crudName), File::get($indexpath)));
        File::put($indexpath, str_replace('{{tableHeader}}', $this->indexTableHeaderText, File::get($indexpath)));
        File::put($indexpath, str_replace('{{tableBody}}', $this->indexBodyHeaderText, File::get($indexpath)));
    }

    public function changeShowVariables($showpath, $inputData)
    {
        $this->createShowCardBody($inputData);
        File::put($showpath, str_replace('{{modelName}}', $inputData->crudName, File::get($showpath)));
        if($inputData->viewPath != "") {
            File::put($showpath, str_replace('{{viewPath}}', "/" . $inputData->viewPath, File::get($showpath)));
        }
        else
        {
            File::put($showpath, str_replace('{{viewPath}}', $inputData->viewPath, File::get($showpath)));
        }
        File::put($showpath, str_replace('{{modelNameLowercase}}', strtolower($inputData->crudName), File::get($showpath)));
        File::put($showpath, str_replace('{{cardBody}}', $this->cardBodyText, File::get($showpath)));
    }

    public function changeCreateVariables($createPath, $inputData)
    {
        File::put($createPath, str_replace('{{modelName}}', $inputData->crudName, File::get($createPath)));
        if($inputData->viewPath != "") {
            File::put($createPath, str_replace('{{viewPath}}', "/" . $inputData->viewPath, File::get($createPath)));
        }
        else
        {
            File::put($createPath, str_replace('{{viewPath}}', $inputData->viewPath, File::get($createPath)));
        }
        File::put($createPath, str_replace('{{formGroupRows}}', $this->formGroupRowsText, File::get($createPath)));
        File::put($createPath, str_replace('{{modelNameLowercase}}', strtolower($inputData->crudName), File::get($createPath)));
        File::put($createPath, str_replace('{{javaCode}}', $this->jqueryText, File::get($createPath)));
    }

    public function changeEditVariables($editpath, $inputData)
    {
        File::put($editpath, str_replace('{{modelName}}', $inputData->crudName, File::get($editpath)));
        if($inputData->viewPath != "") {
            File::put($editpath, str_replace('{{viewPath}}', "/" . $inputData->viewPath, File::get($editpath)));
        }
        else
        {
            File::put($editpath, str_replace('{{viewPath}}', $inputData->viewPath, File::get($editpath)));
        }
        File::put($editpath, str_replace('{{editGroupRows}}', $this->editFormGroupRowsText, File::get($editpath)));
        File::put($editpath, str_replace('{{modelNameLowercase}}', strtolower($inputData->crudName), File::get($editpath)));
        File::put($editpath, str_replace('{{javaCode}}', $this->jqueryText, File::get($editpath)));
    }

    public function createShowCardBody($inputData)
    {
        if($this->crudFields[0]['name'] != 'id') $this->cardBodyText .= '<div class="mb-1">id: ' . '{{$' . $inputData->crudName . '->id}}</div>';
        foreach($this->crudFields as $item) {
            $this->cardBodyText .= '<div class="mb-1"> ' . ucfirst($item['name']) . ': <i>{{$' . $inputData->crudName . '->' . $item['name'] . '}}</i> </div>';
        }
    }

    public function createIndexTableBody($inputData)
    {
        if($this->crudFields[0]['name'] != 'id') $this->indexBodyHeaderText .= '<th scope="col">{{$item->id}}</th>';
        foreach($this->crudFields as $item) {
            $this->indexBodyHeaderText .= '<th scope="col">{{$item->' . $item['name'] . '}}</th>';
        }
        $this->indexBodyHeaderText .= '<td class="text-center actions">
                                        @if(!Auth::guest() && Auth::user()->isAdmin == 1)
                                            <a href="' . strtolower($inputData->crudName) . '/{{$item->id}}" type="button" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                            <a href="' . strtolower($inputData->crudName) . '/{{$item->id}}/edit" type="button" class="btn btn-success"><i class="fa fa-edit"></i></a>
                                            <form action="' . strtolower($inputData->crudName) . '/{{$item->id}}" method="POST">
                                                @csrf
                                                @method(\'DELETE\')
                                                <button type="submit" type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        @else
                                            <a href="' . strtolower($inputData->crudName) . '/{{$item->id}}" type="button" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                                            <button type="button" class="btn btn-success" title="No permission to edit." disabled><i class="fa fa-edit"></i></button>
                                            <button type="button" class="btn btn-danger" title="No permission to delete." disabled><i class="fa fa-trash"></i></button>
                                        @endif
                                        </td>';
    }

    public function createIndexTableHeader()
    {
        if($this->crudFields[0]['name'] != 'id') $this->indexTableHeaderText .= '<th>id</th>';
        foreach($this->crudFields as $item) {
            $this->indexTableHeaderText .= '<th>' . ucfirst($item['name']) . '</th>';
        }
        $this->indexTableHeaderText .= '<th>Actions</th>';
    }

    protected function getInput()
    {
        $viewName = trim($this->argument('name'));
        $fields = trim($this->option('fields'));
        $viewPath = trim($this->option('view-path'));
        $crudName = trim($this->option('crud-name'));

        return (object) compact(
            'viewName',
            'fields',
            'viewPath',
            'crudName'
        );
    }

    protected function getPath($name)
    {
        $path = app_path();
        $path = $path . '/Views/' . $name . '.php';
        return $path;
    }

    protected function getStub($name)
    {
        return __DIR__ . '/Stubs/' . $name . '.blade.stub';
    }

    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace(
            '{{modelName}}', $modelName, $stub
        );

        return $this;
    }

    protected function createFormRow($field)
    {
        switch ($this->possibleTypes[$field['type']]) {
            case 'datetime-local':
            case 'time':
                return $this->createInputField($field);
                break;
            case 'password':
                return $this->createPasswordField($field);
                break;
            case 'checkbox':
                return $this->creatCheckboxField($field);
                break;
            case 'textarea':
                return $this->createTextAreaField($field);
                break;
            default: // text
                return $this->createInputField($field);
        }
    }

    protected function createInputField($field) {
        $text = '<div class="form-group row">
                      <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                      <div class="col-sm-10">
                           <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" placeholder="Enter the ' . $field['name'] . '...">
                      </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror
                 ';
        $this->formGroupRowsText .= $text;

    }

    protected function createPasswordField($field) {
        $text = '<div class="form-group row">
                      <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                      <div class="col-sm-10">
                           <input type="password" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" placeholder="Enter the ' . $field['name'] . '...">
                      </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror
                 ';
        $this->formGroupRowsText .= $text;
    }

    protected function creatCheckboxField($field) {
        $text = '<div class="form-group row">
                  <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                   <div class="form-check">
                    <div class="col-sm-10">
                     <input type="hidden" name="' . $field['name'] . '" value="0"/>
                     <input class="form-check-input" type="checkbox" value="" name="' . $field['name'] . '" id="' . $field['name'] . '">
                     <label class="form-check-label" for="' . $field['name'] . '">
                       Yes
                     </label>
                    </div>
                   </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror';
        $this->formGroupRowsText .= $text;
    }
    protected function createTextAreaField($field) {
        $text = '<div class="form-group row">
                  <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                  <div class="col-sm-10">
                   <textarea class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" rows="4"></textarea>
                  </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror';
        $this->formGroupRowsText .= $text;
    }

    protected function addJquery($field) {
        if(strtolower($field['type']) == 'boolean') {
            $text = '
                $("#' . $field['name'] . '").val() == 0 ? $("#' . $field['name'] . '").prop("checked", false) : "";
                $("#' . $field['name'] . '").click(function () {
                    $("#' . $field['name'] . '").val($("#' . $field['name'] . '").is(":checked") ? 1 : 0);
                });';
            $this->jqueryText .= $text;
        }
    }

    protected function createEditFormRow($field, $name)
    {
        switch ($this->possibleTypes[$field['type']]) {
            case 'datetime-local':
            case 'time':
                return $this->createEditInputField($field, $name);
                break;
            case 'password':
                return $this->createEditPasswordField($field, $name);
                break;
            case 'checkbox':
                return $this->creatEditCheckboxField($field, $name);
                break;
            case 'textarea':
                return $this->createEditTextAreaField($field, $name);
                break;
            default: // text
                return $this->createEditInputField($field, $name);
        }
    }



    protected function createEditInputField($field, $name) {
        $text = '<div class="form-group row">
                      <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                      <div class="col-sm-10">
                           <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="{{$' . $name . '->' . $field['name'] . '}}">
                      </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror
                 ';
        $this->editFormGroupRowsText .= $text;
    }

    protected function createEditPasswordField($field, $name) {
        $text = '<div class="form-group row">
                      <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                      <div class="col-sm-10">
                           <input type="password" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="{{$' . $name . '->' . $field['name'] . '}}">
                      </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror
                 ';
        $this->editFormGroupRowsText .= $text;
    }

    protected function creatEditCheckboxField($field, $name) {
        $text = '<div class="form-group row">
                  <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                   <div class="form-check">
                    <div class="col-sm-10">
                     <input type="hidden" name="' . $field['name'] . '" value="0"/>
                     <input class="form-check-input" type="checkbox" checked="{{$' . $name . '->' . $field['name'] . '}}" value="{{$' . $name . '->' . $field['name'] . '}}" name="' . $field['name'] . '" id="' . $field['name'] . '">
                     <label class="form-check-label" for="' . $field['name'] . '">
                       Yes
                     </label>
                    </div>
                   </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror';
        $this->editFormGroupRowsText .= $text;
    }

    protected function createEditTextAreaField($field, $name) {
        $text = '<div class="form-group row">
                  <label for="' . $field['name'] . '" class="col-sm-2 col-form-label">' . $field['name'] . '</label>
                  <div class="col-sm-10">
                   <textarea class="form-control"  name="' . $field['name'] . '" id="' . $field['name'] . '" rows="4">{{$' . $name . '->' . $field['name'] . '}}</textarea>
                  </div>
                 </div>
                 @error(\'' . $field['name'] . '\')
                    <div class="alert alert-danger">{{ $message }}</div>
                 @enderror';
        $this->editFormGroupRowsText .= $text;
    }
}
