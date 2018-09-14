<?php

namespace libs\QueryBuilder\src;

use \PDO;

use libs\QueryBuilder\src\exception\QueryBuilderException;
use libs\QueryBuilder\src\traits\Validators;

class QueryBuilder
{

    /**
     * Validation methods.
     */
    use Validators;

    /**
     * Database types QueryBuilder supports.
     *
     * @var array $availableDatabases
     */
    private $availableDatabases = ['mysql', 'pgsql'];

    /**
     * Condition operators QueryBuilder supports.
     *
     * @var array $availableConditionOperators
     */
    private $availableConditionOperators = [
        '=',
        '!=',
        '>',
        '<',
        '<=',
        '>=',
        'LIKE',
        'NOT LIKE',
    ];

    /**
     * Order types QueryBuilder supports.
     *
     * @var array $availableOrderTypes
     */
    private $availableOrderTypes = ['ASC', 'DESC'];

    /**
     * Distinct (true/false).
     *
     * @var bool $distinct
     */
    private $distinct;

    /**
     * Table name.
     *
     * @var $table
     */
    private $table;

    /**
     * Array of field names.
     *
     * @var array $fields
     */
    private $fields;

    /**
     * Array of values arrays.
     *
     * @var array $values
     */
    private $values;

    /**
     * Integer LIMIT value.
     *
     * @var int $limit
     */
    private $limit;

    /**
     * Where clauses query string.
     *
     * @var string $whereQuery
     */
    private $whereQuery = '';

    /**
     * Where clauses execute params array.
     *
     * @var array $whereExecuteParams
     */
    private $whereExecuteParams = [];

    /**
     * Having clauses query string.
     *
     * @var string $havingQuery
     */
    private $havingQuery = '';

    /**
     * Having clauses execute params array.
     *
     * @var array $havingExecuteParams
     */
    private $havingExecuteParams = [];

    /**
     * GROUP BY query string.
     *
     * @var string $groupByQuery
     */
    private $groupByQuery = '';

    /**
     * ORDER BY query string.
     *
     * @var string $orderByQuery
     */
    private $orderByQuery = '';

    /**
     * JOIN query string.
     *
     * @var string $orderByQuery
     */
    private $joinQuery = '';

    /**
     * Main query string.
     *
     * @var string $query
     */
    private $query = '';

    /**
     * Main execute params array.
     *
     * @var array $executeParams
     */
    private $executeParams = [];

    /**
     * Query type string.
     *
     * @var string $queryType
     */
    private $queryType;

    /**
     * Current connection database type.
     *
     * @var $dbType
     */
    private $dbType;

    /**
     * PDO instance which is used to execute QueryBuilder statements.
     *
     * @var PDO $pdo
     */
    private $pdo;

    /**
     * Reset QueryBuilder properties.
     *
     * @return void
     */
    private function resetProperties()
    {
        $this->distinct = null;
        $this->table = null;
        $this->fields = null;
        $this->values = null;
        $this->limit = null;

        $this->whereQuery = '';
        $this->whereExecuteParams = [];
        $this->havingQuery = '';
        $this->havingExecuteParams = [];
        $this->groupByQuery = '';
        $this->orderByQuery = '';
        $this->joinQuery = '';

        $this->query = '';
        $this->executeParams = [];
        $this->queryType = null;
    }

    /**
     * Throw QueryBuilder exception.
     *
     * @param $message
     *
     * @throws Exception
     */
    private function throwException($message)
    {
        $this->resetProperties();

        throw new QueryBuilderException($message);
    }

    /**
     * Execute statement.
     *
     * @param $query
     * @param $executeParams
     *
     * @return bool|PDOStatement
     */

    private function executeStatement($query, $executeParams)
    {
        try
        {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($executeParams);
        } catch (PDOException $exception)
        {
            $this->resetProperties();

            throw new PDOException($exception->getMessage());
        }

        return $stmt;
    }

    /**
     * Generate $joinQuery string.
     *
     * @param       $joinTypeQuery
     * @param       $table
     * @param array $fields
     */
    private function setJoinQuery($joinTypeQuery, $table, array $fields = [])
    {
        if (count($fields) > 0)
        {
            $this->joinQuery .= " $joinTypeQuery $table ON {$fields[0]} = {$fields[1]}";
        }
        else
        {
            $this->joinQuery .= " $joinTypeQuery $table";
        }

    }

    /**
     * QueryBuilder constructor.
     *
     * @param        $dbType
     * @param        $host
     * @param        $port
     * @param        $database
     * @param        $user
     * @param        $password
     * @param string $charset
     *
     * @throws Exception
     */
    public function __construct(
        $dbType,
        $host,
        $port,
        $database,
        $user,
        $password,
        $charset = 'utf8mb4'
    ) {
        $this->validateExistsInArray(
            $dbType,
            $this->availableDatabases,
            'Unavailable database type.'
        );

        $this->dbType = $dbType;

        $dsn = null;

        if ('mysql' === $dbType)
        {
            $dsn =
                "$dbType:host=$host;port=$port;dbname=$database;charset=$charset";
        }

        if ('pgsql' === $dbType)
        {
            $dsn = "$dbType:host=$host;port=$port;dbname=$database";
        }

        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,

        ];

        $this->pdo = new PDO(
            $dsn,
            $user,
            $password,
            $opt
        );
    }

    /**
     * Set $table property.
     *
     * @param $tableName
     *
     * @return $this
     * @throws Exception
     */
    public function table($tableName)
    {
        $this->table = $tableName;

        return $this;
    }

    /**
     * Generate INNER JOIN $joinQuery.
     *
     * @param       $table
     * @param array $fields
     *
     * @return $this
     */
    public function join($table, array $fields)
    {
        $this->setJoinQuery(
            'INNER JOIN',
            $table,
            $fields
        );

        return $this;
    }

    /**
     * Generate LEFT OUTER JOIN $joinQuery.
     *
     * @param       $table
     * @param array $fields
     *
     * @return $this
     */
    public function leftJoin($table, array $fields)
    {
        $this->setJoinQuery(
            'LEFT OUTER JOIN',
            $table,
            $fields
        );

        return $this;
    }

    /**
     * Generate RIGHT OUTER JOIN $joinQuery.
     *
     * @param       $table
     * @param array $fields
     *
     * @return $this
     */
    public function rightJoin($table, array $fields)
    {
        $this->setJoinQuery(
            'RIGHT OUTER JOIN',
            $table,
            $fields
        );

        return $this;
    }

    /**
     * Generate CROSS JOIN $joinQuery.
     *
     * @param $table
     *
     * @return $this
     */
    public function crossJoin($table)
    {
        $this->setJoinQuery(
            'CROSS JOIN',
            $table
        );

        return $this;
    }

    /**
     * Generate NATURAL JOIN $joinQuery.
     *
     * @param $table
     *
     * @return $this
     */
    public function naturalJoin($table)
    {
        $this->setJoinQuery(
            'NATURAL JOIN',
            $table
        );

        return $this;
    }

    /**
     * Set $fields property.
     *
     * @param array $fields
     *
     * @return $this
     * @throws Exception
     */
    public function fields(array $fields)
    {
        $this->validateSequentialArray(
            $fields,
            'Method \'fields\' requires sequential array as argument.'
        );

        $this->fields = $fields;

        return $this;
    }

    /**
     * Set $values property.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function values(...$args)
    {
        $this->validateNotNull(
            $this->fields,
            "Method 'values' requires method 'fields' be called before it."
        );

        foreach ($args as $value)
        {
            $this->validateSequentialArray(
                $value,
                'Method \'values\' requires sequential arrays as arguments.'
            );

            $this->validateEqualLengthOfArrays(
                $this->fields,
                $value,
                "Values and fields length must be equal."
            );
        }

        $this->values = $args;

        return $this;
    }

    /**
     * Add WHERE into $whereQuery and params into $whereExecutionParams.
     *
     * @param array $where
     *
     * @return $this
     * @throws Exception
     */
    public function where(array $where)
    {
        $this->validateSequentialArray(
            $where,
            'Method \'where\' requires sequential array as argument.'
        );

        $this->validateExistsInArray(
            $where[1],
            $this->availableConditionOperators,
            'Unavailable condition operator.'
        );

        $this->whereQuery .= "{$where[0]} {$where[1]} ?";
        $this->whereExecuteParams[] = $where[2];

        return $this;
    }

    /**
     * Add AND WHERE into $whereQuery and params into $whereExecutionParams.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function andWhere($args)
    {
        if (is_callable($args))
        {
            $this->whereQuery .= ' AND (';

            $args($this);

            $this->whereQuery .= ')';

            return $this;
        }

        $args = func_get_args();

        foreach ($args as $where)
        {
            $this->validateSequentialArray(
                $where,
                'Method \'andWhere\' requires sequential arrays as arguments.'
            );

            $this->validateExistsInArray(
                $where[1],
                $this->availableConditionOperators,
                'Unavailable condition operator.'
            );

            $this->whereQuery .= " AND {$where[0]} {$where[1]} ?";
            $this->whereExecuteParams[] = $where[2];
        }

        return $this;
    }

    /**
     * Add OR WHERE into $whereQuery and params into $whereExecutionParams.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function orWhere($args)
    {
        if (is_callable($args))
        {
            $this->whereQuery .= ' OR (';

            $args($this);

            $this->whereQuery .= ')';

            return $this;
        }

        $args = func_get_args();

        foreach ($args as $where)
        {
            $this->validateSequentialArray(
                $where,
                'Method \'orWhere\' requires sequential arrays as arguments.'
            );

            $this->validateExistsInArray(
                $where[1],
                $this->availableConditionOperators,
                'Unavailable condition operator.'
            );

            $this->whereQuery .= " OR {$where[0]} {$where[1]} ?";
            $this->whereExecuteParams[] = $where[2];
        }

        return $this;
    }

    /**
     * Generate $groupByQuery query string.
     *
     * @param array $groupBy
     *
     * @return $this
     * @throws Exception
     */
    public function groupBy(array $groupBy)
    {
        $this->validateSequentialArray(
            $groupBy,
            'Method \'groupBy\' requires sequential array as argument.'
        );

        $this->groupByQuery .= ' GROUP BY ';

        $this->groupByQuery .= implode(
            ', ',
            $groupBy
        );

        return $this;
    }

    /**
     * Add HAVING into $havingQuery and params into $havingExecutionParams.
     *
     * @param array $having
     *
     * @return $this
     * @throws Exception
     */
    public function having(array $having)
    {
        $this->validateNotEmptyString(
            $this->groupByQuery,
            "Method 'having' requires method 'groupBy' be called before it."
        );

        $this->validateSequentialArray(
            $having,
            "Method 'having' requires sequential array as argument."
        );

        $this->validateExistsInArray(
            $having[1],
            $this->availableConditionOperators,
            'Unavailable condition operator.'
        );

        $this->havingQuery .= "{$having[0]} {$having[1]} ?";
        $this->havingExecuteParams[] = $having[2];

        return $this;
    }

    /**
     * Add AND HAVING into $havingQuery and params into $havingExecutionParams.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function andHaving($args)
    {
        if (is_callable($args))
        {
            $this->havingQuery .= ' AND (';

            $args($this);

            $this->havingQuery .= ')';

            return $this;
        }

        $args = func_get_args();

        foreach ($args as $having)
        {
            $this->validateSequentialArray(
                $having,
                'Method \'andHaving\' requires sequential arrays as arguments.'
            );

            $this->validateExistsInArray(
                $having[1],
                $this->availableConditionOperators,
                'Unavailable condition operator.'
            );

            $this->havingQuery .= " AND {$having[0]} {$having[1]} ?";
            $this->havingExecuteParams[] = $having[2];
        }

        return $this;
    }

    /**
     * Add OR HAVING into $havingQuery and params into $havingExecutionParams.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function orHaving($args)
    {
        if (is_callable($args))
        {
            $this->havingQuery .= ' OR (';

            $args($this);

            $this->havingQuery .= ')';

            return $this;
        }

        $args = func_get_args();

        foreach ($args as $having)
        {
            $this->validateSequentialArray(
                $having,
                'Method \'orHaving\' requires sequential arrays as arguments.'
            );

            $this->validateExistsInArray(
                $having[1],
                $this->availableConditionOperators,
                'Unavailable condition operator.'
            );

            $this->havingQuery .= " OR {$having[0]} {$having[1]} ?";
            $this->havingExecuteParams[] = $having[2];
        }

        return $this;
    }

    /**
     * Generate $orderByQuery query string.
     *
     * @param mixed ...$args
     *
     * @return $this
     * @throws Exception
     */
    public function orderBy(...$args)
    {
        $this->orderByQuery .= ' ORDER BY ';

        foreach ($args as $orderBy)
        {
            $this->validateSequentialArray(
                $orderBy,
                'Method \'orderBy\' requires sequential arrays as arguments.'
            );

            $this->validateArrayLengthEqualTo(
                $orderBy,
                2,
                "Method 'orderBy' requires arrays (with length 2) as arguments."
            );

            $this->validateExistsInArray(
                $orderBy[1],
                $this->availableOrderTypes,
                'Unavailable order type.'
            );

            $this->orderByQuery .= "{$orderBy[0]} {$orderBy[1]}, ";
        }

        $this->orderByQuery = rtrim(
            $this->orderByQuery,
            ', '
        );

        return $this;
    }

    /**
     * Set $limit property.
     *
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set $distinct property.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * Generate select $query string.
     *
     * @return $this
     * @throws Exception
     */
    public function select()
    {
        $this->validateNotNull(
            $this->table,
            "Method 'select' requires method 'table' be called before it."
        );

        $this->validateNotNull(
            $this->fields,
            "Method 'select' requires method 'fields' be called before it."
        );

        $this->validateNull(
            $this->queryType,
            "Method 'select' not allows methods 'update/delete/insert' be called before it."
        );

        $this->queryType = 'select';

        $this->query .= true === $this->distinct ? "SELECT DISTINCT "
            : "SELECT ";

        $this->query .= implode(
            ', ',
            $this->fields
        );

        $this->query .= " FROM {$this->table}";

        if (strlen($this->joinQuery) > 0)
        {
            $this->query .= $this->joinQuery;
        }

        if (
            strlen($this->whereQuery) > 0 &&
            count($this->whereExecuteParams) > 0
        )
        {
            $this->query .= ' WHERE ';
            $this->query .= $this->whereQuery;
            $this->executeParams = array_merge(
                $this->executeParams,
                $this->whereExecuteParams
            );
        }

        if (strlen($this->groupByQuery) > 0)
        {
            $this->query .= $this->groupByQuery;
        }

        if (
            strlen($this->havingQuery) > 0 &&
            count($this->havingExecuteParams) > 0
        )
        {
            $this->query .= ' HAVING ';
            $this->query .= $this->havingQuery;
            $this->executeParams = array_merge(
                $this->executeParams,
                $this->havingExecuteParams
            );
        }

        if (strlen($this->orderByQuery) > 0)
        {
            $this->query .= $this->orderByQuery;
        }

        if (null !== $this->limit)
        {
            $this->query .= " LIMIT {$this->limit}";
        }

        return $this;
    }

    /**
     * Generate insert $query string.
     *
     * @return $this
     * @throws Exception
     */
    public function insert()
    {
        $this->validateNotNull(
            $this->table,
            "Method 'insert' requires method 'table' be called before it."
        );

        $this->validateNotNull(
            $this->fields,
            "Method 'insert' requires method 'fields' be called before it."
        );

        $this->validateNotNull(
            $this->values,
            "Method 'insert' requires method 'values' be called before it."
        );

        $this->validateNull(
            $this->queryType,
            "Method 'insert' not allows methods 'update/delete/select' be called before it."
        );

        $this->queryType = 'insert';

        $this->query .= "INSERT INTO {$this->table} (";

        $this->query .= implode(
            ', ',
            $this->fields
        );

        $this->query .= ") VALUES";

        foreach ($this->values as $value)
        {
            $this->query .= ' (';

            $this->query .= str_repeat(
                '?, ',
                count($value)
            );

            $this->executeParams = array_merge(
                $this->executeParams,
                $value
            );

            $this->query = rtrim(
                $this->query,
                ', '
            );

            $this->query .= '),';
        }

        $this->query = rtrim(
            $this->query,
            ','
        );

        return $this;
    }

    /**
     * Generate delete $query string.
     *
     * @return $this
     * @throws Exception
     */
    public function delete()
    {
        $this->validateNotNull(
            $this->table,
            "Method 'delete' requires method 'table' be called before it."
        );

        $this->validateNotEmptyString(
            $this->whereQuery,
            "Method 'delete' requires method 'where' be called before it."
        );

        $this->validateNull(
            $this->queryType,
            "Method 'delete' not allows methods 'update/insert/select' be called before it."
        );

        $this->queryType = 'delete';

        $this->query .= "DELETE FROM {$this->table}";

        if (
            strlen($this->whereQuery) > 0 &&
            count($this->whereExecuteParams) > 0
        )
        {
            $this->query .= ' WHERE ';
            $this->query .= $this->whereQuery;
            $this->executeParams = array_merge(
                $this->executeParams,
                $this->whereExecuteParams
            );
        }

        if (null !== $this->limit && $this->dbType === 'mysql')
        {
            $this->query .= " LIMIT {$this->limit}";
        }

        return $this;
    }

    /**
     * Generate update $query string.
     *
     * @return $this
     * @throws Exception
     */
    public function update()
    {
        $this->validateNotNull(
            $this->table,
            "Method 'update' requires method 'table' be called before it."
        );

        $this->validateNotNull(
            $this->fields,
            "Method 'update' requires method 'fields' be called before it."
        );

        $this->validateNotNull(
            $this->values,
            "Method 'update' requires method 'values' be called before it."
        );

        $this->validateArrayLengthLessOrEqual(
            $this->values,
            1,
            "Method 'update' requires only one set of values."
        );

        $this->validateNotEmptyString(
            $this->whereQuery,
            "Method 'update' requires method 'where' be called before it."
        );

        $this->validateNull(
            $this->queryType,
            "Method 'update' not allows methods 'delete/insert/select' be called before it."
        );

        $this->queryType = 'update';

        $this->query .= "UPDATE {$this->table} SET ";

        $this->query .= implode(
            '=?, ',
            $this->fields
        );

        $this->query .= '=?';

        foreach ($this->values as $value)
        {
            foreach ($value as $v)
            {
                $this->executeParams[] = $v;
            }
        }

        if (
            strlen($this->whereQuery) > 0 &&
            count($this->whereExecuteParams) > 0
        )
        {
            $this->query .= ' WHERE ';
            $this->query .= $this->whereQuery;
            $this->executeParams = array_merge(
                $this->executeParams,
                $this->whereExecuteParams
            );
        }

        if (null !== $this->limit && $this->dbType === 'mysql')
        {
            $this->query .= " LIMIT {$this->limit}";
        }

        return $this;
    }

    /**
     * Execute $query with $executeParams.
     *
     * @return array|int
     * @throws Exception
     */
    public function run()
    {
        $this->validateNotNull(
            $this->queryType,
            "Method 'run' requires methods 'select/update/delete/insert' be called before it."
        );

        $stmt = $this->executeStatement(
            $this->query,
            $this->executeParams
        );

        $returnValue = null;

        switch ($this->queryType)
        {
            case 'select':
                $returnValue = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            case 'insert':
                $returnValue = $this->pdo->lastInsertId();
                break;
            case 'delete':
            case 'update':
                $returnValue = $stmt->rowCount() > 0;
                break;
        }

        $this->resetProperties();

        return $returnValue;
    }

    /**
     * Generate $query string with $executeParams.
     *
     * @return mixed
     * @throws Exception
     */
    public function getQuery()
    {
        $this->validateNotNull(
            $this->queryType,
            "Method 'getQuery' requires methods 'select/update/delete/insert' be called before it."
        );

        $query = $this->query;

        foreach ($this->executeParams as $value)
        {
            $pos = strpos(
                $query,
                '?'
            );
            if (false !== $pos)
            {
                $query = substr_replace(
                    $query,
                    "'$value'",
                    $pos,
                    strlen('?')
                );

            }
        }

        $this->resetProperties();

        return $query;
    }

    /**
     * Execute raw query.
     *
     * @param $query
     * @param $executeParams
     *
     * @return bool|PDOStatement
     */
    public function raw($query, $executeParams = [])
    {
        $stmt = $this->executeStatement(
            $query,
            $executeParams
        );

        return $stmt;
    }
}
