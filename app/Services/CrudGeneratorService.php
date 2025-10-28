<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Str;

class CrudGeneratorService
{
    /**
     * Generate full CRUD scaffold code for a given table name and columns.
     *
     * @param string $tableName
     * @param array $columns Array of columns, each as ['name'=>string, 'type'=>string, 'required'=>bool, 'show_in_table'=>bool ...]
     * @return array Associative array with keys: migration, model, controller, views (index, create), routes
     */
    public function generate(string $tableName, array $columns): array
    {
        $modelName = Str::studly(Str::singular($tableName));
        $modelVariable = Str::camel(Str::singular($tableName));
        $controllerName = $modelName . 'Controller';

        // Generate migration file
        $migrationContent = $this->generateMigration($tableName, $columns);
        $migrationFileName = date('Y_m_d_His') . "_create_{$tableName}_table.php";
        $this->createFile(database_path("migrations/{$migrationFileName}"), $migrationContent);

        // Generate model file
        $modelContent = $this->generateModel($modelName, $tableName, $columns);
        $this->createFile(app_path("Models/{$modelName}.php"), $modelContent);

        // Generate controller file
        $controllerContent = $this->generateController($modelName, $controllerName, $tableName, $columns);
        $this->createFile(app_path("Http/Controllers/{$controllerName}.php"), $controllerContent);

        // Generate views
        $viewFolder = resource_path("views/admin/{$tableName}");
        if (!file_exists($viewFolder)) {
            mkdir($viewFolder, 0755, true);
        }
        $views = $this->generateViews($modelName, $modelVariable, $tableName, $columns);
        foreach ($views as $viewName => $viewContent) {
            $this->createFile("{$viewFolder}/{$viewName}.blade.php", $viewContent);
        }

        $modelName = $this->formalText($tableName);

        // Append routes
        $routeContent = $this->generateRoutes($tableName, $controllerName);
        file_put_contents(base_path('routes/crud.php'), "\n" . $routeContent, FILE_APPEND);

        $this->addMenuAndPermissions($modelName);

        return [
            'migration' => $migrationFileName,
            'model' => "{$modelName}.php",
            'controller' => "{$controllerName}.php",
            'views' => array_keys($views),
            'routes' => 'Appended to web.php',
        ];
    }

    protected function createFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
    }

    protected function generateMigration(string $tableName, array $columns): string
    {
        $fields = '';
        foreach ($columns as $col) {
            $name = $col['name'];
            $type = $col['type'];

            $nullable = $col['required'] ?? false ? '' : '->nullable()';

            switch ($type) {
                case 'string':
                case 'varchar':
                    $fields .= "\$table->string('$name')$nullable;\n            ";
                    break;
                case 'text':
                    $fields .= "\$table->text('$name')$nullable;\n            ";
                    break;
                case 'longText':
                    $fields .= "\$table->longText('$name')$nullable;\n            ";
                    break;
                case 'integer':
                case 'int':
                    $fields .= "\$table->integer('$name')$nullable;\n            ";
                    break;
                case 'unsignedBigInteger':
                    $fields .= "\$table->unsignedBigInteger('$name')$nullable;\n            ";
                    break;
                case 'double':
                case 'float':
                    $fields .= "\$table->double('$name')$nullable;\n            ";
                    break;
                case 'boolean':
                    $fields .= "\$table->boolean('$name')->default(true);\n            ";
                    break;
                case 'date':
                    $fields .= "\$table->date('$name')$nullable;\n            ";
                    break;
                case 'datetime':
                    $fields .= "\$table->dateTime('$name')$nullable;\n            ";
                    break;
                case 'json':
                    $fields .= "\$table->json('$name')$nullable;\n            ";
                    break;
                case 'image':
                case 'file':
                    // store path as string
                    $fields .= "\$table->string('$name')$nullable;\n            ";
                    break;
                default:
                    $fields .= "\$table->string('$name')$nullable;\n            ";
            }
        }

        return <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create{$this->studlyCase($tableName)}Table extends Migration
{
    public function up()
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->id();
            $fields
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('$tableName');
    }
}
PHP;
    }

    protected function studlyCase(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    protected function formalText(string $value): string
    {
        // this returns snake_case to formal text like menu_items => Menu Items
        return ucwords(str_replace('_', ' ', $value));
    }

    protected function generateModel(string $modelName, string $tableName, array $columns): string
    {
        // Prepare guarded and relationship methods
        $relations = [];

        foreach ($columns as $col) {
            // Add relationship only if foreign_table and foreign_column are present
            if (!empty($col['foreign_table']) && !empty($col['foreign_column'])) {
                $relationName = Str::camel(Str::singular($col['foreign_table']));
                $relatedModel = Str::studly(Str::singular($col['foreign_table']));

                $relations[] = <<<PHP

        public function $relationName()
        {
            return \$this->belongsTo(\\App\\Models\\$relatedModel::class, '{$col['name']}', '{$col['foreign_column']}');
        }
    PHP;
            }
        }

        $relationsStr = implode("\n", $relations);

        return <<<PHP
    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class $modelName extends Model
    {
        use HasFactory;

        protected \$table = '$tableName';

        protected \$guarded = [];
    $relationsStr
    }
    PHP;
    }

    protected function generateController(string $modelName, string $controllerName, string $tableName, array $columns): string
    {
        $permissionBase = Str::snake($modelName);
        $modelVariable = Str::camel($modelName);
        $headline = Str::headline($modelName);

        $validationRules = [];
        $storeFields = [];
        $updateFields = [];
        $relatedModels = [];
        $relatedViewData = [];

        $filterConditions = [];
        $filterViewData = [];

        foreach ($columns as $col) {
            $rule = $col['required'] ? 'required' : 'nullable';
            $typeRule = match($col['type']) {
                'string', 'varchar' => 'string',
                'text', 'longText' => 'string',
                'integer', 'int', 'unsignedBigInteger' => 'integer',
                'double', 'float' => 'numeric',
                'boolean' => 'boolean',
                'date' => 'date',
                'datetime' => 'date',
                'json' => 'json',
                'image', 'file' => 'file',
                default => 'string',
            };

            $validationRules[] = "'{$col['name']}' => '$rule|$typeRule'";

            if (in_array($col['type'], ['image', 'file'])) {
                $storeFields[] = "'{$col['name']}' => \$request->file('{$col['name']}') ? upload_asset(\$request->file('{$col['name']}')) : null,";
                $updateFields[] = <<<PHP
                if (\$request->hasFile('{$col['name']}')) {
                    delete_uploaded_asset(\${$modelVariable}->{$col['name']});
                    \$data['{$col['name']}'] = upload_asset(\$request->file('{$col['name']}'));
                }
                PHP;
            } else {
                $storeFields[] = "'{$col['name']}' => \$request->input('{$col['name']}'),";
                $updateFields[] = "\$data['{$col['name']}'] = \$request->input('{$col['name']}');";
            }

            if (!empty($col['foreign_table']) && !empty($col['foreign_column'])) {
                $relatedModelName = Str::studly(Str::singular($col['foreign_table']));
                $relatedVarName = Str::camel(Str::plural(Str::snake($relatedModelName)));

                $relatedModels[$relatedModelName] = "use App\Models\\$relatedModelName;";
                $relatedViewData[$relatedVarName] = "\$$relatedVarName = $relatedModelName::all();";

            }

            if($col['is_filter'])
            {
                if (in_array($col['type'], ['date', 'datetime'])) {
                    $filterConditions[] = <<<PHP
                    if (\$request->filled('{$col['name']}')) {
                        \$dates = explode(' - ', \$request->input('{$col['name']}'));
                        if (count(\$dates) === 2) {
                        \$dates[0] = \Carbon\Carbon::parse(trim(\$dates[0]));
                        \$dates[1] = \Carbon\Carbon::parse(trim(\$dates[1]));
                            \$query->whereDate('{$col['name']}', '>=', \$dates[0])
                                ->whereDate('{$col['name']}', '<=', \$dates[1]);
                        }
                    }
                    PHP;

                    $filterViewData[] = "'{$col['name']}' => \$request->input('{$col['name']}')";
                } else {
                    // Add filter condition
                    $filterConditions[] = <<<PHP
                    if (\$request->filled('{$col['name']}')) {
                        \$query->where('{$col['name']}', 'like', '%' . \$request->input('{$col['name']}') . '%');
                    }
                    PHP;
                }

                $filterViewData[] = "'{$col['name']}' => \$request->input('{$col['name']}')";
            }
        }

        $validationStr = implode(",\n            ", $validationRules);
        $storeFieldsStr = implode("\n                ", $storeFields);
        $updateFieldsStr = implode("\n            ", $updateFields);
        $relatedUses = implode("\n", $relatedModels);
        $relatedCreateViewVars = implode("\n        ", $relatedViewData);
        $relatedEditViewVars = implode("\n        ", $relatedViewData);

        $relatedCreateAssocArray = implode(",\n            ", array_map(
            fn($var) => "'$var' => \$$var", array_keys($relatedViewData)
        ));

        $relatedEditAssocArray = $relatedCreateAssocArray
            ? "$relatedCreateAssocArray,\n            '$modelVariable' => \$$modelVariable"
            : "'$modelVariable' => \$$modelVariable";

        $filterConditionsStr = implode("\n", $filterConditions);
        $filterViewParams = !empty($filterViewData)
            ? ', ' . implode(', ', $filterViewData)
            : '';

        return <<<PHP
    <?php

    namespace App\Http\Controllers;

    use App\Models\\$modelName;
    $relatedUses
    use Illuminate\Http\Request;

    class $controllerName extends Controller
    {
        public function __construct()
        {
            \$this->middleware('permission:{$permissionBase}-index')->only('index');
            \$this->middleware('permission:{$permissionBase}-create')->only(['create', 'store']);
            \$this->middleware('permission:{$permissionBase}-update')->only(['edit', 'update']);
            \$this->middleware('permission:{$permissionBase}-delete')->only('destroy');
        }

        public function index(Request \$request)
        {
            \$query = $modelName::orderBy('created_at', 'desc');
    $filterConditionsStr

            \${$modelVariable}Lists = \$query->paginate(10);
            {$relatedCreateViewVars}
            return view('admin.$tableName.index', [
                '{$modelVariable}Lists' => \${$modelVariable}Lists$filterViewParams
            ]);
        }

        public function create()
        {
            $relatedCreateViewVars
            return view('admin.$tableName.create', [
                $relatedCreateAssocArray
            ]);
        }

        public function store(Request \$request)
        {
            \$request->validate([
                $validationStr
            ]);

            $modelName::create([
                $storeFieldsStr
            ]);

            return redirect()->route('admin.$tableName.index')->with('success', '$headline created successfully.');
        }

        public function show($modelName \${$modelVariable})
        {
            return view('admin.$tableName.show', ['{$modelVariable}' => \${$modelVariable}]);
        }

        public function edit($modelName \${$modelVariable})
        {
            $relatedEditViewVars
            return view('admin.$tableName.edit', [
                $relatedEditAssocArray
            ]);
        }

        public function update(Request \$request, $modelName \${$modelVariable})
        {
            \$request->validate([
                $validationStr
            ]);

            \$data = [];
            $updateFieldsStr

            \${$modelVariable}->update(\$data);

            return redirect()->route('admin.$tableName.index')->with('success', '$headline updated successfully.');
        }

        public function destroy($modelName \${$modelVariable})
        {
            \${$modelVariable}->delete();
            return redirect()->route('admin.$tableName.index')->with('success', '$headline deleted successfully.');
        }
    }
    PHP;
    }

    protected function generateViews(string $modelName, string $modelVariable, string $tableName, array $columns): array
    {
        $tableHeaders = '';
        $tableBody = '';
        $permissionBase = Str::snake($modelName);
        $countCols = count($columns);
        $headline = Str::headline($modelName);
        $filterForm = "";

        foreach ($columns as $col) {
            $name = $col['name'];
            $required = !empty($col['required']) ? 'required' : '';
            $requiredStar = !empty($col['required']) ? '<span class="text-danger">*</span>' : '';

            if (isset($col['foreign_table']) && isset($col['foreign_column']) && isset($col['foreign_column_title'])) {
                $label = Str::headline(preg_replace('/_id$/', '', $col['name']));

                $relationMethod = Str::camel(str_replace('_id', '', $col['name']));
                $relatedModel = Str::studly(Str::singular($col['foreign_table']));
                $relationLabel = $col['foreign_column_title'];


                $filterFormItem = <<<BLADE
                <div class="col-md-3 mb-2">
                    <label for="$name">$label $requiredStar</label>
                    <select name="$name" id="$name" class="form-control" $required>
                        <option value="">-- Select $label --</option>
                        @foreach(\\App\\Models\\$relatedModel::get() as \$item)
                            <option value="{{ \$item->id }}" {{ request('$name') == \$item->id ? 'selected' : '' }}>
                                {{ \$item->$relationLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                BLADE;
                $tableBodyItem = "<td>{{ \${$modelVariable}Item->{$relationMethod}?->{$relationLabel} }}</td>\n                            ";
            } elseif(in_array($col['type'],['image','file'])) {
                $label = ucfirst(str_replace('_', ' ', $col['name']));
                $filterFormItem = <<<BLADE
                <div class="col-md-3 mb-2">
                    <label for="$name">$label $requiredStar</label>
                    <input type="text" name="$name" id="$name" class="form-control" value="{{ request('$name') }}" $required>
                </div>
                BLADE;

                $tableBodyItem = "<td><img src=\"{{ get_uploaded_asset(\${$modelVariable}Item->{$col['name']}) }}\" alt=\"$label\" style=\"max-width:80px; max-height:80px;\"></td>\n                            ";
            } elseif($col['type'] == 'date') {
                $label = ucfirst(str_replace('_', ' ', $col['name']));
                $filterFormItem = <<<BLADE
                <div class="col-md-3 mb-2">
                    <label for="$name">$label $requiredStar</label>
                    <input type="text" name="$name" id="$name" class="form-control datepick" value="{{ request('$name') }}" $required>
                </div>
                BLADE;

                $tableBodyItem = "<td>{{ formatDate(\${$modelVariable}Item->{$col['name']}) }}</td>\n                            ";
            } elseif($col['type'] == 'datetime') {
                $label = ucfirst(str_replace('_', ' ', $col['name']));
                $filterFormItem = <<<BLADE
                <div class="col-md-3 mb-2">
                    <label for="$name">$label $requiredStar</label>
                    <input type="text" name="$name" id="$name" class="form-control datepick" value="{{ request('$name') }}" $required>
                </div>
                BLADE;

                $tableBodyItem = "<td>{{ formatDateTime(\${$modelVariable}Item->{$col['name']}) }}</td>\n                            ";
            } else {
                $label = ucfirst(str_replace('_', ' ', $col['name']));

                $filterFormItem = <<<BLADE
                <div class="col-md-3 mb-2">
                    <label for="$name">$label $requiredStar</label>
                    <input type="text" name="$name" id="$name" class="form-control" value="{{ request('$name') }}" $required>
                </div>
                BLADE;

                $tableBodyItem = "<td>{{ \${$modelVariable}Item->{$col['name']} }}</td>\n                            ";
            }

            if($col['is_filter']) {
                $filterForm .= $filterFormItem;
            }
                
            if($col['show_in_table']) {
                $tableHeaders .= "<th>$label</th>\n                            ";
                $tableBody .=  $tableBodyItem;
            }
        }

        $tableHeaders .= "<th>Created At</th>\n                            ";
        $tableBody .= "<td>{{ formatDateTime(\${$modelVariable}Item->created_at) }}</td>\n                            ";

        $filterFormHtml = '';
        if (!empty($filterForm)) {
            $filterFormHtml = <<<BLADE
            <form method="GET" action="{{ route('admin.$tableName.index') }}">
                <div class="row">
                    $filterForm
                    <div class="col-md-3 mb-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.$tableName.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            <hr>
            BLADE;
        }

        // --- Index View ---
        $indexView = <<<BLADE
    @extends('admin.layouts.master')

    @section('body')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>$headline List</h4>
                        @can('$permissionBase-create')
                        <a href="{{ route('admin.$tableName.create') }}" class="btn btn-primary btn-sm">Add New</a>
                        @endcan
                    </div>
                    <div class="card-body">
                        @include('components.alerts')
                        $filterFormHtml
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        $tableHeaders
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(\${$modelVariable}Lists as \${$modelVariable}Item)
                                        <tr>
                                            <td>{{ \$loop->iteration }}</td>
                                            $tableBody
                                            <td>
                                                @can('$permissionBase-index')
                                                <a href="{{ route('admin.$tableName.show', \${$modelVariable}Item) }}" class="btn btn-primary btn-sm">Show</a>
                                                @endcan
                                                @can('$permissionBase-update')
                                                <a href="{{ route('admin.$tableName.edit', \${$modelVariable}Item) }}" class="btn btn-warning btn-sm">Edit</a>
                                                @endcan
                                                @can('$permissionBase-delete')
                                                <form action="{{ route('admin.$tableName.destroy', \${$modelVariable}Item) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ $countCols + 2 }}" class="text-center">No $headline found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ \${$modelVariable}Lists->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    BLADE;

        // --- Form Inputs Generator ---
        $generateInputs = function($isEdit = false) use ($columns, $modelVariable) {
            $inputs = '';
            foreach ($columns as $col) {
                $colName = $col['name'];
                $label = ucfirst(str_replace('_', ' ', $colName));
                $type = $col['type'];

                $required = !empty($col['required']) ? 'required' : '';
                $requiredStar = !empty($col['required']) ? '<span class="text-danger">*</span>' : '';

                // If relation, generate <select>
                if (isset($col['foreign_table'], $col['foreign_column'], $col['foreign_column_title'])) {
                    $relationVar = Str::camel(Str::plural(Str::snake($col['foreign_table'])));
                    $relationLabel = $col['foreign_column_title'];
                    $selectLabel = Str::headline(preg_replace('/_id$/', '', $col['name']));

                    $fieldBlade = <<<HTML
                    <label for="$colName" class="form-label">$selectLabel $requiredStar</label>
                    <select class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName" $required>
                        <option value="">Select $selectLabel</option>
                        @foreach(\$$relationVar as \$item)
                            <option value="{{ \$item->id }}"
                                @if(old('$colName', isset(\${$modelVariable}) ? \${$modelVariable}->$colName : null) == \$item->id) selected @endif>
                                {{ \$item->$relationLabel }}
                            </option>
                        @endforeach
                    </select>
                    @error('$colName')
                        <div class="invalid-feedback">{{ \$message }}</div>
                    @enderror
                    HTML;
                } else {
                    $htmlClass = '';
                    // Input field type logic
                    $inputType = 'text';
                    if (in_array($type, ['image', 'file'])) {
                        $inputType = 'file';
                    } elseif (in_array($type,['text','longText'])) {
                        $inputType = 'textarea';
                        if($type == 'longText') {
                            $htmlClass .= ' html-editor';
                        }
                    } elseif (in_array($type, ['integer', 'unsignedBigInteger'])) {
                        $inputType = 'number';
                    } elseif (in_array($type, ['double', 'float'])) {
                        $inputType = 'number" step="any';
                    } elseif ($type === 'boolean') {
                        $inputType = 'checkbox';
                    } elseif ($type === 'date') {
                        $inputType = 'date';
                    } elseif ($type === 'datetime') {
                        $inputType = 'datetime-local';
                    }

                    if ($inputType === 'textarea') {
                        $value = $isEdit ? "{{ old('$colName', \${$modelVariable}->$colName) }}" : "{{ old('$colName') }}";
                        $fieldBlade = <<<HTML
                        <label for="$colName" class="form-label">$label $requiredStar</label>
                        <textarea class="form-control @error('$colName') is-invalid @enderror $htmlClass" id="$colName" name="$colName" rows="4" $required>$value</textarea>
                        @error('$colName')
                            <div class="invalid-feedback">{{ \$message }}</div>
                        @enderror
                        HTML;
                    } elseif ($inputType === 'checkbox') {
                        $checked = $isEdit 
                            ? "{{ old('$colName', \${$modelVariable}->$colName) ? 'checked' : '' }}" 
                            : "{{ old('$colName') ? 'checked' : '' }}";
                        $fieldBlade = <<<HTML
                        <div class="form-check">
                            <input type="hidden" name="$colName" value="0">
                            <input type="checkbox" class="form-check-input @error('$colName') is-invalid @enderror" id="$colName" name="$colName" value="1" $checked>
                            <label for="$colName" class="form-check-label">$label $requiredStar</label>
                            @error('$colName')
                                <div class="invalid-feedback">{{ \$message }}</div>
                            @enderror
                        </div>
                        HTML;
                    } elseif ($inputType === 'file') {
                        $fieldBlade = <<<HTML
                        <label for="$colName" class="form-label">$label $requiredStar</label>
                        <input type="file" class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName" $required>
                        @error('$colName')
                            <div class="invalid-feedback">{{ \$message }}</div>
                        @enderror
                        HTML;
                    } else {
                        $value = $isEdit ? "{{ old('$colName', \${$modelVariable}->$colName) }}" : "{{ old('$colName') }}";
                        $fieldBlade = <<<HTML
                        <label for="$colName" class="form-label">$label $requiredStar</label>
                        <input type="$inputType" class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName" value="$value" $required>
                        @error('$colName')
                            <div class="invalid-feedback">{{ \$message }}</div>
                        @enderror
                        HTML;
                    }
                }

                $inputs .= <<<HTML

                <div class="form-group">
                    $fieldBlade
                </div>
                HTML;
            }
            return $inputs;
        };

        // --- Create View ---
        $createInputs = $generateInputs(false);
        $createView = <<<BLADE
    @extends('admin.layouts.master')

    @section('body')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Create $headline</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.$tableName.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            $createInputs
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('admin.$tableName.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    BLADE;

        // --- Edit View ---
        $editInputs = $generateInputs(true);
        $editView = <<<BLADE
    @extends('admin.layouts.master')

    @section('body')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Edit $headline</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.$tableName.update', \${$modelVariable}) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            $editInputs
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('admin.$tableName.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    BLADE;

        // --- Show View ---
        $showFields = '';
        foreach ($columns as $col) {
            $label = ucfirst(str_replace('_', ' ', $col['name']));
            if (isset($col['foreign_table'], $col['foreign_column'], $col['foreign_column_title'])) {
                $relationMethod = Str::camel(str_replace('_id', '', $col['name']));
                $relationLabel = $col['foreign_column_title'];
                $showFields .= <<<HTML
                <tr>
                    <th>$label</th>
                    <td>{{ \${$modelVariable}->{$relationMethod}?->{$relationLabel} }}</td>
                </tr>
                HTML;
            } elseif (in_array($col['type'], ['image', 'file'])) {
                $showFields .= <<<HTML
                <tr>
                    <th>$label</th>
                    <td><img src="{{ get_uploaded_asset(\${$modelVariable}->{$col['name']}) }}" alt="$label" style="max-width:100px;"></td>
                </tr>
                HTML;
            } else if($col['type'] =='date') {
                $showFields .= <<<HTML
                <tr>
                    <th>$label</th>
                    <td>{{ formatDate(\${$modelVariable}->{$col['name']}) }}</td>
                </tr>
                HTML;
            } else if($col['type'] =='datetime') {
                $showFields .= <<<HTML
                <tr>
                    <th>$label</th>
                    <td>{{ formatDateTime(\${$modelVariable}->{$col['name']}) }}</td>
                </tr>
                HTML;
            } else {
                $showFields .= <<<HTML
                <tr>
                    <th>$label</th>
                    <td>{{ \${$modelVariable}->{$col['name']} }}</td>
                </tr>
                HTML;
            } 
        }

        $showFields .= <<<HTML
                <tr>
                    <th>Created At</th>
                     <td>{{ formatDateTime(\${$modelVariable}->created_at) }}</td>
                </tr>
                HTML;

        $showView = <<<BLADE
        @extends('admin.layouts.master')

        @section('body')
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Show $headline</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                $showFields
                            </table>
                            <a href="{{ route('admin.$tableName.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection
    BLADE;


        return [
            'index' => $indexView,
            'create' => $createView,
            'edit' => $editView,
            'show' => $showView,
        ];
    }

    protected function generateRoutes(string $tableName, string $controllerName): string
    {
        return "Route::resource('$tableName', \\App\\Http\\Controllers\\$controllerName::class)->middleware('auth',TrackUserActivity::class);";
    }

    protected function addMenuAndPermissions($moduleName)
    {
        // $module = 'crud-generator'; eg .
        $module = Str::snake($moduleName); // ensure singular and snake_case
        $modulePermission = Str::snake(Str::singular($moduleName)); // ensure singular and snake_case
        $actions = ['create', 'index', 'update', 'delete'];

        // === 2. Create Super Admin Role ===
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);

        // === 3. Create Permissions and assign to super-admin ===
        
            foreach ($actions as $action) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                    'name' => "{$modulePermission}-{$action}",
                ]);
                $adminRole->givePermissionTo($permission);
            }

        // === 4. Create Menu ===

        Menu::firstOrCreate([
                'title' => ucfirst($moduleName),
                'route' => "{$module}",
                'permission_name' => "{$modulePermission}-index",
                'parent_id' => null,
            ], [
                'icon' => 'fa fa-bars',
                'order' => 0,
            ]);
    }
}
