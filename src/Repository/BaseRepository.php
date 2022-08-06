<?php

namespace App\Repository;

use App\Entity\BaseEntity;
use App\Exception\ConexionDB;
use App\QueryManager\Interfaces\BDQueryManager;
use PDO;

abstract class BaseRepository
{

    public $bdQueryManager;

    private $columns = array();
    private $whereParams = array();
    private $orderParams = array();
    private $joins = array();
    private $page = 0;
    private $perPage = 0;
    private $nameSpaceEntity = 'App\\Entity\\';

    public function __construct(BDQueryManager $bdQueryManager, string $table)
    {
        $this->bdQueryManager = $bdQueryManager;
        $this->bdQueryManager::setTable($table);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return array
     */
    public function getWhereParams(): array
    {
        return $this->whereParams;
    }

    /**
     * @param array $whereParams
     * @return $this
     */
    public function setWhereParams(array $whereParams)
    {
        $this->whereParams = $whereParams;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderParams(): array
    {
        return $this->orderParams;
    }

    /**
     * @param array $orderParams
     * @return $this
     */
    public function setOrderParams(array $orderParams)
    {
        $this->orderParams = $orderParams;
        return $this;
    }

    /**
     * @return array
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     * @return $this
     */
    public function setJoins(array $joins)
    {
        $this->joins = $joins;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     * @return $this
     */
    public function setPerPage(int $perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function create(BaseEntity $entity)
    {
        try {
            $query = $this->bdQueryManager->insert($entity->getTable());
            $attributes = $entity->getAttributes();

            foreach ($attributes as $key => $value) {
                if (!isset($value)) {
                    continue;
                }
                $query->columns($key);
            }

            $statement = $this->bdQueryManager->prepare($query);

            foreach ($attributes as $key => &$value) {
                if (!isset($value)) {
                    continue;
                }
                $statement->bindParam(':' . $key, $value);
            }

            $statement->execute();
            if ($statement->rowCount() === 1) {
                $entity->setAttribute($entity->getKeyName(), $this->bdQueryManager->lastInsertId());
                return $entity->getAttributes();
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function update(BaseEntity $entity)
    {
        try {
            $query = $this->bdQueryManager->modify($entity->getTable());
            $attributes = $entity->getAttributes();

            foreach ($attributes as $key => $value) {
                if ($key == $entity->getKeyName() || !isset($value)) {
                    continue;
                }
                $query->set($key);
            }

            $query->where($entity->getKeyName() . ' = :' . $entity->getKeyName());
            $statement = $this->bdQueryManager->prepare($query);

            foreach ($attributes as $key => &$value) {
                if (!isset($value)) {
                    continue;
                }
                $statement->bindParam(':' . $key, $value);
            }

            $statement->execute();
            return $entity->getAttributes();

        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function delete(BaseEntity $entity)
    {
        try {
            /**
             * Todo
             */
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function fetchRowsByCriteria()
    {
        list($criteria) = func_get_args();

        $this->setColumns(isset($criteria['columns']) ? $criteria['columns'] : []);

        $this->setWhereParams(isset($criteria['whereParams']) ? $criteria['whereParams'] : []);

        $this->setOrderParams(isset($criteria['orderParams']) ? $criteria['orderParams'] : []);

        $this->setJoins(isset($criteria['joins']) ? $criteria['joins'] : []);

        $this->setPage(isset($criteria['page']) ? $criteria['page'] : UNDEFINED);

        $this->setPerPage(isset($criteria['perPage']) ? $criteria['perPage'] : UNDEFINED);

        return $this->buildQueryForRows();
    }

    public function fetchRowByCriteria()
    {
        list($criteria) = func_get_args();
        $this->setColumns(isset($criteria['columns']) ? $criteria['columns'] : []);
        $this->setWhereParams(isset($criteria['whereParams']) ? $criteria['whereParams'] : []);
        return $this->buildQueryForRow();
    }

    private function buildQueryForRows()
    {

        try {

            $query = $this->bdQueryManager
                ->select(is_array($this->getColumns()) && count($this->getColumns()) ? implode(', ', $this->getColumns()) : '*')
                ->callFoundRows(true)
                ->where($this->buildWhere());

            foreach ($this->getJoins() as $join) {
                $query->innerJoin($join['table'] . ' ON ' . $join['table'] . '.' . $join['tablePK'] . ' = ' . $this->bdQueryManager::getTable() . '.' . $join['tablePK']);
            }

            foreach ($this->getOrderParams() as $op) {
                $query->orderBy($op['field'] . ' ' . (isset($op['order']) ? $op['order'] : ''));
            }

            if ($this->getPerPage() != 0 && $this->getPerPage() != UNDEFINED) {
                $query->page(($this->getPage() - 1) * $this->getPerPage());
                $query->perPage($this->getPerPage());
            }

            $statement = $this->bdQueryManager->prepare($query);
            foreach ($this->getWhereParams() as $wp) {
                if (is_array($wp['value'])) {
                    continue;
                }
                $fieldClean = str_replace('.', '', $wp['field']);
                $statement->bindParam(':' . (is_array($wp["field"]) ? implode('', $wp["field"]) : $fieldClean), $wp["value"]);
            }
            $statement->execute();
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->nameSpaceEntity . 'Generic');

            $result = $this->bdQueryManager->query("SELECT FOUND_ROWS() AS foundRows");
            $result->setFetchMode(PDO::FETCH_ASSOC);
            $total = $result->fetch()["foundRows"];

            return [
                'pagination' => [
                    'totalRows' => $total,
                    'totalPages' => ceil($total / ($this->getPerPage() > 0 ? $this->getPerPage() : 1)),
                    'currentPage' => $this->getPage(),
                    'perPage' => $this->getPerPage(),
                ],
                'data' => (array)$statement->fetchAll()
            ];
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    private function buildQueryForRow()
    {

        try {

            $query = $this->bdQueryManager
                ->select(is_array($this->getColumns()) && count($this->getColumns()) ? implode(', ', $this->getColumns()) : '*')
                ->where($this->buildWhere())
                ->page(1);

            $statement = $this->bdQueryManager->prepare($query);
            foreach ($this->getWhereParams() as $wp) {
                $fieldClean = str_replace('.', '', $wp['field']);
                $statement->bindParam(':' . (is_array($wp["field"]) ? implode('', $wp["field"]) : $fieldClean), $wp["value"]);
            }
            $statement->execute();
            return $statement->fetchObject($this->nameSpaceEntity . ucfirst($this->bdQueryManager::getTable()));

        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    private function buildWhere()
    {
        $conditions = array();

        foreach ($this->whereParams as $index => $wp) {
            $statementWhere = '';
            $operatorConditional = 'where';
            $conditional = '';
            $operator = isset($wp['operator']) ? $wp['operator'] : '=';
            $fieldClean = str_replace('.', '', $wp['field']);

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
                    $statementWhere .= sprintf(' %s is null %s', $wp['field'], $index != count($this->whereParams) - 1 ? $conditional : '');
                } else {
                    if (is_array($wp['field'])) {
                        $statementWhere .= sprintf(' concat (%s) %s :%s %s', implode(", ' ', ", $wp['field']), $operator, implode('', $fieldClean), $index != count($this->whereParams) - 1 ? $conditional : '');
                    } else {
                        $statementWhere .= sprintf(' %s %s :%s %s', $wp['field'], $operator, $fieldClean, $index != count($this->whereParams) - 1 ? $conditional : '');
                    }
                }
            } else if ($operatorConditional == 'whereIn') {
                $statementWhere .= sprintf(' %s in %s %s', $wp['field'], is_array($wp['value']) ? '(' . implode(',', $wp['value']) . ')' : $wp['value'], $index != count($this->whereParams) - 1 ? $conditional : '');
            }
            $conditions[] = $statementWhere;
        }

        return $conditions;
    }

}
