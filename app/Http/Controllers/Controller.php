<?php

namespace App\Http\Controllers;

use App\Utils\HttpResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $rule = [];
    protected $model;
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function __showAll($search = [])
    {
        try {
            $limit = request()->input('limit', 10);
            $page = request()->input('page', 1);
            $offset = ($page - 1) * $limit;
            $params = request()->input('search', '');

            $data = is_string($this->model) ? $this->model::query() : $this->model;
            $data->where(function ($query) use ($params, $search) {
                foreach ($search as $key => $value) {
                    $query->orWhere($value, 'like', '%' . $params . '%');
                }
            });
    
            $totalCount = $data->count();
    
            $builder = $data->offset($offset)->limit($limit)->get();

            $response = [
                'page' => $page,
                'limit' => $limit,
                'total_page' => ceil($totalCount / $limit),
                'total_rows' => $totalCount,
                'data' => $builder,
            ];
    
            return $response;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
    

    public function __show($query = [])
    {
        try {
            $data = $this->model::where(function ($q) use ($query) {
                foreach ($query as $key => $value) {
                    $q->where($key, $value);
                }
            })->first();
            if (!$data) {
                return HttpResponse::error('Data not found');
            }
            return HttpResponse::ok('Data found', $data);
        } catch (\Throwable $th) {
            return HttpResponse::error($th->getMessage());
        }
    }

    protected function __create($payload)
    {
        try {
            $data = $this->model::create($payload);
            return HttpResponse::ok('Data created successfully', $data);
        } catch (\Throwable $th) {
            return HttpResponse::error($th->getMessage());
        }
    }

    protected function __update($payload, $query)
    {
        try {
            $data = $this->model::where($query)->first();
            if (!$data) {
                return HttpResponse::error('Data not found');
            }
            $data->update($payload);
            return HttpResponse::ok('Data updated successfully', $data);
        } catch (\Throwable $th) {
            return HttpResponse::error($th->getMessage());
        }
    }

    protected function __destroy($query)
    {
        try {
            $data = $this->model::where($query)->first();
            if (!$data) {
                return HttpResponse::error($this->model . 'not found');
            }
            $data->delete();

            return HttpResponse::ok($data);
        } catch (\Throwable $th) {
            return HttpResponse::error($th->getMessage());
        }
    }
}