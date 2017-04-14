<?php

namespace Maelstromeous\Mariokart\Contract;

use Aura\Sql\ExtendedPdo as DBDriver;
use Aura\SqlQuery\QueryFactory;

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
}
