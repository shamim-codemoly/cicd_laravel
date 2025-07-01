<?php

namespace App\Interfaces\Category;

interface CategoryInterface
{
    public function index($request, int $per_page);

    public function getAll($request);

    public function getById(int $id);

    public function create(array $data, $request);

    public function update(int $id, array $data, $request);

    public function delete(int $id);
}
