<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Requests\Post\PostDeleteRequest;
use App\Services\Post\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Traits\ApiResponse;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PostService $service
    ) {}

    public function all(Request $request)
    {
        $posts = $this->service->getAll($request);
        $metadata['count'] = count($posts);
        if(!$posts){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($posts, $metadata);
    }

    public function index(Request $request)
    {
        $perPage = request('per_page') ?? config('app.per_page');
        $posts = $this->service->index($request, $perPage);
        if(!$posts){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($posts);
    }

    public function store(PostStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->service->create($request->validated(), $request);
            DB::commit();
            return $this->ResponseSuccess($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseError($e->getMessage(). " in " . $e->getFile() . " on line " . $e->getLine(), null, 'Data Process Error!');
        }
    }

    public function show(int $id)
    {
        $post = $this->service->getById($id);
        if(!$post){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($post);
    }

    public function update(PostUpdateRequest $request, int $id)
    {
        DB::beginTransaction();
        try {
            $data = $this->service->update($id, $request->validated(), $request);
            DB::commit();
            return $this->ResponseSuccess($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseError($e->getMessage(). " in " . $e->getFile() . " on line " . $e->getLine(), null, 'Data Process Error!');
        }
    }

    public function destroy(Request $request, int $id)
    {
        DB::beginTransaction();
        try {
            $data = $this->service->delete($id);
            if(!$data){
                return $this->ResponseSuccess([], null, 'Data Not Found!', 204);
            }
            DB::commit();
            return $this->ResponseSuccess($data, null, 'Data successfully delete!', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseError($e->getMessage(). " in " . $e->getFile() . " on line " . $e->getLine(), null, 'Data Not Found!');
        }
    }

    public function status(Request $request, int $id)
    {
        DB::beginTransaction();
        try {
            $data = $this->service->updateStatus($id, $request);
            DB::commit();
            return $this->ResponseSuccess($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->ResponseError($e->getMessage() . 'in ' . $e->getFile() . 'on line ' . $e->getLine(), null, 'Data Process Error!');
        }
    }
}
