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

    /**
     * @var string
     */
    public static $OPERATOR_OR = 'OR';
    /**
     * @var string
     */
    public static $OPERATOR_AND = 'AND';
    /**
     * @var string
     */
    public static $SEARCH_TYPE_DFS_QUERY = 'dfs_query_then_fetch';
    /**
     * @var string
     */
    public static $SEARCH_TYPE_QUERY = 'query_then_fetch';

    /**
     * @var string
     */
    protected $analyzer;

    /**
     * @var bool
     */
    protected $analyzeWildcard;

    /**
     * Use the constant OPERATOR_OR or OPERATOR_AND to fill this attribute
     * @var string
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
     * @var string[]
     */
    protected $fields;
    /**
     * @var int
     */
    protected $from;

    /**
     * @var string
     */
    protected $ignoreIndices;

    /**
     * @var string[]
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
     * @var string[]
     */
    protected $routing;

    /**
     * The format shall follow the ElasticSearch time units convention:
     * https://www.elastic.co/guide/en/elasticsearch/reference/6.x/common-options.html#time-units
     * An example is available here: https://www.elastic.co/guide/en/elasticsearch/reference/6.x/search-request-scroll.html
     * @var string
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
     * @var string[]
     */
    protected $sort;

    /**
     * @var string
     */
    protected $source;

    /**
     * True or false to return the _source field or not, or a list of fields to return
     * The corresponding parameter in ElasticSearch is _source
     * @var bool|string[]
     */
    protected $includeSource;

    /**
     * A list of fields to exclude from the returned _source field
     * The corresponding parameter in ElasticSearch is _source_exclude
     * @var string[]
     */
    protected $fieldExcluded;

    /**
     * A list of fields to exclude from the returned _source field
     * The corresponding parameter in ElasticSearch is _source_include
     * @var string[]
     */
    protected $fieldsIncluded;

    /**
     * @var string[]
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
     * The format shall follow the ElasticSearch time units convention:
     * https://www.elastic.co/guide/en/elasticsearch/reference/6.x/common-options.html#time-units
     * @var string
     */
    protected $timeout;

    /**
     * @var bool
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
     * Convert the object to an appropriate array with the correct field attempt by ElasticSearch
     * @return string[]
     */
    public function build()
    {
        $params = array();
        $this->buildAnalyzer($params);
        $this->buildAnalyzeWildcard($params);
        $this->buildDefaultOperator($params);
        $this->buildDf($params);
        $this->buildExplain($params);
        $this->buildFields($params);
        $this->buildFrom($params);
        $this->buildIgnoreIndices($params);
        $this->buildIndicesBoost($params);
        $this->buildLenient($params);
        $this->buildLowercaseExpendedTerms($params);
        $this->buildPreference($params);
        $this->buildQuery($params);
        $this->buildQueryCache($params);
        $this->buildRequestCache($params);
        $this->buildRouting($params);
        $this->buildScroll($params);
        $this->buildSearchType($params);
        $this->buildSize($params);
        $this->buildSort($params);
        $this->buildSource($params);
        $this->buildIncludeSource($params);
        $this->buildFieldExcluded($params);
        $this->buildFieldsIncluded($params);
        $this->buildState($params);
        $this->buildSuggestField($params);
        $this->buildSuggestMode($params);
        $this->buildSuggestSize($params);
        $this->buildSuggestText($params);
        $this->buildTimeout($params);
        $this->buildTerminateAfter($params);
        $this->buildVersion($params);

        return $params;
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
     * @param string[] $fields
     * @return SearchParameter
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;
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
     * @param string $ignoreIndices
     * @return SearchParameter
     */
    public function ignoreIndices(string $ignoreIndices)
    {
        $this->ignoreIndices = $ignoreIndices;
        return $this;
    }

    /**
     * @param string[] $indicesBoost
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
     * @param string[] $routing
     * @return SearchParameter
     */
    public function routing(array $routing)
    {
        $this->routing = $routing;
        return $this;
    }

    /**
     * @param string $scroll
     * @return SearchParameter
     */
    public function scroll(string $scroll)
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

    /**
     * @param int $size
     * @return SearchParameter
     */
    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param string[] $sort
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
     * @param string[]|bool $includeSource
     * @return SearchParameter
     */
    public function includeSource($includeSource)
    {
        $this->includeSource = $includeSource;
        return $this;
    }

    /**
     * @param string[] $fieldIncluded
     * @return SearchParameter
     */
    public function includeFieldsFromSource(array $fieldIncluded)
    {
        $this->fieldsIncluded = $fieldIncluded;
        return $this;
    }

    /**
     * @param string[] $fieldExcluded
     * @return SearchParameter
     */
    public function excludeFieldsFromSource(array $fieldExcluded)
    {
        $this->fieldExcluded = $fieldExcluded;
        return $this;
    }

    /**
     * @param string[] $stats
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
    public function suggestMode(string $suggestMode)
    {
        $this->suggestMode = $suggestMode;
        return $this;
    }

    /**
     * @param int $suggestSize
     * @return SearchParameter
     */
    public function suggestSize(int $suggestSize)
    {
        $this->suggestSize = $suggestSize;
        return $this;
    }

    /**
     * @param string $suggestText
     * @return SearchParameter
     */
    public function suggestText(string $suggestText)
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

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildAnalyzer(&$params)
    {
        $this->buildString($params, 'analyzer', $this->analyzer);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildAnalyzeWildcard(&$params)
    {
        $this->buildBoolean($params, 'analyze_wildcard', $this->analyzeWildcard);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildDefaultOperator(&$params)
    {
        if (($this->defaultOperator === SearchParameter::$OPERATOR_OR) || ($this->defaultOperator === SearchParameter::$OPERATOR_AND)) {
            $params['default_operator'] = $this->defaultOperator;
        }
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildDf(&$params)
    {
        $this->buildString($params, 'df', $this->df);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildExplain(&$params)
    {
        $this->buildBoolean($params, 'explain', $this->explain);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildFields(&$params)
    {
        $this->buildArray($params, 'fields', $this->fields);
    }

    /**
     * @param string[] $param
     * @return void
     */
    protected function buildFrom(&$param)
    {
        $param['from'] = $this->from;
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildIgnoreIndices(&$params)
    {
        $this->buildString($params, 'ignore_indices', $this->ignoreIndices);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildIndicesBoost(&$params)
    {
        $this->buildArray($params, 'indices_boost', $this->indicesBoost);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildLenient(&$params)
    {
        $this->buildBoolean($params, 'lenient', $this->lenient);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildLowercaseExpendedTerms(&$params)
    {
        $this->buildBoolean($params, 'lowercase_expanded_terms', $this->lowercaseExpandedTerms);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildPreference(&$params)
    {
        $this->buildString($params, 'preference', $this->preference);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildQuery(&$params)
    {
        $this->buildString($params, 'q', $this->q);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildQueryCache(&$params)
    {
        $this->buildBoolean($params, 'query_cache', $this->queryCache);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildRequestCache(&$params)
    {
        $this->buildBoolean($params, 'request_cache', $this->requestCache);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildRouting(&$params)
    {
        $this->buildArray($params, 'routing', $this->routing);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildScroll(&$params)
    {
        $this->buildString($params, 'scroll', $this->scroll);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSearchType(&$params)
    {
        if (($this->searchType === SearchParameter::$SEARCH_TYPE_DFS_QUERY) || ($this->searchType === SearchParameter::$SEARCH_TYPE_QUERY)) {
            $params['search_type'] = $this->searchType;
        }
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSize(&$params)
    {
        $params['size'] = $this->size;
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSort(&$params)
    {
        $this->buildArray($params, 'sort', $this->sort);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSource(&$params)
    {
        $this->buildString($params, 'source', $this->source);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildIncludeSource(&$params)
    {
        if (is_bool($this->includeSource)) {
            $params['_source'] = $this->includeSource;
        } elseif (is_array($this->includeSource) && (count($this->includeSource) !== 0)) {
            $params['_source'] = implode(',', $this->includeSource);
        }
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildFieldExcluded(&$params)
    {
        $this->buildArray($params, '_source_exclude', $this->fieldExcluded);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildFieldsIncluded(&$params)
    {
        $this->buildArray($params, '_source_include', $this->fieldsIncluded);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildState(&$params)
    {
        $this->buildArray($params, 'stats', $this->stats);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSuggestField(&$params)
    {
        $this->buildString($params, 'suggest_field', $this->suggestField);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSuggestMode(&$params)
    {
        $this->buildString($params, 'suggest_mode', $this->suggestMode);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSuggestSize(&$params)
    {
        $this->buildInt($params, 'suggest_size', $this->suggestSize);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildSuggestText(&$params)
    {
        $this->buildString($params, 'suggest_text', $this->suggestText);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildTimeout(&$params)
    {
        $this->buildString($params, 'timeout', $this->timeout);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildTerminateAfter(&$params)
    {
        $this->buildInt($params, 'terminate_after', $this->terminateAfter);
    }

    /**
     * @param string[] $params
     * @return void
     */
    protected function buildVersion(&$params)
    {
        $this->buildBoolean($params, 'version', $this->version);
    }

    /**
     * @param string[] $params
     * @param string $esParam
     * @param bool $attribute
     *
     * @return void
     */
    private function buildBoolean(&$params, string $esParam, $attribute)
    {
        if (is_bool($attribute)) {
            $params[$esParam] = $attribute;
        }
    }

    /**
     * @param string[] $params
     * @param string $esParam
     * @param int $attribute
     *
     * @return void
     */
    private function buildInt(&$params, string $esParam, $attribute)
    {
        if (is_int($attribute) && ($attribute > 0)) {
            $params[$esParam] = $attribute;
        }
    }

    /**
     * @param string[] $params
     * @param string $esParam
     * @param string $attribute
     *
     * @return void
     */
    private function buildString(&$params, string $esParam, $attribute)
    {
        if (is_string($attribute) && strlen($attribute) > 0) {
            $params[$esParam] = $attribute;
        }
    }

    /**
     * @param string[] $params
     * @param string $esParam
     * @param string[] $attribute
     *
     * @return void
     */
    private function buildArray(&$params, string $esParam, $attribute)
    {
        if (is_array($attribute) && (count($attribute) !== 0)) {
            $params[$esParam] = implode(',', $attribute);
        }
    }
}
