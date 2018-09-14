<?php

require_once 'libs/QueryBuilder/config.php';
require_once 'libs/QueryBuilder/src/exception/QueryBuilderException.php';
require_once 'libs/QueryBuilder/src/traits/Validators.php';
require_once 'libs/QueryBuilder/src/QueryBuilder.php';

use libs\QueryBuilder\src\QueryBuilder;
use libs\QueryBuilder\src\exception\QueryBuilderException;

class TestsMySQL extends PHPUnit_Framework_TestCase
{
    protected static $builder;

    /**
     * Set up QueryBuilder instance to run in all tests within this file
     * Create table for tests
     * Seed tests table with some data
     */
    public static function setUpBeforeClass()
    {
        self::$builder = new QueryBuilder(
            'mysql',
            MYSQL_SETTINGS['host'],
            MYSQL_SETTINGS['port'],
            MYSQL_SETTINGS['database'],
            MYSQL_SETTINGS['user'],
            MYSQL_SETTINGS['password']
        );

        self::$builder->raw('DROP TABLE IF EXISTS authors');
        self::$builder->raw(
            "CREATE TABLE IF NOT EXISTS authors (
                  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  first_name VARCHAR(255),
                  last_name VARCHAR(255)
            )"
        );
        self::$builder->raw(
            "INSERT INTO authors (first_name, last_name) 
             VALUES 
             ('Matthew', 'Johnson'),
             ('Tony', 'Fortune'),
             ('John', 'James'),
             ('John', 'Robinson')"
        );

        self::$builder->raw('DROP TABLE IF EXISTS books');
        self::$builder->raw(
            "CREATE TABLE books (
                  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  title VARCHAR(255),
                  description TEXT,
                  genre VARCHAR(255),
                  pages INT,
                  author_id INT
            )"
        );
        self::$builder->raw(
            "INSERT INTO books (title, description, genre, pages, author_id) 
             VALUES 
             ('New book1', 'New book description1', 'Fantasy', 800, 1),
             ('New book2', 'New book description2', 'Fantasy', 750, 1),
             ('New book3', 'New book description3', 'Adventure', 700, 2),
             ('New book4', 'New book description4', 'Adventure', 650, 2),
             ('New book5', 'New book description5', 'Science', 600, 3),
             ('New book6', 'New book description6', 'Science', 550, 3),
             ('New book7', 'New book description7', 'Fantasy', 500, 3),
             ('New book8', 'New book description8', 'Fantasy', 450, 2)"
        );
    }

    /**
     * Drop table after tests finished
     * Delete QueryBuilder instance after finishing all tests within this file
     */
    public static function tearDownAfterClass()
    {
        self::$builder->raw('DROP TABLE IF EXISTS authors');
        self::$builder->raw('DROP TABLE IF EXISTS books');
        self::$builder = null;
    }

    /**
     * Test connection QueryBuilderException thrown with unavailable database type set
     */
    public function testConnectionUnavailableDBType()
    {
        try
        {
            new QueryBuilder(
                'oracle',
                MYSQL_SETTINGS['host'],
                MYSQL_SETTINGS['port'],
                MYSQL_SETTINGS['database'],
                MYSQL_SETTINGS['user'],
                MYSQL_SETTINGS['password']
            );
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test single insert
     */
    public function testSingleInsert()
    {
        $runInsertQuery = self::$builder->table('authors')
                                        ->fields(['first_name', 'last_name'])
                                        ->values(['John', 'Walker'])
                                        ->insert()
                                        ->run();

        $getInsertQuery = self::$builder->table('authors')
                                        ->fields(['first_name', 'last_name'])
                                        ->values(['John', 'Walker'])
                                        ->insert()
                                        ->getQuery();

        $this->assertEquals(
            $runInsertQuery,
            5
        );

        $this->assertEquals(
            "INSERT INTO authors (first_name, last_name) VALUES ('John', 'Walker')",
            $getInsertQuery
        );
    }

    /**
     * Test multiple insert
     */
    public function testMultipleInsert()
    {
        $runInsertQuery = self::$builder->table('authors')
                                        ->fields(['first_name', 'last_name'])
                                        ->values(
                                            ['John', 'Smith'],
                                            ['Matthew', 'James'],
                                            ['Vinny', 'Jones']
                                        )
                                        ->insert()
                                        ->run();

        $getInsertQuery = self::$builder->table('authors')
                                        ->fields(['first_name', 'last_name'])
                                        ->values(
                                            ['John', 'Smith'],
                                            ['Matthew', 'James'],
                                            ['Vinny', 'Jones']
                                        )
                                        ->insert()
                                        ->getQuery();

        $this->assertEquals(
            "INSERT INTO authors (first_name, last_name) VALUES ('John', 'Smith'), ('Matthew', 'James'), ('Vinny', 'Jones')",
            $getInsertQuery
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown without table set
     */
    public function testInsertTableMissed()
    {
        try
        {
            self::$builder->fields(['first_name', 'last_name'])
                          ->values(['John', 'Walker'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown without fields set
     */
    public function testInsertFieldsMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->values(['John', 'Walker'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown without values set
     */
    public function testInsertValuesMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name', 'last_name'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * #1
     * Test insert statement QueryBuilderException thrown when values length
     * not equal to fields length
     */
    public function testInsertValuesAndFieldsLengthNotEqual1()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name', 'last_name'])
                          ->values(['John'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * #2
     * Test insert statement QueryBuilderException thrown when values length
     * not equal to fields length
     */
    public function testInsertValuesAndFieldsLengthNotEqual2()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name'])
                          ->values(['John', 'Walker'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown when fields array is
     * associative not sequential
     */
    public function testInsertFieldsArrayNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['key1' => 'first_name', 'key2' => 'last_name']
                          )
                          ->values(['John', 'Walker'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown when values array is
     * associative not sequential
     */
    public function testInsertValuesArrayNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name', 'last_name'])
                          ->values(['key1' => 'John', 'key2' => 'Walker'])
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test insert statement QueryBuilderException thrown if
     * update method called before it
     */
    public function testInsertWithUpdateBefore()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->values(['John', 'Whisper'])
                          ->where(['title', '=', 'Another story'])
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->update()
                          ->insert()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select
     */
    public function testSelect()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors",
            $getSelectQuery
        );
    }

    /**
     * Test select distinct
     */
    public function testSelectDistinct()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name']
                                        )
                                        ->distinct()
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name']
                                        )
                                        ->distinct()
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            4
        );

        $this->assertEquals(
            "SELECT DISTINCT first_name FROM authors",
            $getSelectQuery
        );
    }

    /**
     * Test select with aggregate function
     */
    public function testSelectWithAggregate()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['COUNT(first_name) as count_rows']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['COUNT(first_name) as count_rows']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT COUNT(first_name) as count_rows FROM authors",
            $getSelectQuery
        );
    }

    /**
     * Test select with limit
     */
    public function testSelectWithLimit()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name']
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name']
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT DISTINCT first_name FROM authors LIMIT 1",
            $getSelectQuery
        );
    }

    /**
     * Test select with andWhere clause
     */
    public function testSelectWithAndWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->getQuery();


        $this->assertEquals(
            [
                [
                    'first_name' => 'John',
                    'last_name'  => 'Walker',
                ],
            ],
            $runSelectQuery
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'John' AND last_name = 'Walker'",
            $getSelectQuery
        );
    }

    /**
     * Test select with orWhere clause
     */
    public function testSelectWithOrWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            2
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Walker'",
            $getSelectQuery
        );
    }

    /**
     * Test select with multiple orWhere clause
     */
    public function testSelectWithMultipleOrWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Walker'],
                                            ['last_name', '=', 'Jones']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Walker'],
                                            ['last_name', '=', 'Jones']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            3
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Walker' OR last_name = 'Jones'",
            $getSelectQuery
        );
    }

    /**
     * Test select with orWhere and andWhere clause
     */
    public function testSelectWithOrWhereAndWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Walker' AND last_name = 'Fortune'",
            $getSelectQuery
        );
    }

    /**
     * Test select with andWhere and orWhere clause
     */
    public function testSelectWithAndWhereOrWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->orWhere(['last_name', '=', 'Walker'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            2
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'Tony' AND last_name = 'Fortune' OR last_name = 'Walker'",
            $getSelectQuery
        );
    }

    /**
     * Test select with where LIKE condition
     */
    public function testSelectWithWhereLike()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['last_name', 'LIKE', '%tune'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['last_name', 'LIKE', '%tune'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE last_name LIKE '%tune'",
            $getSelectQuery
        );
    }

    /**
     * Test select with complex where clause
     */
    public function testSelectWithComplexWhere1()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->andWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'Tony' AND last_name = 'Fortune' OR (last_name = 'Walker' AND first_name = 'Jimmy')",
            $getSelectQuery
        );
    }

    /**
     * Test select with complex where clause
     */
    public function testSelectWithComplexWhere2()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT DISTINCT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Fortune' AND (last_name = 'Walker' OR first_name = 'Jimmy')",
            $getSelectQuery
        );
    }

    /**
     * Test select with complex where clause
     */
    public function testSelectWithComplexWhere3()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT DISTINCT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Fortune' AND (last_name = 'Walker' OR first_name = 'Jimmy') OR (last_name = 'Walker' AND first_name = 'Jimmy') LIMIT 1",
            $getSelectQuery
        );
    }

    /**
     * Test select with complex where clause
     */
    public function testSelectWithComplexWhere4()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          function ($query)
                                                          {
                                                              $query->where(
                                                                  [
                                                                      'last_name',
                                                                      '=',
                                                                      'Walker',
                                                                  ]
                                                              )
                                                                    ->orWhere(
                                                                        [
                                                                            'first_name',
                                                                            '=',
                                                                            'Jimmy',
                                                                        ]
                                                                    );
                                                          }
                                                      );
                                            }
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'Tony'])
                                        ->orWhere(
                                            ['last_name', '=', 'Fortune']
                                        )
                                        ->andWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->orWhere(
                                                          function ($query)
                                                          {
                                                              $query->where(
                                                                  [
                                                                      'last_name',
                                                                      '=',
                                                                      'Walker',
                                                                  ]
                                                              )
                                                                    ->orWhere(
                                                                        [
                                                                            'first_name',
                                                                            '=',
                                                                            'Jimmy',
                                                                        ]
                                                                    );
                                                          }
                                                      );
                                            }
                                        )
                                        ->orWhere(
                                            function ($query)
                                            {
                                                $query->where(
                                                    ['last_name', '=', 'Walker']
                                                )
                                                      ->andWhere(
                                                          [
                                                              'first_name',
                                                              '=',
                                                              'Jimmy',
                                                          ]
                                                      );
                                            }
                                        )
                                        ->distinct()
                                        ->limit(1)
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT DISTINCT first_name, last_name FROM authors WHERE first_name = 'Tony' OR last_name = 'Fortune' AND (last_name = 'Walker' OR (last_name = 'Walker' OR first_name = 'Jimmy')) OR (last_name = 'Walker' AND first_name = 'Jimmy') LIMIT 1",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY
     */
    public function testSelectWithGroupBy()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'genre',
                                                'COUNT(pages) as count_pages',
                                            ]
                                        )
                                        ->groupBy(['genre'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'genre',
                                                'COUNT(pages) as count_pages',
                                            ]
                                        )
                                        ->groupBy(['genre'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            3
        );

        $this->assertEquals(
            "SELECT genre, COUNT(pages) as count_pages FROM books GROUP BY genre",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and HAVING
     */
    public function testSelectWithGroupByAndHaving1()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            4
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600'",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and HAVING OR
     */
    public function testSelectWithGroupByAndHaving2()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(['pages', '<', '500'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(['pages', '<', '500'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            5
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' OR pages < '500'",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and HAVING OR + AND
     */
    public function testSelectWithGroupByAndHaving3()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->orHaving(['pages', '<', '500'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->orHaving(['pages', '<', '500'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            2
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' AND title LIKE '%1' OR pages < '500'",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and multiple HAVING OR
     */
    public function testSelectWithGroupByAndHaving4()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            3
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' AND title LIKE '%1' OR pages < '500' OR pages = '550'",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and multiple HAVING OR
     */
    public function testSelectWithGroupByAndHaving5()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->andHaving(['title', 'LIKE', '%1'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            5
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' OR pages < '500' OR pages = '550' AND title LIKE '%1'",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and complex HAVING
     */
    public function testSelectWithGroupByAndComplexHaving1()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            ['pages', '<', '500'],
                                            ['pages', '=', '550']
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            6
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' OR pages < '500' OR pages = '550' AND (pages > '600' OR pages < '500' OR pages = '550')",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and complex HAVING
     */
    public function testSelectWithGroupByAndComplexHaving2()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            6
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' OR (pages > '600' OR pages < '500' OR pages = '550') AND (pages > '600' OR pages < '500' OR pages = '550')",
            $getSelectQuery
        );
    }

    /**
     * Test select with GROUP BY and complex HAVING
     */
    public function testSelectWithGroupByAndComplexHaving3()
    {
        $runSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          function ($query)
                                                          {
                                                              $query->having(
                                                                  [
                                                                      'pages',
                                                                      '>',
                                                                      '600',
                                                                  ]
                                                              )
                                                                    ->orHaving(
                                                                        [
                                                                            'pages',
                                                                            '<',
                                                                            '500',
                                                                        ],
                                                                        [
                                                                            'pages',
                                                                            '=',
                                                                            '550',
                                                                        ]
                                                                    );
                                                          }
                                                      );
                                            }
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('books')
                                        ->fields(
                                            [
                                                'title',
                                                'genre',
                                            ]
                                        )
                                        ->groupBy(['title', 'genre', 'pages'])
                                        ->having(['pages', '>', '600'])
                                        ->orHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          function ($query)
                                                          {
                                                              $query->having(
                                                                  [
                                                                      'pages',
                                                                      '>',
                                                                      '600',
                                                                  ]
                                                              )
                                                                    ->orHaving(
                                                                        [
                                                                            'pages',
                                                                            '<',
                                                                            '500',
                                                                        ],
                                                                        [
                                                                            'pages',
                                                                            '=',
                                                                            '550',
                                                                        ]
                                                                    );
                                                          }
                                                      );
                                            }
                                        )
                                        ->andHaving(
                                            function ($query)
                                            {
                                                $query->having(
                                                    ['pages', '>', '600']
                                                )
                                                      ->orHaving(
                                                          ['pages', '<', '500'],
                                                          ['pages', '=', '550']
                                                      );
                                            }
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            6
        );

        $this->assertEquals(
            "SELECT title, genre FROM books GROUP BY title, genre, pages HAVING pages > '600' OR (pages > '600' OR (pages > '600' OR pages < '500' OR pages = '550')) AND (pages > '600' OR pages < '500' OR pages = '550')",
            $getSelectQuery
        );
    }

    /**
     * Test select with single ORDER BY
     */
    public function testSelectWithSingleOrderBy()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->orderBy(['first_name', 'ASC'])
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->orderBy(['first_name', 'ASC'])
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors ORDER BY first_name ASC",
            $getSelectQuery
        );
    }

    /**
     * Test select with multiple ORDER BY
     */
    public function testSelectWithMultipleOrderBy()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors ORDER BY first_name ASC, last_name DESC",
            $getSelectQuery
        );
    }

    /**
     * Test select with multiple ORDER BY and WHERE
     */
    public function testSelectWithMultipleOrderByAndWhere()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            4
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'John' ORDER BY first_name ASC, last_name DESC",
            $getSelectQuery
        );
    }

    /**
     * Test select with multiple ORDER BY and WHERE and LIMIT
     */
    public function testSelectWithMultipleOrderByAndWhereAndLimit()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->limit(1)
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->fields(
                                            ['first_name', 'last_name']
                                        )
                                        ->where(['first_name', '=', 'John'])
                                        ->orderBy(
                                            ['first_name', 'ASC'],
                                            ['last_name', 'DESC']
                                        )
                                        ->limit(1)
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            1
        );

        $this->assertEquals(
            "SELECT first_name, last_name FROM authors WHERE first_name = 'John' ORDER BY first_name ASC, last_name DESC LIMIT 1",
            $getSelectQuery
        );
    }

    /**
     * Test select with INNER JOIN
     */
    public function testSelectWithInnerJoin()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->join(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->join(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT authors.first_name, authors.last_name, books.title FROM authors INNER JOIN books ON authors.id = books.author_id",
            $getSelectQuery
        );
    }

    /**
     * Test select with LEFT OUTER JOIN
     */
    public function testSelectWithLeftJoin()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->leftJoin(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->leftJoin(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            13
        );

        $this->assertEquals(
            "SELECT authors.first_name, authors.last_name, books.title FROM authors LEFT OUTER JOIN books ON authors.id = books.author_id",
            $getSelectQuery
        );
    }

    /**
     * Test select with RIGHT OUTER JOIN
     */
    public function testSelectWithRightJoin()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->rightJoin(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->rightJoin(
                                            'books',
                                            ['authors.id', 'books.author_id']
                                        )
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT authors.first_name, authors.last_name, books.title FROM authors RIGHT OUTER JOIN books ON authors.id = books.author_id",
            $getSelectQuery
        );
    }

    /**
     * Test select with CROSS JOIN
     */
    public function testSelectWithCrossJoin()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->crossJoin('books')
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->crossJoin('books')
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            64
        );

        $this->assertEquals(
            "SELECT authors.first_name, authors.last_name, books.title FROM authors CROSS JOIN books",
            $getSelectQuery
        );
    }

    /**
     * Test select with NATURAL JOIN
     */
    public function testSelectWithNaturalJoin()
    {
        $runSelectQuery = self::$builder->table('authors')
                                        ->naturalJoin('books')
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->run();

        $getSelectQuery = self::$builder->table('authors')
                                        ->naturalJoin('books')
                                        ->fields(
                                            [
                                                'authors.first_name',
                                                'authors.last_name',
                                                'books.title',
                                            ]
                                        )
                                        ->select()
                                        ->getQuery();

        $this->assertEquals(
            count($runSelectQuery),
            8
        );

        $this->assertEquals(
            "SELECT authors.first_name, authors.last_name, books.title FROM authors NATURAL JOIN books",
            $getSelectQuery
        );
    }

    /**
     * Test select statement QueryBuilderException thrown with orderBy type missed
     */
    public function testSelectWithOrderByTypeMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'John'])
                          ->orderBy(
                              ['first_name', 'ASC'],
                              ['last_name']
                          )
                          ->limit(1)
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown with orderBy invalid type
     */
    public function testSelectWithOrderByInvalidType()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'John'])
                          ->orderBy(
                              ['first_name', 'ASCE'],
                              ['last_name', 'DESC']
                          )
                          ->limit(1)
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown without table set
     */
    public function testSelectTableMissed()
    {
        try
        {
            self::$builder->fields(['first_name', 'last_name'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown without fields set
     */
    public function testSelectFieldsMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if where associative array provided
     */
    public function testSelectWhereNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['field' => 'first_name', '=', 'Tony'])
                          ->orWhere(
                              ['last_name', '=', 'Fortune']
                          )
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if andWhere associative array provided
     */
    public function testSelectAndWhereNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'Tony'])
                          ->andWhere(
                              ['field' => 'last_name', '=', 'Fortune']
                          )
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if orWhere associative array provided
     */
    public function testSelectOrWhereNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'Tony'])
                          ->orWhere(
                              ['field' => 'last_name', '=', 'Fortune']
                          )
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if having associative array provided
     */
    public function testSelectHavingNotSequential()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['field' => 'pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if andHaving associative array provided
     */
    public function testSelectAndHavingNotSequential()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['field' => 'title', 'LIKE', '%1'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if orHaving associative array provided
     */
    public function testSelectOrHavingNotSequential()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['field' => 'pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if groupBy associative array provided
     */
    public function testSelectGroupByNotSequential()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['field' => 'title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if groupBy missed before having
     */
    public function testSelectGroupByMissedBeforeHaving()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if orderBy associative array provided
     */
    public function testSelectOrderByNotSequential()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['field' => 'title', 'ASC'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if where unavailable
     * condition operator provided
     */
    public function testSelectWhereUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=!', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if andWhere unavailable
     * condition operator provided
     */
    public function testSelectAndWhereUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=!', 'Walker'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if orWhere unavailable
     * condition operator provided
     */
    public function testSelectOrWhereUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->where(['first_name', '=', 'John'])
                          ->orWhere(['last_name', '=!', 'Walker'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if having unavailable
     * condition operator provided
     */
    public function testSelectHavingUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '><', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if andHaving unavailable
     * condition operator provided
     */
    public function testSelectAndHavingUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKES', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if orHaving unavailable
     * condition operator provided
     */
    public function testSelectOrHavingUnavailableConditionOperator()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->orHaving(
                              ['pages', '<<', '500'],
                              ['pages', '=', '550']
                          )
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test select statement QueryBuilderException thrown if
     * insert method called before it
     */
    public function testSelectWithUpdateBefore()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->values(['John', 'Whisper'])
                          ->where(['title', '=', 'Another story'])
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->update()
                          ->select()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update
     */
    public function testUpdate()
    {
        $runUpdateQuery = self::$builder->table('authors')
                                        ->fields(['first_name'])
                                        ->values(['Tommy'])
                                        ->where(['first_name', '=', 'John'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->update()
                                        ->run();

        $getUpdateQuery = self::$builder->table('authors')
                                        ->fields(['first_name'])
                                        ->values(['Tommy'])
                                        ->where(['first_name', '=', 'John'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->update()
                                        ->getQuery();

        $this->assertEquals(
            $runUpdateQuery,
            1
        );

        $this->assertEquals(
            "UPDATE authors SET first_name='Tommy' WHERE first_name = 'John' AND last_name = 'Walker'",
            $getUpdateQuery
        );
    }

    /**
     * Test update with limit
     */
    public function testUpdateWithLimit()
    {
        $runUpdateQuery = self::$builder->table('authors')
                                        ->fields(['last_name'])
                                        ->values(['Davidson'])
                                        ->where(['first_name', '=', 'John'])
                                        ->limit(1)
                                        ->update()
                                        ->run();

        $getUpdateQuery = self::$builder->table('authors')
                                        ->fields(['last_name'])
                                        ->values(['Davidson'])
                                        ->where(['first_name', '=', 'John'])
                                        ->limit(1)
                                        ->update()
                                        ->getQuery();

        $this->assertEquals(
            $runUpdateQuery,
            1
        );

        $this->assertEquals(
            "UPDATE authors SET last_name='Davidson' WHERE first_name = 'John' LIMIT 1",
            $getUpdateQuery
        );
    }

    /**
     * Test update statement QueryBuilderException thrown without table set
     */
    public function testUpdateTableMissed()
    {
        try
        {
            self::$builder->fields(['first_name'])
                          ->values(['Tommy'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown without fields set
     */
    public function testUpdateFieldsMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->values(['Tommy'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown without values set
     */
    public function testUpdateValuesMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown without where clause set
     */
    public function testUpdateWhereMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name'])
                          ->values(['Tommy'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * #1
     * Test update statement QueryBuilderException thrown when values length
     * not equal to fields length
     */
    public function testUpdateValuesAndFieldsLengthNotEqual1()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name'])
                          ->values(['Tommy', 'John'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * #2
     * Test update statement QueryBuilderException thrown when values length
     * not equal to fields length
     */
    public function testUpdateValuesAndFieldsLengthNotEqual2()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(['first_name', 'last_name'])
                          ->values(['Tommy'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown when fields array is
     * associative not sequential
     */
    public function testUpdateFieldsArrayNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              [
                                  'first_name' => 'Tommy',
                                  'last_name'  => 'Jameson',
                              ]
                          )
                          ->values(['Tommy', 'Jameson'])
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown when values array is
     * associative not sequential
     */
    public function testUpdateValuesArrayNotSequential()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->values(
                              [
                                  'first_name' => 'Tommy',
                                  'last_name'  => 'Jameson',
                              ]
                          )
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown if
     * delete method called before it
     */
    public function testUpdateWithDeleteBefore()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->values(['John', 'Whisper'])
                          ->where(['title', '=', 'Another story'])
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->delete()
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test update statement QueryBuilderException thrown when
     * there are multiple arrays in values method
     */
    public function testUpdateValuesWithMultipleArrays()
    {
        try
        {
            self::$builder->table('authors')
                          ->fields(
                              ['first_name', 'last_name']
                          )
                          ->values(
                              ['Tommy', 'Jameson'],
                              ['Jimmy', 'Smith']
                          )
                          ->where(['first_name', '=', 'John'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->update()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $runDeleteQuery = self::$builder->table('authors')
                                        ->where(['first_name', '=', 'Tommy'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->delete()
                                        ->run();

        $getDeleteQuery = self::$builder->table('authors')
                                        ->where(['first_name', '=', 'Tommy'])
                                        ->andWhere(['last_name', '=', 'Walker'])
                                        ->delete()
                                        ->getQuery();

        $this->assertEquals(
            $runDeleteQuery,
            1
        );

        $this->assertEquals(
            "DELETE FROM authors WHERE first_name = 'Tommy' AND last_name = 'Walker'",
            $getDeleteQuery
        );
    }

    /**
     * Test delete with limit
     */
    public function testDeleteWithLimit()
    {
        $runDeleteQuery = self::$builder->table('authors')
                                        ->where(['first_name', '=', 'John'])
                                        ->limit(1)
                                        ->delete()
                                        ->run();

        $getDeleteQuery = self::$builder->table('authors')
                                        ->where(['first_name', '=', 'John'])
                                        ->limit(1)
                                        ->delete()
                                        ->getQuery();

        $this->assertEquals(
            $runDeleteQuery,
            1
        );

        $this->assertEquals(
            "DELETE FROM authors WHERE first_name = 'John' LIMIT 1",
            $getDeleteQuery
        );
    }

    /**
     * Test delete statement QueryBuilderException thrown without table set
     */
    public function testDeleteTableMissed()
    {
        try
        {
            self::$builder->where(['first_name', '=', 'Tommy'])
                          ->andWhere(['last_name', '=', 'Walker'])
                          ->delete()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test delete statement QueryBuilderException thrown without where clause set
     */
    public function testDeleteWhereMissed()
    {
        try
        {
            self::$builder->table('authors')
                          ->delete()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test delete statement QueryBuilderException thrown if
     * update method called before it
     */
    public function testDeleteWithUpdateBefore()
    {
        try
        {
            self::$builder->table('books')
                          ->fields(
                              [
                                  'title',
                                  'genre',
                              ]
                          )
                          ->values(['John', 'Whisper'])
                          ->where(['title', '=', 'Another story'])
                          ->groupBy(['title', 'genre', 'pages'])
                          ->having(['pages', '>', '600'])
                          ->andHaving(['title', 'LIKE', '%1'])
                          ->orderBy(['title', 'ASC'])
                          ->update()
                          ->delete()
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test QueryBuilderException thrown without 'select/update/insert/delete' before run
     */
    public function testRunWithoutElse()
    {
        try
        {
            self::$builder->table('authors')
                          ->run();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }

    /**
     * Test QueryBuilderException thrown without 'select/update/insert/delete' before getQuery
     */
    public function testGetQueryWithoutElse()
    {
        try
        {
            self::$builder->table('authors')
                          ->getQuery();
        } catch (QueryBuilderException $exception)
        {
            return;
        }

        $this->fail(
            'An expected exception has not been raised.'
        );
    }
}