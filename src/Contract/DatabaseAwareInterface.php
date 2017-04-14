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
}
