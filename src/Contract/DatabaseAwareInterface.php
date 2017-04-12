<?php

namespace Maelstromeous\Mariokart\Contract;

use Aura\Sql\ExtendedPdo as DBDriver;

interface DatabaseAwareInterface
{
    /**
     * Set the Database driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseDriver(DBDriver $db);

    /**
     * Get the Database driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDriver();

    /**
     * Set the DatabaseData driver
     *
     * @param \Aura\Sql\ExtendedPdo $dbData
     */
    public function setDatabaseDataDriver(DBDriver $dbData);

    /**
     * Get the DatabaseData driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseDataDriver();

    /**
     * Set the Database Archive driver
     *
     * @param \Aura\Sql\ExtendedPdo $db
     */
    public function setDatabaseArchiveDriver(DBDriver $db);

    /**
     * Get the Database Archive driver
     *
     * @return \Aura\Sql\ExtendedPdo
     */
    public function getDatabaseArchiveDriver();
}
