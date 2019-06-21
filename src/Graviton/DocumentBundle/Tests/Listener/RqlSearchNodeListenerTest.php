<?php
/**
 * RqlSearchNodeListenerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Listener;

use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\Rql\Node\SearchNode;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\Component\EdisMax;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Graviton\RqlParser\Node\LimitNode;

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
            ->setMethods(['get', 'set'])
            ->getMock();

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

        $this->requestStack = $this->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();

        // solr client stuff
        $this->solrClientResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNumFound', 'getIterator'])
            ->getMock();

        $this->solrClientQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEDisMax', 'setQuery', 'setStart', 'setRows', 'setFields'])
            ->getMock();

        $this->eDismax = $this->getMockBuilder(EdisMax::class)
            ->disableOriginalConstructor()
            ->setMethods(['setQueryFields'])
            ->getMock();

        $this->solrClient = $this->getMockBuilder(Client::class)
            ->setConstructorArgs(
                [
                    null,
                    $this->getMockBuilder(EventDispatcher::class)->getMock()
                ]
            )
            ->setMethods(['createQuery', 'addEndpoint', 'setDefaultEndpoint', 'select'])
            ->getMock();

        $this->solrClient
            ->method('createQuery')
            ->willReturn($this->solrClientQuery);
        $this->solrClient
            ->method('select')
            ->willReturn($this->solrClientResult);

        // SolrQuery class
        $this->solrQuery = new SolrQuery(
            'http://localhost/solr',
            5,
            3,
            true,
            [
                'MyNiceDocument' => 'fieldName^2 fieldNameTwo^3',
                'MyOtherNiceDocument' => 'fieldName^20 fieldNameTwo^30'
            ],
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
            ->with('fieldName^20 fieldNameTwo^30')
            ->willReturn(true);

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
            ->expects($this->once())
            ->method('getNumFound')
            ->willReturn(9999);


        // the final result returned
        $resultA = new \stdClass();
        $resultA->id = 'KALMAN';
        $resultB = new \stdClass();
        $resultB->id = 'KALMANS';
        $resultC = new \stdClass();
        $resultC->id = 'KALMANZ';
        $resultList = new \ArrayObject(
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
