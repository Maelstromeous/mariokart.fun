<?php

namespace Maelstromeous\Mariokart\Contract;

use Aura\Sql\ExtendedPdo as DBDriver;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\QueryInterface;

trait DatabaseAwareTrait
{
    /**
     * @var \Aura\Sql\ExtendedPdo
     */
    protected $db;

    /**
     * @var \Aura\Sql\ExtendedPdo
     */
    protected $dbData;

    /**
     * @var \Aura\Sql\ExtendedPdo
     */
    protected $dbArchive;

    /**
     * Set the Database driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseDriver(DBDriver $db)
    {
        $this->db = $db;
    }

    /**
     * Get the Database driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDriver()
    {
        return $this->db;
    }

    /**
     * Gets a new instance of the query factory
     *
     * @return \Aura\SqlQuery\QueryFactory
     */
    public function getQueryFactory()
    {
        return new QueryFactory('mysql');
    }

    /**
     * Gets a new select query builder instance
     *
     * @return \Aura\SqlQuery\Mysql\Select
     */
    public function newSelectQuery()
    {
        return $this->getQueryFactory()->newSelect();
    }

    /**
     * Gets a new select query builder instance
     *
     * @return \Aura\SqlQuery\Mysql\Insert
     */
    public function newInsertQuery()
    {
        return $this->getQueryFactory()->newInsert();
    }

    /**
     * Gets a new update query builder instance
     *
     * @return \Aura\SqlQuery\Mysql\Update
     */
    public function newUpdateQuery()
    {
        return $this->getQueryFactory()->newUpdate();
    }

    /**
     * Gets a new delete query builder instance
     *
     * @return \Aura\SqlQuery\Mysql\Update
     */
    public function newDeleteQuery()
    {
        return $this->getQueryFactory()->newDelete();
    }

    /**
     * Helper function to execute the query and return
     *
     * @param  QueryInterface $query    Query object
     * @param  string         $returnAs Flag to specify return type
     *
     * @return mixed                    Array by default
     */
    public function executeQuery(QueryInterface $query, $returnAs = null)
    {
        $pdo = $this->getDatabaseDriver();

        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        if ($returnAs === null) {
            return $stm->fetch($pdo::FETCH_OBJ);
        }

        if ($returnAs === 'array') {
            return $stm->fetch($pdo::FETCH_ASSOC);
        }

        throw new \Exception('Invalid return type specified!');
    }

    /**
     * Special Insert query function that returns the ID of the last entry
     *
     * @param  QueryInterface $query
     *
     * @return int
     */
    public function executeInsertQuery(QueryInterface $query, $key = 'id')
    {
        $pdo = $this->getDatabaseDriver();
        $stm = $pdo->prepare($query->getStatement());
        $stm->execute($query->getBindValues());

        return $pdo->lastInsertId($query->getLastInsertIdName($key));
    }
}
