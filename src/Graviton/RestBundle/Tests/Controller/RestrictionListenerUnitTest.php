<?php

namespace Graviton\RestBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\AnalyticsBundle\Event\PreAggregateEvent;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\RestBundle\Listener\RestrictionListener;
use Graviton\Rql\Event\VisitNodeEvent;
use Graviton\Rql\Node\SearchNode;
use Graviton\TestBundle\Test\GravitonTestCase;
use GravitonDyn\AppBundle\Document\App;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionListenerUnitTest extends GravitonTestCase
{

    /**
     * gets the sut
     *
     * @param string $clientId clientid
     * @param array  $headers  more headers
     *
     * @return RestrictionListener listener
     */
    private function getSut($clientId = '1', $headers = [])
    {
        $server = array_merge(
            $headers,
            [
                'HTTP_X-GRAVITON-CLIENT' => $clientId,
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $request = new Request([], [], [], [], [], $server);

        $logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();

        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->getMock();

        $requestStack->expects($this->any())->method('getCurrentRequest')->willReturn($request);

        $restrictionMap = [
            'x-graviton-client' => 'int:clientId'
        ];

        return new RestrictionListener($logger, $restrictionMap, $requestStack);
    }

    /**
     * gets the dm
     *
     * @return DocumentManager manager
     */
    private function getDm()
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
    }

    /**
     * test onModelQuery
     *
     * @return void
     */
    public function testOnModelQuery()
    {
        $sut = $this->getSut();

        $builder = new Builder($this->getDm(), App::class);

        $event = new ModelQueryEvent();
        $event->setQueryBuilder($builder);

        $sut->onModelQuery($event);

        // normal EQUAL tenant mode
        $this->assertEquals(
            [
                '$and' => [
                    ['clientId' => ['$in' => [null, 1]]]
                ]
            ],
            $event->getQueryBuilder()->getQueryArray()
        );
    }

    /**
     * test onDeleteOrPersist
     *
     * @return void
     */
    public function testOnDeleteOrPersist()
    {
        $repo = $this->getDm()->getRepository(App::class);

        $event = new EntityPrePersistEvent();

        $app = new App();
        $event->setEntity($app);
        $event->setRepository($repo);

        $sut = $this->getSut();
        $sut->onEntityPrePersistOrDelete($event);

        // should be set to clientId before we save it!
        $this->assertEquals('1', $event->getEntity()['clientId']);
    }

    /**
     * test onPreAggregate
     *
     * @return void
     */
    public function testOnPreAggregate()
    {
        $event = new PreAggregateEvent();
        $event->setPipeline([]);

        $sut = $this->getSut();
        $sut->onPreAggregate($event);

        $expectedPipeline = [
            [
                '$match' => [
                    'clientId' => [
                        '$in' => [1, null]
                    ]
                ],
            ],
            [
                '$project' => [
                    'clientId' => 0
                ]
            ]
        ];

        $this->assertEquals($expectedPipeline, $event->getPipeline());
    }

    /**
     * test onRqlSearch
     *
     * @return void
     */
    public function testOnRqlSearch()
    {
        $searchNode = new SearchNode(['search', 'term']);

        $event = new VisitNodeEvent(
            $searchNode,
            $this->getMockBuilder(Builder::class)->disableOriginalConstructor()->getMock(),
            new \SplStack()
        );

        $sut = $this->getSut();
        $sut->onRqlSearch($event);

        $expectedTerms = [
            'search',
            'term',
            'clientId:1'
        ];

        $this->assertEquals($expectedTerms, $event->getNode()->getSearchTerms());
    }
}
