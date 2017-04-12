<?php

namespace Maelstromeous\Mariokart\Contract;

interface ConfigAwareInterface
{
    public function setConfig(array $config);

    public function getConfig();
}
