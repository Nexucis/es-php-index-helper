<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions;


use Elasticsearch\Common\Exceptions\ElasticsearchException;

/**
 * IndexAlreadyExistException thrown when an index already exists
 *
 * @category Elasticsearch Helper
 * @package  Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
class IndexAlreadyExistException extends \Exception implements ElasticsearchException
{

    private $index;

    /**
     * IndexNotFoundException constructor.
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->index = $alias;
        parent::__construct(sprintf('$index %s already exists', $this->index));
    }

}