<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:menu-index')->only('index');
        $this->middleware('permission:menu-create')->only(['create', 'store']);
        $this->middleware('permission:menu-update')->only(['edit', 'update']);
        $this->middleware('permission:menu-delete')->only('destroy');
    }

    public function index()
    {
        $menus = Menu::with('children', 'parent')->whereNull('parent_id')->orderBy('order')->get();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $parents = Menu::whereNull('parent_id')->orderBy('order')->get();
        return view('menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'route' => 'nullable|string',
            'icon' => 'nullable|string',
            'permission_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
        ]);

        Menu::create($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $parents = Menu::whereNull('parent_id')->where('id', '!=', $menu->id)->get();
        return view('menus.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required|string',
            'route' => 'nullable|string',
            'icon' => 'nullable|string',
            'permission_name' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
        ]);

        $menu->update($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu deleted.');
    }
}
