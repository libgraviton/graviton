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
     * @param string $restrictionMode     restriction mode
     * @param bool   $persistRestrictions persist restrictions
     * @param string $clientId            clientid
     * @param array  $headers             more headers
     *
     * @return RestrictionListener listener
     */
    private function getSut($restrictionMode, $persistRestrictions, $clientId = '1', $headers = [])
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

        return new RestrictionListener(
            $logger,
            $restrictionMap,
            $requestStack,
            $restrictionMode,
            $persistRestrictions
        );
    }

    /**
     * Data provider for restriction modes
     *
     * @return array[] modes
     */
    public function dataProviderModes()
    {
        return [
            // in this mode, we make "EQ" comparisons with the client id..
            'eqmode' => [
                RestrictionListener::RESTRICTION_MODE_EQ,
                true
            ],
            // in this mode, we make an LTE comparison
            'ltemode' => [
                RestrictionListener::RESTRICTION_MODE_LTE,
                false // don't persist restriction values
            ]
        ];
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
     * @param string $restrictionMode     restriction mode
     * @param bool   $persistRestrictions persist restrictions
     *
     * @dataProvider dataProviderModes
     *
     * @return void
     */
    public function testOnModelQuery($restrictionMode, $persistRestrictions)
    {
        $sut = $this->getSut($restrictionMode, $persistRestrictions);

        $builder = new Builder($this->getDm(), App::class);

        $event = new ModelQueryEvent();
        $event->setQueryBuilder($builder);

        $sut->onModelQuery($event);

        if ($restrictionMode == RestrictionListener::RESTRICTION_MODE_EQ) {
            // normal EQ mode
            $expectedQuery = [
                '$and' => [
                    ['clientId' => ['$in' => [null, 1]]]
                ]
            ];
        } else {
            // LTE mode
            $expectedQuery = [
                '$and' => [
                    [
                        '$or' => [
                            ['clientId' => null],
                            ['clientId' => ['$lte' => 1]]
                        ]
                    ]
                ]
            ];
        }

        $event->getQueryBuilder()->getQuery()->execute()->toArray();
        $this->assertEquals(
            $expectedQuery,
            $event->getQueryBuilder()->getQueryArray()
        );
    }

    /**
     * test onDeleteOrPersist
     *
     * @param string $restrictionMode     restriction mode
     * @param bool   $persistRestrictions persist restrictions
     *
     * @dataProvider dataProviderModes
     *
     * @return void
     */
    public function testOnDeleteOrPersist($restrictionMode, $persistRestrictions)
    {
        $repo = $this->getDm()->getRepository(App::class);

        $event = new EntityPrePersistEvent();

        $app = new App();
        $event->setEntity($app);
        $event->setRepository($repo);

        $sut = $this->getSut($restrictionMode, $persistRestrictions);
        $sut->onEntityPrePersistOrDelete($event);

        // should be set to clientId before we save it!
        if ($persistRestrictions) {
            $this->assertEquals('1', $event->getEntity()['clientId']);
        } else {
            $this->assertNull($event->getEntity()['clientId']);
        }
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

        $sut = $this->getSut(RestrictionListener::RESTRICTION_MODE_EQ, true);
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

        $sut = $this->getSut(RestrictionListener::RESTRICTION_MODE_EQ, true);
        $sut->onRqlSearch($event);

        $expectedTerms = [
            'search',
            'term',
            'clientId:1'
        ];

        $this->assertEquals($expectedTerms, $event->getNode()->getSearchTerms());
    }
}
