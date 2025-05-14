@csrf
<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $menu->title ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="route" class="form-label">Route Name</label>
    <input type="text" name="route" class="form-control" value="{{ old('route', $menu->route ?? '') }}">
</div>

<div class="mb-3">
    <label for="icon" class="form-label">Icon (class)</label>
    <input type="text" name="icon" class="form-control" value="{{ old('icon', $menu->icon ?? '') }}">
</div>

<div class="mb-3">
    <label for="permission_name" class="form-label">Permission Name</label>
    <input type="text" name="permission_name" class="form-control" value="{{ old('permission_name', $menu->permission_name ?? '') }}">
</div>

<div class="mb-3">
    <label for="parent_id" class="form-label">Parent Menu</label>
    <select name="parent_id" class="form-select">
        <option value="">None</option>
        @foreach($parents as $parent)
            <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                {{ $parent->title }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="order" class="form-label">Order</label>
    <input type="number" name="order" class="form-control" value="{{ old('order', $menu->order ?? 0) }}">
</div>

<button type="submit" class="btn btn-success">Save</button>
