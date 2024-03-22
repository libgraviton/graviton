<?php
/**
 * SolrQueryTest class file
 */
namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Node\SearchNode;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Graviton\RqlParser\Node\LimitNode;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrQueryTest extends TestCase
{

    /**
     * setup type we want to test
     *
     * @param string  $expectedQuery  expected query
     * @param boolean $andifyTerms    if terms should be ANDified
     * @param int     $fuzzyBridge    fuzzy bridge
     * @param int     $wildcardBridge wildcard bridge
     * @param int     $literalBridge  literal bridge
     *
     * @return SolrQuery sut
     */
    private function getMock(
        $expectedQuery,
        $andifyTerms = false,
        $fuzzyBridge = 5,
        $wildcardBridge = 4,
        $literalBridge = 5
    ) {
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
            ->with($classFields);

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
            ->expects($this->exactly(2))
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
        $solrClient->method('addEndpoint');
        $solrClient->method('setDefaultEndpoint');
        // return results here
        $solrClient->method('select')->willReturn($searchResult);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $solr = new SolrQuery(
            $logger,
            'http://solr:3033',
            $fuzzyBridge,
            $wildcardBridge,
            $literalBridge,
            $andifyTerms,
            [
                $className => $classFields
            ],
            [
                $className => [
                    'sort' => 'sort asc'
                ]
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
            'literal-escaped-&' => [
                'meier & dude',
                '(meier || meier~) && (\&) && (dude*)',
                true
            ],
            'literal-escaped-+' => [
                'meier + dude',
                '(meier || meier~) && (\+) && (dude*)',
                true
            ],
            'int-tel-search-short' => [
                '+41 79 521 21 21',
                '"+41795212121"',
                true
            ],
            'simple-combined-with-andify' => [
                'han hans hanso',
                'han && (hans*) && (hanso || hanso~)',
                true
            ],
            'own-operator-2-NOT' => [
                'peter && year:>40 && month:<10 -segment:15 -segment:90',
                '(peter || peter~) && year:[40 TO *] && month:[* TO 10] NOT segment:"15" NOT segment:"90"',
                true
            ],
            'tel-search' => [
                '087 532 11 11',
                '"+41875321111"',
                true
            ],
            'tel-search-mixed' => [
                '087 332 11 11 muster',
                '"+41873321111" && (muster || muster~)',
                true
            ],
            'int-tel-search' => [
                '+1 55 555 11 11',
                '"+1555551111"',
                true
            ],
            'int-tel-search-mixed' => [
                'muster +1 55 555 11 11',
                '(muster || muster~) && "+1555551111"',
                true
            ],
            'simple-search' => [
                'han',
                'han',
                true
            ],
            'simple-search-andified' => [
                'han ha2',
                'han && ha2',
                true
            ],
            'simple-search-multispace1' => [
                'han  ha2',
                'han && ha2',
                true
            ],
            'simple-search-multispace2' => [
                'han      ha2    ha3 ha4',
                'han && ha2 && ha3 && ha4',
                true
            ],
            'simple-search-wildcard' => [
                'hans',
                '(hans*)',
                true
            ],
            'simple-search-liteal-fuzzy' => [
                'hanso',
                '(hanso || hanso~)',
                true
            ],
            'forced-wildcard-from-client' => [
                'hanso*', // this *should* be fuzzy as from config, but client wants wildcard
                '(hanso || hanso*)',
                true
            ],
            'forced-fuzzy-from-client' => [
                'hans~', // this *should* be fuzzy as from config, but client wants wildcard
                '(hans~)',
                true
            ],
            'forced-mixed-from-client' => [
                'hansomat* han~',
                '(hansomat || hansomat*) && (han~)',
                true
            ],
            'simple-combined-no-andify' => [
                'han hans hanso',
                'han (hans*) (hanso || hanso~)',
                false
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

            'own-operator-1-NOT' => [
                'peter NOT segment:15',
                '(peter || peter~) NOT segment:"15"',
                true
            ],
            'own-operator-1-NOT-BOOL' => [
                'peter ! segment:15',
                '(peter || peter~) ! segment:"15"',
                true
            ],
            'own-operator-1-NOT-OP' => [
                'peter -segment:15',
                '(peter || peter~) NOT segment:"15"',
                true
            ],
            'own-operator-1-||-BOOL' => [
                'peter || segment:15',
                '(peter || peter~) || segment:"15"',
                true
            ],
            'own-operator-1-||-BOOL-OP' => [
                'peter || segment:15 -segment:16',
                '(peter || peter~) || segment:"15" NOT segment:"16"',
                true
            ],
            'word-and-bool' => [
                'peter isBool:true noBool:false',
                '(peter || peter~) && isBool:T && noBool:F',
                true
            ],
            'metacharacters1' => [
                'b-5a',
                '(/b[-]5a.*/)',
                true
            ],
            'metacharacters2' => [
                'p-5a ag',
                '(/p[-]5a.*/) && ag',
                true
            ],
            'jp-love1' => [
                'jean-pierre',
                '("jean-pierre" || jean-pierre~)',
                true
            ],
            'metacharacters-over-literal' => [
                'p-5a-another ag',
                '("p-5a-another" || p-5a-another~) && ag',
                true
            ]
        ];
    }

    /**
     * verify that the correct search term is given with a certain input
     *
     * @dataProvider solrQueryHandlingDataProviderSecond
     *
     * @param string $searchTerm    term
     * @param string $expectedQuery what should be sent to solr
     *
     * @return void
     */
    public function testSolrQueryHandlingOtherSettings($searchTerm, $expectedQuery)
    {
        $solr = $this->getMock($expectedQuery, true, 9999, 1);

        $searchNode = new SearchNode(explode(' ', $searchTerm));
        $limitNode = new LimitNode(10, 0);

        $solr->query($searchNode, $limitNode);
    }

    /**
     * data provider for solr test
     *
     * @return array searches
     */
    public function solrQueryHandlingDataProviderSecond()
    {
        return [
            'simple-search' => [
                'han',
                '(han*)'
            ],
            'simple-search-andified' => [
                'han ha2',
                '(han*) && (ha2*)'
            ],
            'only numbers' => [
                '2131412434142',
                '"2131412434142"'
            ],
            'simple-search-account-syntax' => [
                '99 1.123.456.78',
                '"99 1.123.456.78"'
            ],
            'tel-search' => [
                '087 321 11 11',
                '"+41873211111"',
                true
            ],
            'metacharacters1' => [
                'b-5a',
                '(/b[-]5a.*/)'
            ],
            'metacharacters2' => [
                'p-5a ag',
                '(/p[-]5a.*/) && (ag*)'
            ],
            'jp-love1' => [
                'jean-pierre',
                '("jean-pierre" || /jean[-]pierre.*/)'
            ],
            'jp-love2' => [
                '"jean-pierre"',
                '("jean-pierre" || /"jean[-]pierre".*/)'
            ],
            'metacharacters-over-literal' => [
                'p-5a-another ag',
                '("p-5a-another" || /p[-]5a[-]another.*/) && (ag*)'
            ]
        ];
    }
}
