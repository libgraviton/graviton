<?php
/**
 * SolrQueryTest class file
 */
namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Node\SearchNode;
use Symfony\Component\HttpFoundation\Request;
use Xiag\Rql\Parser\Node\LimitNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrQueryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * setup type we want to test
     *
     * @param string  $expectedQuery expected query
     * @param boolean $andifyTerms   if terms should be ANDified
     *
     * @return SolrQuery sut
     */
    private function getMock($expectedQuery, $andifyTerms = false)
    {
        $className = '\Test\Class';
        $classFields = 'fieldA^1 fieldB^2';

        $request = new Request();

        $requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();
        $requestStack
            ->expects($this->exactly(3))
            ->method('getCurrentRequest')
            ->willReturn($request);

        $edisMax = $this->getMockBuilder('\Solarium\Component\EdisMax')
            ->disableOriginalConstructor()
            ->setMethods(['setQueryFields'])
            ->getMock();
        $edisMax
            ->expects($this->once())
            ->method('setQueryFields')
            ->with($classFields)
            ->willReturn(true);

        $solrQueryClass = $this->getMockBuilder('\Solarium\QueryType\Select\Query\Query')
            ->disableOriginalConstructor()
            ->setMethods(['getEDisMax', 'setQuery'])
            ->getMock();
        $solrQueryClass->method('getEDisMax')->willReturn($edisMax);
        $solrQueryClass
            ->expects($this->once())
            ->method('setQuery')
            ->with($expectedQuery);

        $searchResult = $this->getMockBuilder('\Solarium\QueryType\Select\Result\Result')
            ->disableOriginalConstructor()
            ->setMethods(['getNumFound', 'getIterator'])
            ->getMock();
        $searchResult
            ->expects($this->once())
            ->method('getNumFound')
            ->willReturn(5);
        $searchResult
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $solrClient = $this->getMockBuilder('\Solarium\Client')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(
                [
                    'createQuery',
                    'getQuery',
                    'addEndpoint',
                    'setDefaultEndpoint',
                    'select'
                ]
            )
            ->getMock();
        $solrClient->method('createQuery')->willReturn($solrQueryClass);
        $solrClient->method('getQuery')->willReturn($solrQueryClass);
        $solrClient->method('addEndpoint')->willReturn(true);
        $solrClient->method('setDefaultEndpoint')->willReturn(true);
        // return results here
        $solrClient->method('select')->willReturn($searchResult);

        $solr = new SolrQuery(
            'http://solr:3033',
            5,
            4,
            $andifyTerms,
            [
                $className => $classFields
            ],
            10,
            $solrClient,
            $requestStack
        );
        $solr->setClassName($className);

        return $solr;
    }

    /**
     * verify that the correct search term is given with a certain input
     *
     * @dataProvider solrQueryHandlingDataProvider
     *
     * @param string  $searchTerm    term
     * @param string  $expectedQuery what should be sent to solr
     * @param boolean $andifyTerms   andify terms?
     *
     * @return void
     */
    public function testSolrQueryHandling($searchTerm, $expectedQuery, $andifyTerms)
    {
        $solr = $this->getMock($expectedQuery, $andifyTerms);

        $searchNode = new SearchNode(explode(' ', $searchTerm));
        $limitNode = new LimitNode(10, 0);

        $solr->query($searchNode, $limitNode);
    }

    /**
     * data provider for solr test
     *
     * @return array searches
     */
    public function solrQueryHandlingDataProvider()
    {
        return [
            'simple-search' => [
                'han',
                'han',
                true
            ],
            'simple-search-andified' => [
                'han ha2',
                'han AND ha2',
                true
            ],
            'simple-search-wildcard' => [
                'hans',
                '(hans OR hans*)',
                true
            ],
            'simple-search-fuzzy' => [
                'hanso',
                '(hanso OR hanso~)',
                true
            ],
            'forced-wildcard-from-client' => [
                'hanso*', // this *should* be fuzzy as from config, but client wants wildcard
                '(hanso OR hanso*)',
                true
            ],
            'forced-fuzzy-from-client' => [
                'hans~', // this *should* be fuzzy as from config, but client wants wildcard
                '(hans OR hans~)',
                true
            ],
            'forced-mixed-from-client' => [
                'hansomat* han~',
                '(hansomat OR hansomat*) AND (han OR han~)',
                true
            ],
            'simple-combined-no-andify' => [
                'han hans hanso',
                'han (hans OR hans*) (hanso OR hanso~)',
                false
            ],
            'simple-combined-with-andify' => [
                'han hans hanso',
                'han AND (hans OR hans*) AND (hanso OR hanso~)',
                true
            ],
            'alphanumeric-iban' => [
                'CH0000000111111111111',
                '"CH0000000111111111111"',
                true
            ],
            'alphanumeric-others' => [
                'HANS1234',
                '"HANS1234"',
                true
            ],
            'only numbers' => [
                '2131412434142',
                '"2131412434142"',
                true
            ],
            'simple-search-account-syntax' => [
                '99 1.123.456.78',
                '"99 1.123.456.78"',
                true
            ],
            'own-operator-2-NOT' => [
                'peter AND year:>40 AND month:<10 NOT segment:15 NOT segment:90',
                '(peter OR peter~) AND year:[40 TO *] AND month:[* TO 10] NOT segment:"15" NOT segment:"90"',
                true
            ],
            'own-operator-1-NOT' => [
                'peter NOT segment:15',
                '(peter OR peter~) NOT segment:"15"',
                true
            ],
            'own-operator-1-NOT-BOOL' => [
                'peter ! segment:15',
                '(peter OR peter~) ! segment:"15"',
                true
            ],
            'own-operator-1-OR-BOOL' => [
                'peter || segment:15',
                '(peter OR peter~) || segment:"15"',
                true
            ]
        ];
    }
}
