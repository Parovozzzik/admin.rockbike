<?php

namespace App\Models;

use App\Settings\DB\Database;
use App\Settings\Exceptions\DatabaseException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use App\Models\Entities\Entity;
use Spot\Entity\Collection;
use Spot\EntityInterface;
use Spot\Mapper;

/**
 * Class Model
 * @package App\Models
 */
class Model extends Mapper
{
    const
        PAGES_PER_PAGER = 10,
        ITEMS_PER_PAGE = 50,
        SIGN_GOODS_PER_PAGE = 20;

    const
        TYPE_PAGE = 0,
        TYPE_OFFSET = 1;

    const WAIT_BEFORE_NEXT_CALL_SAFE = 15;

    /** @var  \Doctrine\DBAL\Driver\Statement */
    private $updateStatement;
    /** @var  \Doctrine\DBAL\Driver\Statement */
    private $insertStatement;

    /**
     * Overrides method connection() in \Spot\Mapper. Catches \Exception and changes it into DatabaseException.
     *
     * @param null $connectionName
     * @return Connection
     * @throws DatabaseException
     */
    public function connection($connectionName = null): Connection
    {
        try {
            return parent::connection($connectionName);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage());
        }
    }

    /**
     * @param $entityClass
     * @param null $connectionName
     * @return Mapper
     * @throws DatabaseException
     */
    public static function getModel($entityClass, $connectionName = null)
    {
        return Database::db($connectionName)->mapper($entityClass);
    }

    /**
     * @param $page
     * @param null $itemsPerPage
     * @param int $type
     * @return string
     */
    public function buildLimitClause($page, $itemsPerPage = null, $type = self::TYPE_PAGE)
    {
        $itemsPerPage = (is_numeric($itemsPerPage) && $itemsPerPage > 1) ? $itemsPerPage : static::ITEMS_PER_PAGE;

        if ($type === self::TYPE_PAGE) {
            $page = (is_numeric($page) && $page > 1) ? $page : 1;

            $offset = $itemsPerPage * ($page - 1);
        } else {
            $offset = $page;
        }

        return sprintf('LIMIT %d, %d', $offset, $itemsPerPage);
    }

    /**
     * @param $page
     * @param $itemsCount
     * @param null $itemsPerPage
     * @return array
     */
    public static function buildPager($page, $itemsCount, $itemsPerPage = null)
    {
        $page = (is_numeric($page) && $page > 1) ? $page : 1;
        $itemsPerPage = (is_numeric($itemsPerPage) && $itemsPerPage > 1) ? $itemsPerPage : static::ITEMS_PER_PAGE;

        $pageCount = ceil($itemsCount / $itemsPerPage);

        $row = array();
        $row['total'] = $itemsCount;
        $row['items_per_page'] = $itemsPerPage;
        $row['page_count'] = $pageCount;
        $row['current_page'] = $page;
        $row['first_page'] = 1;
        $row['previous_page'] = $page - ($page == 1 ? 0 : 1);
        $row['next_page'] = $page + ($page == $pageCount ? 0 : 1);
        $row['last_page'] = $pageCount;

        $pagesPerPager = self::PAGES_PER_PAGER;
        if ($pagesPerPager < $pageCount) {
            $startPage = $page - (int)($pagesPerPager / 2);
            if ($startPage < 1) {
                $startPage = 1;
            }
            if ($page + (int)($pagesPerPager / 2) > $pageCount) {
                $startPage = $startPage - ($page + (int)(($pagesPerPager) / 2) - $pageCount) + ($pagesPerPager % 2 == 1 ? 0 : 1);
            }
            for ($i = $startPage; $i < $startPage + $pagesPerPager; $i++) {
                $row["page"][$i] = $i;
            }
        } else {
            for ($i = 1; $i <= $pageCount; $i++) {
                $row["page"][$i] = $i;
            }
        }
        return $row;
    }

    /**
     * prepare variable to int[]
     * @param mixed $v
     * @return int[]
     */
    protected function prepareIntArray($v)
    {
        if (!is_array($v)) {
            $v = [$v];
        }
        if (count($v) < 1) {
            $v = [0];
        }
        return array_map(function ($e) {
            return (int)$e;
        }, $v);
    }

    /**
     * prepare variable to string[]
     * @param mixed $v
     * @return string[]
     */
    protected function prepareStringArray($v): array
    {
        if (!is_array($v)) {
            $v = [$v];
        }
        if (count($v) < 1) {
            $v = [''];
        }
        return array_map('trim', $v);
    }

    /**
     * Prepare Int array from Ds/Map
     * @param Map $v
     * @return array
     */
    protected function prepareIntArrayFromMap(Map $v): array
    {
        if (count($v) < 1) {
            $v = [0];
        }
        $result = [];
        foreach ($v as $key => $info) {
            array_push($result, (int)trim($info));
        }
        return $result;
    }

    /**
     * @param array $filters
     * @return int
     */
    public function tableRowsCount(array $filters = []): int
    {
        return $this->select()->where($filters)->count();
    }

    /**
     * execute query in connection
     * @param string $q
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     * @throws \Exception
     */
    public function connectionQuery($q)
    {
        $this->connection()->exec($q);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool|Collection
     * @throws DatabaseException
     * @throws \Exception
     */
    public function query($sql, array $params = [])
    {
        try {
            return parent::query($sql, $params);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'gone away') !== false) {
                $this->connection()->close();
                sleep(self::WAIT_BEFORE_NEXT_CALL_SAFE);
                $this->connection()->ping();
                return parent::query($sql, $params);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param mixed $entity
     * @param array $options
     * @return bool|int|mixed|string
     * @throws \Spot\Exception
     */
    public function insert($entity, array $options = [])
    {
        return parent::insert($entity, $options);
    }

    /**
     * @param object|EntityInterface $entity
     * @param array $options
     * @return bool|int
     * @throws \Spot\Exception
     */
    public function update($entity, array $options = [])
    {
        return parent::update($entity, $options);
    }

    /**
     * @param array $conditions
     * @return bool|\Doctrine\DBAL\Driver\Statement|int
     */
    public function delete($conditions = [])
    {
        return parent::delete($conditions);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function exec($sql, array $params = [])
    {
        return parent::exec($sql, $params);
    }

    /**
     * @param string $fields
     * @return \Spot\Query
     */
    public function select($fields = '*')
    {
        return parent::select($fields);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        if (substr($name, -4) === 'Safe') {
            $safeMethodName = substr($name, 0, mb_strlen($name) - 4);
            if (method_exists($this, $safeMethodName)) {
                return $this->_getSafeResult($safeMethodName, $arguments);
            }
        }
        throw new \Exception('Method `' . $name . '` not found in ' . get_class($this));
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    private function _getSafeResult($name, $arguments)
    {
        try {
            return call_user_func_array([$this, $name], $arguments);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'gone away') !== false) {
                $this->connection()->close();
                sleep(self::WAIT_BEFORE_NEXT_CALL_SAFE);
                $this->connection()->ping();
                return call_user_func_array([$this, $name], $arguments);
            } else {
                throw $e;
            }
        }
    }

    /**
     * fastest way to check if row exists
     *
     * @param $conditions
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function isRowExists($conditions)
    {
        return (int)$this->connection()->executeQuery(
                $this->select('COUNT(1)')->where($conditions)->limit(1)->toSql(),
                array_values($conditions)
            )->fetchColumn() > 0;
    }

    /**
     * return string `?, ?, ?, ... ?`
     *
     * @param array $list
     * @return string
     */
    public function getListUnnamedParams($list)
    {
        return implode(',', array_fill(0, count($list), '?'));
    }

    /**
     * Возвращает скомпилированый запрос на обновление сущности модели по Ключу
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Exception
     */
    public function getUpdateStatement()
    {
        if (null === $this->updateStatement) {
            $pk = $this->primaryKeyField();

            $fields = $this->fields();
            unset($fields[$pk]);
            $fields = array_keys($fields);

            $sets = array_map(
                function ($field) {
                    return sprintf('`%1$s` = COALESCE(?, `%1$s`)', $field);
                },
                $fields
            );

            $query = sprintf('UPDATE %s SET %s WHERE %s = ?', $this->table(), implode(',', $sets), $pk);

            $this->updateStatement = $this->connection()->prepare($query);
        }

        return $this->updateStatement;
    }

    /**
     * Возвращает скомпилированный запрос на создание сущности модели
     *
     * @param string $onDuplicateKeyUpdateQuery
     *
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Exception
     */
    public function getInsertStatement(string $onDuplicateKeyUpdateQuery = ''): \Doctrine\DBAL\Driver\Statement
    {
        if (null === $this->insertStatement) {
            $pk = $this->primaryKeyField();

            $fields = $this->fields();
            unset($fields[$pk]);
            $fields = array_keys($fields);

            $query = sprintf(
                'INSERT INTO %s (%s) VALUES (%s) %s;',
                $this->table(), '`' . implode('`,`', $fields) . '`',
                $this->getListUnnamedParams($fields),
                $onDuplicateKeyUpdateQuery
            );

            $this->insertStatement = $this->connection()->prepare($query);
        }

        return $this->insertStatement;
    }

    /**
     * Метод для быстрой записи данных в базу
     *
     * @param array $data Данные для записи в базу
     * @param array $onDuplicateKeyUpdate
     *
     * @return int|false
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Spot\Exception
     * @throws \Exception
     */
    public function quickUpsert(array $data, array $onDuplicateKeyUpdate = [])
    {
        $pk = $this->primaryKeyField();

        $fields = array_keys($this->fields());

        $pkId = (isset($data[$pk]) && $data[$pk] > 0) ? (int)$data[$pk] : 0;

        /* update */
        if ($pkId > 0) {
            $stm = $this->getUpdateStatement();

            /* set all fields */
            $data = array_reduce(
                $fields,
                function ($carry, $field) use ($data) {
                    $carry[$field] = array_key_exists($field, $data) ? $data[$field] : null;
                    return $carry;
                },
                []
            );
        } /* insert */
        else {
            [$onDuplicateKeyUpdateQuery, $onDuplicateKeyUpdateData] = $this->getOnDuplicateKeyUpdate($onDuplicateKeyUpdate);
            $stm = $this->getInsertStatement($onDuplicateKeyUpdateQuery);

            $defaultData = $this->entityManager()->fieldDefaultValues();

            /* set default fields */
            array_walk(
                $defaultData,
                function (&$item, $key, $newData) {
                    $item = array_key_exists($key, $newData) ? $newData[$key] : $item;
                },
                $data
            );

            $data = $defaultData;
        }

        /* convert to database values */
        $data = $this->convertToDatabaseValues($this->entityName, $data);

        unset($data[$pk]);

        /* get all params */
        $params = array_values($data);

        if (isset($onDuplicateKeyUpdateData)) {
            $params = array_merge($params, $onDuplicateKeyUpdateData);
        }

        if ($pkId) {
            $params[] = $pkId;
        }

        /* execute and get primary key */
        if ($stm->execute($params)) {
            $data[$pk] = isset($data[$pk]) ? $data[$pk] : $this->connection()->executeQuery('SELECT LAST_INSERT_ID();')->fetchColumn();
        } else {
            return false;
        }

        return $data[$pk];
    }

    /**
     * check if current connection has readonly access
     *
     * @param int $id
     * @param string $field
     * @throws \Exception
     */
    public function checkReadonlyConnection(int $id, string $field): void
    {
        $e = $this->get($id);
        $e->{$field} = $e->{$field} . '1';
        $this->save($e);
    }

    /**
     * Get array of default model entity values
     *
     * @return array
     */
    public function getEntityDefaultValues(): array
    {
        return $this->get(false)->notEmptyData();
    }

    /**
     * Get a new entity object, set given data on it and fill defaults
     *
     * @param array $data array of key/values to set on new Entity instance
     * @return Entity Instance of $entityClass with $data set on it
     */
    public function build(array $data): Entity
    {
        /** @var Entity $e */
        $e = parent::build($data + $this->getEntityDefaultValues());
        return $e;
    }

    /**
     * getOnDuplicateKeyUpdate
     *
     * @param array $data
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    private function getOnDuplicateKeyUpdate(array $data = []): array
    {
        $onUpdateSet = [];
        $platform = $this->connection()->getDatabasePlatform();
        $fields = $this->fields();
        foreach ($fields as $field => $fieldData) {
            if (array_key_exists($field, $data)) {
                $onUpdateSet[$field] = Type::getType($fieldData['type'])->convertToDatabaseValue($data[$field],
                    $platform);
            }
        }
        if ([] !== $onUpdateSet) {
            $set = [];
            foreach ($onUpdateSet as $field => $value) {
                $set[] = sprintf('`%s` = ?', $field);
            }
            return [' ON DUPLICATE KEY UPDATE ' . implode(',', $set), array_values($onUpdateSet)];
        }

        return ['', []];
    }

    /**
     * Method create/update many entities. For insert method create
     * all rows by one query, for update method use cycle
     *
     * @param Entity ...$entities
     *
     * @return int The number of affected rows
     * @throws \Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function saveMany(Entity ...$entities): int
    {
        $insertEntities = [];
        $updateEntities = [];
        $affectedRows = 0;

        foreach ($entities as $entity) {
            if (empty($entity->primaryKey()) && $entity->isNew()) {
                $insertEntities[] = $entity;
            } else {
                $updateEntities[] = $entity;
            }
        }

        if (count($insertEntities) > 0) {
            $affectedRows += $this->insertMany(...$insertEntities);
        }

        if (count($updateEntities) > 0) {
            $affectedRows += $this->updateMany(...$updateEntities);
        }

        return $affectedRows;
    }

    /**
     * @param Entity ...$entities
     *
     * @return int The number of affected rows
     * @throws \Exception
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertMany(Entity ...$entities): int
    {
        $insertValues = [];

        foreach ($entities as $entity) {
            $insertValues[] = $this->prepareEntityToInsert($entity);
        }

        if (count($insertValues) > 0) {
            $params = [];

            $questionMarks = array_map(function ($values) {
                return $this->getListUnnamedParams($values);
            }, $insertValues);
            array_walk_recursive($insertValues, function ($item) use (&$params) {
                $params[] = $item;
            });

            $pk = $this->primaryKeyField();
            $fields = $this->fields();
            if (!isset($insertValues[0][$pk])) {
                unset($fields[$pk]);
            }
            $fields = array_keys($fields);

            $query = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->table(),
                implode(',', $fields),
                implode('),(', $questionMarks)
            );

            return $this->connection()->executeUpdate($query, $params);
        }

        return 0;
    }

    /**
     * @param Entity ...$entities
     *
     * @return int The number of affected rows
     * @throws \Exception
     */
    public function updateMany(Entity ...$entities): int
    {
        $totalUpdates = 0;
        foreach ($entities as $entity) {
            $updateResult = $this->update($entity);
            if ($updateResult === true) {
                $totalUpdates++;
            } else if (is_int($updateResult)) {
                $totalUpdates += $updateResult;
            }
        }

        return $totalUpdates;
    }

    /**
     * Validate entity. Get result values of entity in array
     *
     * @param Entity $entity
     *
     * @return array|null
     */
    protected function prepareEntityToInsert(Entity $entity): ?array
    {
        $entityName = get_class($entity);

        // Run validation unless disabled via options
        if (!$this->validate($entity)) {
            return null;
        }

        // Ensure there is actually data to update
        $data = $entity->data(null, true, false);
        if (count($data) > 0) {
            $pkField = $this->primaryKeyField();

            // Save only known, defined fields
            $entityFields = $this->fields();
            $data = array_intersect_key($data, $entityFields);

            // Do type conversion
            $data = $this->convertToDatabaseValues($entityName, $data);

            // Don't pass NULL for "serial" columns (causes issues with PostgreSQL + others)
            if (array_key_exists($pkField, $data) && empty($data[$pkField])) {
                unset($data[$pkField]);
            }
        }

        return $data;
    }

    /**
     * @param EntityInterface $entity
     * @param array $options
     * @param int $retryCount
     * @return bool|int|mixed|string|null
     * @throws \Exception
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save(EntityInterface $entity, array $options = [], int $retryCount = 1)
    {
        if ($retryCount === 1) {
            return parent::save($entity, $options);
        }

        $result = null;
        $result = parent::save($entity, $options);
        return $result;
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     */
    public function getList(int $page = 0, int $limit = 25, array $filters = []): array
    {
        $page = $page <= 0 ? 0 : $page;
        $offset = $limit * ($page <= 0 ? 0 : $page - 1);
        $data = $this->select()->where($filters)->limit($limit)->offset($offset);
        $pager = self::buildPager($page, $this->tableRowsCount($filters), $limit);

        return [
            'data' => $data->toArray(),
            'pager' => $pager
        ];
    }
}