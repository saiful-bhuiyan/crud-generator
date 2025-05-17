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
     * @param array $columns Array of columns, each as ['name'=>string, 'type'=>string, 'required'=>bool, ...]
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
        $viewFolder = resource_path("views/{$tableName}");
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
                    $fields .= "\$table->boolean('$name')->default(false);\n            ";
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
        $fillable = [];
        foreach ($columns as $col) {
            $fillable[] = "'{$col['name']}'";
        }
        $fillableStr = implode(", ", $fillable);

        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class $modelName extends Model
{
    use HasFactory;

    protected \$table = '$tableName';

    protected \$fillable = [
        $fillableStr
    ];
}
PHP;
    }

    protected function generateController(string $modelName, string $controllerName, string $tableName, array $columns): string
    {
        $permissionBase = Str::snake($modelName);
        $modelVariable = Str::camel($modelName);
        $validationRules = [];
        foreach ($columns as $col) {
            $rule = $col['required'] ? 'required' : 'nullable';
            $typeRule = match($col['type']) {
                'string', 'varchar' => 'string',
                'text' => 'string',
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
        }
        $validationStr = implode(",\n            ", $validationRules);

        $storeUpdateFields = [];
        foreach ($columns as $col) {
            $storeUpdateFields[] = "'{$col['name']}' => \$request->input('{$col['name']}'),";
        }
        $storeUpdateFieldsStr = implode("\n            ", $storeUpdateFields);

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$modelName;
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
        
    public function index()
    {
        \${$modelVariable}s = $modelName::paginate(10);
        return view('$tableName.index', compact('{$modelVariable}s'));
    }

    public function create()
    {
        return view('$tableName.create');
    }

    public function store(Request \$request)
    {
        \$request->validate([
            $validationStr
        ]);

        $modelName::create([
            $storeUpdateFieldsStr
        ]);

        return redirect()->route('$tableName.index')->with('success', '$modelName created successfully.');
    }

    public function show($modelName \${$modelVariable})
    {
        return view('$tableName.show', compact('{$modelVariable}'));
    }

    public function edit($modelName \${$modelVariable})
    {
        return view('$tableName.edit', compact('{$modelVariable}'));
    }

    public function update(Request \$request, $modelName \${$modelVariable})
    {
        \$request->validate([
            $validationStr
        ]);

        \${$modelVariable}->update([
            $storeUpdateFieldsStr
        ]);

        return redirect()->route('$tableName.index')->with('success', '$modelName updated successfully.');
    }

    public function destroy($modelName \${$modelVariable})
    {
        \${$modelVariable}->delete();
        return redirect()->route('$tableName.index')->with('success', '$modelName deleted successfully.');
    }
}
PHP;
    }

    protected function generateViews(string $modelName, string $modelVariable, string $tableName, array $columns): array
    {
        // --- Index View ---

        $tableHeaders = '';
        $tableBody = '';
        $permissionBase = Str::snake($modelName);
        $countCols = count($columns);
        foreach ($columns as $col) {
            $label = ucfirst(str_replace('_', ' ', $col['name']));
            $tableHeaders .= "<th>$label</th>\n                            ";
            $tableBody .= "<td>{{ \${$modelVariable}Item->{$col['name']} }}</td>\n                            ";
        }

        $indexView = <<<BLADE
    @extends('admin.layouts.master')

    @section('body')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>$modelName List</h4>
                        @can('$permissionBase-create')
                        <a href="{{ route('$tableName.create') }}" class="btn btn-primary btn-sm">Add New</a>
                        @endcan
                    </div>
                    <div class="card-body">
                        @include('components.alerts')

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
                                    @forelse(\${$modelVariable}s as \${$modelVariable}Item)
                                        <tr>
                                            <td>{{ \$loop->iteration }}</td>
                                            $tableBody
                                            <td>
                                                @can('$permissionBase-update')
                                                <a href="{{ route('$tableName.edit', \${$modelVariable}Item) }}" class="btn btn-warning btn-sm">Edit</a>
                                                @endcan
                                                @can('$permissionBase-delete')
                                                <form action="{{ route('$tableName.destroy', \${$modelVariable}Item) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ $countCols + 2 }}" class="text-center">No $modelName found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ \${$modelVariable}s->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    BLADE;

        // --- Form Inputs Generator (for create & edit) ---
        $generateInputs = function($isEdit = false) use ($columns, $modelVariable) {
            $inputs = '';
            foreach ($columns as $col) {
                $colName = $col['name'];
                $label = ucfirst(str_replace('_', ' ', $colName));
                $type = $col['type'];

                $inputType = 'text';
                if (in_array($type, ['text'])) {
                    $inputType = 'textarea';
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
                } elseif (in_array($type, ['image', 'file'])) {
                    $inputType = 'file';
                }

                if ($inputType === 'textarea') {
                    $valueBinding = $isEdit
                        ? "{{ old('$colName', \${$modelVariable}->$colName) }}"
                        : "{{ old('$colName') }}";

                    $fieldBlade = <<<HTML
    <textarea class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName" rows="4">$valueBinding</textarea>
    HTML;
                } elseif ($inputType === 'checkbox') {
                    $checkedBinding = $isEdit
                        ? "{{ old('$colName', \${$modelVariable}->$colName) ? 'checked' : '' }}"
                        : "{{ old('$colName') ? 'checked' : '' }}";

                    $fieldBlade = <<<HTML
    <input type="checkbox" class="form-check-input @error('$colName') is-invalid @enderror" id="$colName" name="$colName" value="1" $checkedBinding>
    HTML;
                } elseif ($inputType === 'file') {
                    $fieldBlade = <<<HTML
    <input type="file" class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName">
    HTML;
                } else {
                    $valueBinding = $isEdit
                        ? "{{ old('$colName', \${$modelVariable}->$colName) }}"
                        : "{{ old('$colName') }}";

                    $fieldBlade = <<<HTML
    <input type="$inputType" class="form-control @error('$colName') is-invalid @enderror" id="$colName" name="$colName" value="$valueBinding">
    HTML;
                }

                $inputs .= <<<HTML

    <div class="mb-3">
        <label for="$colName" class="form-label">$label</label>
        $fieldBlade
        @error('$colName')
            <div class="invalid-feedback">{{ \$message }}</div>
        @enderror
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
                        <h4>Create $modelName</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('$tableName.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            $createInputs
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('$tableName.index') }}" class="btn btn-secondary">Cancel</a>
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
                        <h4>Edit $modelName</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('$tableName.update', \${$modelVariable}) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            $editInputs
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('$tableName.index') }}" class="btn btn-secondary">Cancel</a>
                        </form>
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
                    'name' => "{$module}-{$action}",
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
