<?php
/**
 * RqlSearchNodeListenerTest class file
 */

namespace Graviton\Tests\Rest\Listener;

use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Node\SearchNode;
use Graviton\RqlParser\Node\LimitNode;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Solarium\Client;
use Solarium\Component\EdisMax;
use Solarium\Core\Client\Adapter\Curl;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlSearchNodeListenerTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ParameterBag
     */
    private $requestAttrs;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Client
     */
    private $solrClient;
    /**
     * @var \Solarium\Core\Query\AbstractQuery
     */
    private $solrClientQuery;
    /**
     * @var \Solarium\QueryType\Select\Result\Result
     */
    private $solrClientResult;
    /**
     * @var \Solarium\Component\EdisMax
     */
    private $eDismax;
    /**
     * @var SolrQuery
     */
    private $solrQuery;


    /**
     * Setup the test
     *
     * @return void
     */
    protected function setUp() : void
    {
        // request stack
        $this->requestAttrs = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get', 'set'])
            ->getMock();

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCurrentRequest'])
            ->getMock();

        // solr client stuff
        $this->solrClientResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNumFound', 'getIterator'])
            ->getMock();

        $this->solrClientQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEDisMax', 'setQuery', 'setStart', 'setRows', 'setFields'])
            ->getMock();

        $this->eDismax = $this->getMockBuilder(EdisMax::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setQueryFields'])
            ->getMock();

        $this->solrClient = $this->getMockBuilder(Client::class)
            ->setConstructorArgs(
                [
                    $this->getMockBuilder(Curl::class)->getMock(),
                    $this->getMockBuilder(EventDispatcher::class)->getMock()
                ]
            )
            ->onlyMethods(['createQuery', 'addEndpoint', 'setDefaultEndpoint', 'select'])
            ->getMock();

        $this->solrClient
            ->method('createQuery')
            ->willReturn($this->solrClientQuery);
        $this->solrClient
            ->method('select')
            ->willReturn($this->solrClientResult);

        // SolrQuery class
        $this->solrQuery = new SolrQuery(
            $this->getMockBuilder(LoggerInterface::class)->getMock(),
            'http://localhost/solr',
            5,
            3,
            2,
            true,
            [
                'MyNiceDocument' => 'fieldName^2 fieldNameTwo^3',
                'MyOtherNiceDocument' => 'fieldName^20 fieldNameTwo^30'
            ],
            [],
            2,
            $this->solrClient,
            $this->requestStack
        );

        parent::setUp();
    }

    /**
     * check handling of the classmap check
     *
     * @return void
     */
    public function testSolrQueryClassMap()
    {
        $this->solrQuery->setClassName('hans');
        $this->assertFalse($this->solrQuery->isConfigured());

        $this->solrQuery->setClassName('MyNiceDocument');
        $this->assertTrue($this->solrQuery->isConfigured());
    }

    /**
     * Check that interfacing with the solr client is correct
     *
     * @return void
     */
    public function testSolrQueryHandling()
    {
        // expectations / setups
        $this->requestStack
            ->expects($this->exactly(3))
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->eDismax
            ->expects($this->once())
            ->method('setQueryFields')
            ->with('fieldName^20 fieldNameTwo^30');

        $this->solrClient
            ->expects($this->once())
            ->method('addEndpoint');
        $this->solrClient
            ->expects($this->once())
            ->method('setDefaultEndpoint');

        $this->solrClientQuery
            ->expects($this->once())
            ->method('getEDisMax')
            ->willReturn($this->eDismax);

        $this->solrClientQuery
            ->expects($this->once())
            ->method('setQuery')
            ->with('(fred || fred*) && (test || test*)');
        $this->solrClientQuery
            ->expects($this->once())
            ->method('setStart')
            ->with(50)
            ->willReturnSelf();
        $this->solrClientQuery
            ->expects($this->once())
            ->method('setRows')
            ->with(10);
        $this->solrClientQuery
            ->expects($this->once())
            ->method('setFields')
            ->with(['id']);

        $this->solrClientResult
            ->expects($this->exactly(2))
            ->method('getNumFound')
            ->willReturn(9999);


        // the final result returned
        $resultA = new \stdClass();
        $resultA->id = 'KALMAN';
        $resultB = new \stdClass();
        $resultB->id = 'KALMANS';
        $resultC = new \stdClass();
        $resultC->id = 'KALMANZ';
        $resultList = new \ArrayIterator(
            [
                $resultA,
                $resultB,
                $resultC
            ]
        );

        $this->solrClientResult
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($resultList);

        // test
        $this->solrQuery->setClassName('MyOtherNiceDocument');

        $searchNode = new SearchNode(['fred', 'test']);
        $limitNode = new LimitNode(10, 50);

        $this->assertEquals(
            [
                'KALMAN',
                'KALMANS',
                'KALMANZ'
            ],
            $this->solrQuery->query($searchNode, $limitNode)
        );
    }
}
