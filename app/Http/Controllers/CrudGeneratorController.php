<?php

namespace App\Http\Controllers;

use App\Services\CrudGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrudGeneratorController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:crud-generator-index')->only('index');
        $this->middleware('permission:crud-generator-create')->only(['create', 'store']);
        $this->middleware('permission:crud-generator-update')->only(['edit', 'update']);
        $this->middleware('permission:crud-generator-delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modules = DB::table('crud_modules')->get();
        return view('crud-generator.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('crud-generator.create');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'table_name' => ['required', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*.name' => ['required', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns.*.type' => ['required', 'string', 'in:string,integer,bigInteger,unsignedBigInteger,double,text,longText,boolean,date,datetime,image'],
            'columns.*.required' => ['sometimes', 'boolean'],
            'columns.*.show_in_table' => ['sometimes', 'boolean'],
            'columns.*.is_filter' => ['sometimes', 'boolean'],
            'columns.*.foreign_table' => ['nullable', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns.*.foreign_column' => ['nullable', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'columns.*.foreign_column_title' => ['nullable', 'string', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
        ]);

        // Extra logic: if foreign_column is set, foreign_table must be set too
        foreach ($validated['columns'] as $col) {
            if (!empty($col['foreign_column']) && empty($col['foreign_table'])) {
                return back()->withErrors(['columns' => 'Foreign table must be specified if foreign column is set'])->withInput();
            }
        }

        // Then call your CrudGeneratorService to generate the migration/module
        $generator = new CrudGeneratorService();
        $generator->generate($validated['table_name'], $validated['columns']);

        return redirect()->route('admin.crud-generator.index')->with('success', 'Module generated successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
