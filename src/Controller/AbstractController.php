<?php

namespace Maelstromeous\Mariokart\Controller;

use Maelstromeous\Mariokart\Contract\DatabaseAwareInterface;
use Maelstromeous\Mariokart\Contract\DatabaseAwareTrait;
use Maelstromeous\Mariokart\Contract\TemplateAwareInterface;
use Maelstromeous\Mariokart\Contract\TemplateAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController implements
    DatabaseAwareInterface,
    TemplateAwareInterface
{
    use DatabaseAwareTrait;
    use TemplateAwareTrait;
}
