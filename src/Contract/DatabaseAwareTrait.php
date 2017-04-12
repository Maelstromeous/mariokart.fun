<?php

namespace Maelstromeous\Mariokart\Contract;

use Aura\Sql\ExtendedPdo as DBDriver;

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
     * Set the Database Data driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseDataDriver(DBDriver $db)
    {
        $this->dbData = $db;
    }

    /**
     * Get the Database Data driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDataDriver()
    {
        return $this->dbData;
    }

    /**
     * Set the Database Data driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseArchiveDriver(DBDriver $db)
    {
        $this->dbArchive = $db;
    }

    /**
     * Get the Database Data driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseArchiveDriver()
    {
        return $this->dbArchive;
    }
}
