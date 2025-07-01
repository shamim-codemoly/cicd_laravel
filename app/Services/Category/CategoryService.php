<?php

namespace App\Services\Category;

use App\Interfaces\Category\CategoryInterface;
use App\Models\Category;

final class CategoryService implements CategoryInterface
{
    public function __construct(
        protected Category $model
    ){}

    public function index($request, int $per_page)
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
        ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search . '%');
            })
        ->orderBy($orderColumn, $orderDirection)
        ->paginate($per_page);
    }
    public function getAll($request)
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
        ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search . '%');
            })
        ->orderBy($orderColumn, $orderDirection)
        ->get();
    }

    public function getById(int $id)
    {
        $record = $this->model::find($id);

        return $record ?? null;
    }

    public function create(array $data, $request)
    {
        $newModel = $this->model::create($data);
        return $newModel;
    }

    public function update(int $id, array $data, $request)
    {
        $model = $this->model::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete(int $id)
    {
        $model = $this->model::findOrFail($id);
        return $model->delete();
    }

    public function updateStatus($id, $request)
    {
        $model = $this->model::findOrFail($id);
        $model->update([
            'is_active' => !$model->is_active
        ]);

        return $model;
    }
}

