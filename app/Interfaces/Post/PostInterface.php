<?php

namespace App\Interfaces\Post;

interface PostInterface
{
    public function index($request, int $per_page);

    public function getAll($request);

    public function getById(int $id);

    public function create(array $data, $request);

    public function update(int $id, array $data, $request);

    public function delete(int $id);
}
