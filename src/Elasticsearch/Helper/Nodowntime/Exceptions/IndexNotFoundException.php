<?php
namespace Elasticsearch\Helper\Nodowntime\Exceptions;


use Elasticsearch\Common\Exceptions\ElasticsearchException;

/**
 * IndexNotFoundException thrown when an index is not found
 *
 * @category Elasticsearch Helper
 * @package  Elasticsearch\Helper\Nodowntime\Exceptions
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
class IndexNotFoundException extends \Exception implements ElasticsearchException
{

}