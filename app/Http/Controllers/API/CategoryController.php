<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Requests\Category\CategoryDeleteRequest;
use App\Services\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CategoryService $service
    ) {}

    public function all(Request $request)
    {
        $categorys = $this->service->getAll($request);
        $metadata['count'] = count($categorys);
        if(!$categorys){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($categorys, $metadata);
    }


    public function index(Request $request)
    {
        $perPage = request('per_page') ?? config('app.per_page');
        $categorys = $this->service->index($request, $perPage);
        if(!$categorys){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($categorys);
    }

    public function store(CategoryStoreRequest $request)
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
        $category = $this->service->getById($id);
        if(!$category){
            return $this->ResponseSuccess([], null, 'No Data Found!');
        }
        return $this->ResponseSuccess($category);
    }

    public function update(CategoryUpdateRequest $request, int $id)
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
