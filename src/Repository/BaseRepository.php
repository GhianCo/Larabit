<?php

namespace App\Repository;

use App\Exception\ConexionDB;
use Illuminate\Database\Eloquent\Model;
use App\Service\BaseService;

abstract class BaseRepository
{
    public function create(Model $model)
    {
        try {
            $model->save();
            return $model->refresh();
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function update(Model $model)
    {
        try {
            $model->exists = true;
            $model->update();
            return $model;
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function delete(Model $model)
    {
        try {
            $model->exists = true;
            return $model->delete();
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    protected function getResultsWithPagination($model, $whereParams = array(), $page = 0, $perPage = BaseService::DEFAULT_PER_PAGE_PAGINATION)
    {
        $resultQuery = $this->getResultByPage($model, $whereParams, ($page - 1) * $perPage, $perPage);

        return [
            'pagination' => [
                'totalRows' => $resultQuery['total'],
                'totalPages' => ceil($resultQuery['total'] / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage,
            ],
            'data' => $resultQuery['data'],
        ];
    }

    protected function getResultByPage(Model $model, $whereParams = array(), $page = 0, $perPage = 2000)
    {

        try {
            global $pdo;

            $modelSql = $model::select($model::raw('SQL_CALC_FOUND_ROWS *'));

            if (count($whereParams) > 0) {
                foreach ($whereParams as $wp) {
                    $operatorConditional = 'where';
                    $conditional = '';
                    if (isset($wp['conditional'])) {
                        if ($wp['conditional'] == 'whereIn' && is_array($wp['value'])) {
                            $operatorConditional = 'whereIn';
                        } else {
                            if ($wp['conditional'] == '' || $wp['conditional'] == null) {
                                $conditional = 'and';
                            } else {
                                if (strtolower(trim($wp['conditional'], ' ')) == 'and') {
                                    $conditional = 'and';
                                } else if (strtolower(trim($wp['conditional'], ' ')) == 'or') {
                                    $conditional = 'or';
                                } else {
                                    $conditional = 'and';
                                }
                            }
                        }
                    } else {
                        $conditional = 'and';
                    }
                    if ($operatorConditional == 'where') {
                        if ($wp['value'] == null) {
                            $modelSql->whereNull($wp['field']);
                        } else {
                            $modelSql->where($wp['field'], $wp['operator'], $wp['value'], $conditional);
                        }
                    } else if ($operatorConditional == 'whereIn') {
                        $modelSql->whereIn($wp['field'], $wp['value']);
                    }
                }
            }

            if ($perPage != 0 && $perPage != UNDEFINED) {
                $modelSql->take($perPage)->skip($page);
            }

            $data = $modelSql->get()->toArray();

            $total = $pdo::selectOne('SELECT FOUND_ROWS() AS totalCount')->totalCount;

            return [
                'data' => (array)$data,
                'total' => $total
            ];
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

}
