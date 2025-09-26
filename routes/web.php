<?php



use App\Http\Controllers\CrudGeneratorController;
use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\TrackUserActivity;


// Route::get('/', function () {
//     return view('admin.layouts.master');
// });

Auth::routes();


// ✅ All admin routes grouped here
Route::prefix('admin') ->as('admin.')->middleware(['auth', TrackUserActivity::class])->group(function () {

    Broadcast::routes(['middleware' => ['auth']]);

Route::get('logout',function(){
    Auth::logout();
    return redirect('/');
});

    // include crud.php with admin prefix
    require base_path('routes/crud.php');

    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

    Route::middleware(['auth',TrackUserActivity::class])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('users', UserController::class);
        Route::resource('menus', MenuController::class);
        Route::resource('profile', ProfileController::class)->only('index','store');
        Route::resource('general-settings', GeneralSettingController::class)->only('index','store');
        Route::resource('crud-generator', CrudGeneratorController::class)->only('index','create');
        Route::post('crud-generator/generate', [CrudGeneratorController::class, 'generate'])->name('crud-generator.generate');

        Route::get('add-new-permission',function(){
    // === 1. Define Modules and Actions ===
            $modules = ['crud-generator'];
            $actions = ['create', 'index', 'update', 'delete'];

            // === 2. Create Super Admin Role ===
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);

            // === 3. Create Permissions and assign to super-admin ===
            foreach ($modules as $module) {
                foreach ($actions as $action) {
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => "{$module}-{$action}",
                    ]);
                    $adminRole->givePermissionTo($permission);
                }
            }
        });

    });

    
});
