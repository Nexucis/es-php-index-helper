<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions;

use Elasticsearch\Common\Exceptions\ElasticsearchException;

/**
 * IndexNotFoundException thrown when an index is not found
 *
 * @category Elasticsearch Helper
 * @package  Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
class IndexNotFoundException extends \Exception implements ElasticsearchException
{
    /**
     * @var string
     */
    private $index;

    /**
     * IndexNotFoundException constructor.
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->index = $alias;
        parent::__construct(sprintf('index %s not found', $this->index));
    }
}
