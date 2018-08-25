<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime\parameter;

use Nexucis\Elasticsearch\Helper\Nodowntime\Parameter\SearchParameter;
use PHPUnit\Framework\TestCase;

class SearchParameterTest extends TestCase
{
    public function testBuildWithFullSearchParameter()
    {
        $search = (new SearchParameter())
            ->analyzer('my_analyzer')
            ->analyzeWildcard(true)
            ->defaultOperator(SearchParameter::$OPERATOR_OR)
            ->df('prefix')
            ->explain(true)
            ->fields(array(
                'field1',
                'field2'
            ))
            ->from(5)
            ->ignoreIndices('missing')
            ->indicesBoost(array(
                'index1'
            ))
            ->lenient(false)
            ->lowercaseExpandedTerms(false)
            ->preference('random')
            ->q('lucene query')
            ->queryCache(false)
            ->requestCache(false)
            ->routing(array(
                'routing1', 'routing2'
            ))
            ->scroll('1m')
            ->searchType(SearchParameter::$SEARCH_TYPE_DFS_QUERY)
            ->size(87)
            ->sort(array(
                'field1:direction1'
            ))
            ->source('query dsl')
            ->includeSource(array(
                'name',
                'id'
            ))
            ->excludeFieldsFromSource(array(
                'exclude1'
            ))
            ->includeFieldsFromSource(array(
                'include1', 'include2', 'include3'
            ))
            ->stats(array(
                'tag1', 'tag2', 'tag3', 'tag4'
            ))
            ->suggestField('field1')
            ->suggestMode('missing')
            ->suggestSize(14)
            ->suggestText('my suggestion text')
            ->timeout('10s')
            ->terminateAfter(25)
            ->version(true);

        $expectedParams = array(
            'analyzer' => 'my_analyzer',
            'analyze_wildcard' => true,
            'default_operator' => 'OR',
            'df' => 'prefix',
            'explain' => true,
            'fields' => 'field1,field2',
            'from' => 5,
            'ignore_indices' => 'missing',
            'indices_boost' => 'index1',
            'lenient' => false,
            'lowercase_expanded_terms' => false,
            'preference' => 'random',
            'q' => 'lucene query',
            'query_cache' => false,
            'request_cache' => false,
            'routing' => 'routing1,routing2',
            'scroll' => '1m',
            'search_type' => 'dfs_query_then_fetch',
            'size' => 87,
            'sort' => 'field1:direction1',
            'source' => 'query dsl',
            '_source' => 'name,id',
            '_source_exclude' => 'exclude1',
            '_source_include' => 'include1,include2,include3',
            'stats' => 'tag1,tag2,tag3,tag4',
            'suggest_field' => 'field1',
            'suggest_mode' => 'missing',
            'suggest_size' => 14,
            'suggest_text' => 'my suggestion text',
            'timeout' => '10s',
            'terminate_after' => 25,
            'version' => true,
        );

        $params = $search->build();
        $this->assertTrue($this->arraysAreSimilar($expectedParams, $params));
    }

    /**
     * Snippet found on stack overflow : https://stackoverflow.com/questions/3293531/how-to-permanently-remove-few-commits-from-remote-branch
     * Determine if two associative arrays are similar
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    private function arraysAreSimilar($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }
}
