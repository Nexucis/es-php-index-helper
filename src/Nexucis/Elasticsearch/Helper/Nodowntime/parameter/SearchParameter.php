<?php

namespace Nexucis\Elasticsearch\Helper\Nodowntime\Parameter;

/**
 * Class SearchParameter
 *
 * @category Elasticsearch Helper
 * @package  Nexucis\Elasticsearch\Helper\Nodowntime\Parameter
 * @author   Augustin Husson <husson.augustin@gmail.com>
 * @license  MIT
 */
class SearchParameter
{

    const OPERATOR_OR = 'OR';
    const OPERATOR_AND = 'AND';
    const SEARCH_TYPE_DFS_QUERY = 'dfs_query_then_fetch';
    const SEARCH_TYPE_QUERY = 'query_then_fetch';

    /**
     * @var string
     */
    protected $analyzer;
    /**
     * @var bool
     */
    protected $analyzeWildcard;
    /**
     * @var string
     * Use the constant OPERATOR_OR or OPERATOR_AND to fill this attribute
     */
    protected $defaultOperator;
    /**
     * @var string
     */
    protected $df;
    /**
     * @var bool
     */
    protected $explain;
    /**
     * @var int
     */
    protected $from;
    /**
     * @var array
     */
    protected $indicesBoost;
    /**
     * @var bool
     */
    protected $lenient;
    /**
     * @var bool
     */
    protected $lowercaseExpandedTerms;
    /**
     * @var string
     */
    protected $preference;
    /**
     * @var string
     */
    protected $q;

    /**
     * @var bool
     */
    protected $queryCache;
    /**
     * @var bool
     */
    protected $requestCache;
    /**
     * @var array
     */
    protected $routing;
    /**
     * @var int
     */
    protected $scroll;
    /**
     * Use the constant SEARCH_TYPE_DFS_QUERY or SEARCH_TYPE_QUERY to fill this attribute
     * See https://www.elastic.co/guide/en/elasticsearch/reference/6.x/search-request-search-type.html
     * for more details on the different types of search that can be performed.
     * @var string
     */
    protected $searchType;
    /**
     * @var int
     */
    protected $size;
    /**
     * @var array
     */
    protected $sort;
    /**
     * @var string
     */
    protected $source;
    /**
     * True or false to return the _source field or not, or a list of fields to return
     * The corresponding parameter in ElasticSearch is _source
     * @var boolean|array
     */
    protected $includeSource;
    /**
     * A list of fields to exclude from the returned _source field
     * The corresponding parameter in ElasticSearch is _source_exclude
     * @var array
     */
    protected $fieldExcluded;
    /**
     * A list of fields to exclude from the returned _source field
     * The corresponding parameter in ElasticSearch is _source_include
     * @var array
     */
    protected $fieldsIncluded;
    /**
     * @var array
     */
    protected $stats;
    /**
     * @var string
     */
    protected $suggestField;
    /**
     * @var string
     */
    protected $suggestMode;
    /**
     * @var int
     */
    protected $suggestSize;
    /**
     * @var string
     */
    protected $suggestText;
    /**
     * @var string
     * The format shall follow the ElasticSearch time units convention:
     * https://www.elastic.co/guide/en/elasticsearch/reference/6.x/common-options.html#time-units
     */
    protected $timeout;
    /**
     * @var boolean
     */
    protected $version;
    /**
     * @var int
     */
    protected $terminateAfter;


    public function __construct()
    {
        $this->from = 0;
        $this->size = 10;
    }

    /**
     * @return array
     */
    public function build()
    {
        return array();
    }

    /**
     * @param string $analyzer
     * @return SearchParameter
     */
    public function analyzer(string $analyzer)
    {
        $this->analyzer = $analyzer;
        return $this;
    }

    /**
     * @param bool $analyzeWildcard
     * @return SearchParameter
     */
    public function analyzeWildcard(bool $analyzeWildcard)
    {
        $this->analyzeWildcard = $analyzeWildcard;
        return $this;
    }

    /**
     * @param string $defaultOperator
     * @return SearchParameter
     */
    public function defaultOperator(string $defaultOperator)
    {
        $this->defaultOperator = $defaultOperator;
        return $this;
    }

    /**
     * @param string $df
     * @return SearchParameter
     */
    public function df(string $df)
    {
        $this->df = $df;
        return $this;
    }

    /**
     * @param bool $explain
     * @return SearchParameter
     */
    public function explain(bool $explain)
    {
        $this->explain = $explain;
        return $this;
    }

    /**
     * @param int $from
     * @return SearchParameter
     */
    public function from(int $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param array $indicesBoost
     * @return SearchParameter
     */
    public function indicesBoost(array $indicesBoost)
    {
        $this->indicesBoost = $indicesBoost;
        return $this;
    }

    /**
     * @param bool $lenient
     * @return SearchParameter
     */
    public function lenient(bool $lenient)
    {
        $this->lenient = $lenient;
        return $this;
    }

    /**
     * @param bool $lowercaseExpandedTerms
     * @return SearchParameter
     */
    public function lowercaseExpandedTerms(bool $lowercaseExpandedTerms)
    {
        $this->lowercaseExpandedTerms = $lowercaseExpandedTerms;
        return $this;
    }

    /**
     * @param string $preference
     * @return SearchParameter
     */
    public function preference(string $preference)
    {
        $this->preference = $preference;
        return $this;
    }

    /**
     * @param string $q
     * @return SearchParameter
     */
    public function q(string $q)
    {
        $this->q = $q;
        return $this;
    }

    /**
     * @param bool $queryCache
     * @return SearchParameter
     */
    public function queryCache(bool $queryCache)
    {
        $this->queryCache = $queryCache;
        return $this;
    }

    /**
     * @param bool $requestCache
     * @return SearchParameter
     */
    public function requestCache(bool $requestCache)
    {
        $this->requestCache = $requestCache;
        return $this;
    }

    /**
     * @param array $routing
     * @return SearchParameter
     */
    public function routing(array $routing)
    {
        $this->routing = $routing;
        return $this;
    }

    /**
     * @param int $scroll
     * @return SearchParameter
     */
    public function scroll(int $scroll)
    {
        $this->scroll = $scroll;
        return $this;
    }

    /**
     * @param string $searchType
     * @return SearchParameter
     */
    public function searchType(string $searchType)
    {
        $this->searchType = $searchType;
        return $this;
    }

    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param array $sort
     * @return SearchParameter
     */
    public function sort(array $sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param string $source
     * @return SearchParameter
     */
    public function source(string $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param array|bool $includeSource
     * @return SearchParameter
     */
    public function includeSource($includeSource)
    {
        $this->includeSource = $includeSource;
        return $this;
    }

    /**
     * @param array $fieldIncluded
     * @return SearchParameter
     */
    public function includeFieldsFromSource(array $fieldIncluded)
    {
        $this->fieldsIncluded = $fieldIncluded;
        return $this;
    }

    /**
     * @param array $fieldExcluded
     * @return SearchParameter
     */
    public function excludeFieldsFromSource(array $fieldExcluded)
    {
        $this->fieldExcluded = $fieldExcluded;
        return $this;
    }

    /**
     * @param array $stats
     * @return SearchParameter
     */
    public function stats(array $stats)
    {
        $this->stats = $stats;
        return $this;
    }

    /**
     * @param string $suggestField
     * @return SearchParameter
     */
    public function suggestField(string $suggestField)
    {
        $this->suggestField = $suggestField;
        return $this;
    }

    /**
     * @param string $suggestMode
     * @return SearchParameter
     */
    public function setSuggestMode(string $suggestMode)
    {
        $this->suggestMode = $suggestMode;
        return $this;
    }

    /**
     * @param int $suggestSize
     * @return SearchParameter
     */
    public function setSuggestSize(int $suggestSize)
    {
        $this->suggestSize = $suggestSize;
        return $this;
    }

    /**
     * @param string $suggestText
     * @return SearchParameter
     */
    public function setSuggestText(string $suggestText)
    {
        $this->suggestText = $suggestText;
        return $this;
    }

    /**
     * @param string $timeout
     * @return SearchParameter
     */
    public function timeout(string $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param bool $version
     * @return SearchParameter
     */
    public function version(bool $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param int $terminateAfter
     * @return SearchParameter
     */
    public function terminateAfter(int $terminateAfter)
    {
        $this->terminateAfter = $terminateAfter;
        return $this;
    }
}
