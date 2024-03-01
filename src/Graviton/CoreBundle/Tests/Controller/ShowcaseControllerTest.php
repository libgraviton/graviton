<?php
/**
 * functional test for /hans/showcase
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\ShowCaseBundle\DataFixtures\MongoDB\LoadShowCaseData;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for /hans/showcase
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ShowcaseControllerTest extends RestTestCase
{
    /**
     * suppress setup client and load fixtures of parent class
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadShowCaseData::class
            ]
        );
    }

    /**
     * checks empty objects
     *
     * @return void
     */
    public function testGetEmptyObject()
    {
        $showCase = (object) [
            'anotherInt'            => 100,
            'aBoolean'              => true,
            'testField'             => ['en' => 'test'],
            'someOtherField'        => ['en' => 'other'],
            'contactCode'           => [
                'someDate'          => '2015-06-07T06:30:00+0000',
                'text'              => ['en' => 'text'],
            ],
            'contact'               => [
                'type'      => 'type',
                'value'     => 'value',
                'protocol'  => 'tel',
                'uri'       => 'protocol:value',
            ],

            'nestedApps'            => [],
            'unstructuredObject'    => (object) [],
            'choices'               => "<>"
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase/', $showCase);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertNull($client->getResults());

        $url = $client->getResponse()->headers->get('Location');
        $this->assertNotNull($url);

        $client = static::createRestClient();
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $created = $client->getResults();
        $this->assertEquals($showCase->nestedApps, $created->nestedApps);
        $this->assertEquals($showCase->unstructuredObject, $created->unstructuredObject);
    }

    /**
     * see how our missing fields are explained to us
     *
     * @return void
     */
    public function testMissingFields()
    {
        $document = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-incomplete.json'),
            true
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );

        $res = $client->getResults();

        // first should be complaining about aBoolean missing
        $this->assertEquals('aBoolean', $res[1]->propertyPath);

        // post again with the boolean
        $document['aBoolean'] = true;

        $client = static::createRestClient();
        $client->post('/hans/showcase/', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );

        $res = $client->getResults();

        // now should be complaining about 'choices' field
        $this->assertEquals('choices', $res[1]->propertyPath);
    }

    /**
     * see how our empty fields are explained to us
     *
     * @dataProvider emptyFieldsDataProvider
     *
     * @return void
     */
    public function testEmptyAllFields(array $changes, int $expectedCode, ?string $expectedErrorField)
    {
        $document = [
            'anotherInt'  => 6555488894525,
            'testField'   => ['en' => 'a test string'],
            'aBoolean'    => '',
            'contactCode' => [
                'text'     => ['en' => 'Some Text'],
                'someDate' => '1984-05-01T00:00:00+0000',
            ],
            'contact'     => [
                'type'      => '',
                'value'     => '',
                'protocol'  => '',
            ],
        ];

        $document = array_merge(
            $document,
            $changes
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $res = $client->getResults();

        $this->assertEquals(
            $expectedCode,
            $client->getResponse()->getStatusCode()
        );

        if (!is_null($expectedErrorField)) {
            $this->assertEquals($expectedErrorField, $res[1]->propertyPath);
        }
    }

    /**
     * data provider for empty checks
     *
     * @return array[] data
     */
    private static function emptyFieldsDataProvider() : array
    {
        return [
            'simple' => [
                [],
                Response::HTTP_BAD_REQUEST,
                'choices'
            ],
            'bool-wrong-type' => [
                [
                    'choices' => 'a'
                ],
                Response::HTTP_BAD_REQUEST,
                'aBoolean'
            ],
            'wrong-choice' => [
                [
                    'choices' => 'a',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => 'a',
                        'protocol' => 'tel',
                        'value' => ''
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
                'choices'
            ],

            'contact-type-null' => [
                [
                    'choices' => '<',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => null,
                        'protocol' => null,
                        'value' => null
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
                'contact.type'
            ],
            'contact-type-empty' => [
                [
                    'choices' => '<',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => '',
                        'protocol' => null,
                        'value' => null
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
                'contact.type'
            ],
            'contact-type-protocol-null' => [
                [
                    'choices' => '<',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => 'contactType',
                        'protocol' => '',
                        'value' => null
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
                'contact.protocol'
            ],
            'contact-type-value-null' => [
                [
                    'choices' => '<',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => 'contactType',
                        'protocol' => 'tel',
                        'value' => null
                    ]
                ],
                Response::HTTP_BAD_REQUEST,
                'contact.value'
            ],
            'contact-type-value-empty' => [
                [
                    'choices' => '<',
                    'aBoolean' => true,
                    'contact' => [
                        'type' => 'contactType',
                        'protocol' => 'tel',
                        'value' => '' // empty value is accepted here!
                    ]
                ],
                Response::HTTP_CREATED,
                null
            ]
        ];
    }

    /**
     * check that hiddenField is not rendered as it's marked as hidden.. it's filled via fixtures
     *
     * @return void
     */
    public function testHiddenFieldNotExposed()
    {
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');
        $this->assertObjectNotHasProperty('hiddenField', $client->getResults());
    }

    /**
     * check that hiddenField is not rendered as it's marked as hidden.. it's filled via fixtures
     *
     * @return void
     */
    public function testDeselect()
    {
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500?deselect(contact,someOtherField,contacts)');
        $this->assertObjectNotHasProperty('contact', $client->getResults());
        $this->assertObjectNotHasProperty('someOtherField', $client->getResults());
        $this->assertEquals([], $client->getResults()->contacts);

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?eq(id,string:500)&deselect(contact,someOtherField,contacts)');
        $this->assertTrue(($client->getResults()[0] instanceof \stdClass));
        $this->assertObjectNotHasProperty('contact', $client->getResults()[0]);
        $this->assertObjectNotHasProperty('someOtherField', $client->getResults()[0]);
        $this->assertEquals([], $client->getResults()[0]->contacts);
    }

    /**
     * make sure an invalid choice value is detected
     *
     * @return void
     */
    public function testWrongChoiceValue()
    {
        $payload = json_decode(file_get_contents($this->postCreationDataProvider()['minimal'][0]));
        $payload->choices = 'invalidChoice';

        $client = static::createRestClient();
        $client->post('/hans/showcase', $payload);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // first real items should be choices violation!
        $this->assertEquals('choices', $client->getResults()[1]->propertyPath);
    }

    /**
     * make sure an invalid extref value is detected
     *
     * @return void
     */
    public function testWrongExtRef()
    {
        $payload = json_decode(file_get_contents($this->postCreationDataProvider()['minimal'][0]));
        $payload->nestedApps = [
            (object) ['$ref' => 'http://localhost/core/module/name'],
            (object) ['$ref' => 'unknown']
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase', $payload);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            'nestedApps.0.$ref',
            $client->getResults()[1]->propertyPath
        );

        // fix first!
        $payload->nestedApps = [
            (object) ['$ref' => '/core/app/name'],
            (object) ['$ref' => 'unknown']
        ];

        $client->post('/hans/showcase', $payload);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // now complains about 2nd array ref

        $this->assertEquals(
            'nestedApps.1.$ref',
            $client->getResults()[1]->propertyPath
        );

        // fix both
        $payload->nestedApps = [
            (object) ['$ref' => '/core/app/name'],
            (object) ['$ref' => 'http://full-path:port/core/app/name2']
        ];

        $client->post('/hans/showcase', $payload);

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
    }

    /**
     * insert various formats to see if all works as expected
     *
     * @dataProvider postCreationDataProvider
     *
     * @param string $filename filename
     *
     * @return void
     */
    public function testPost($filename)
    {
        // showcase contains some datetime fields that we need rendered as UTC in the case of this test
        ini_set('date.timezone', 'UTC');
        $document = json_decode(
            file_get_contents($filename),
            false
        );

        $client = static::createRestClient();
        $client->post('/hans/showcase/', $document);
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));

        $result = $client->getResults();

        // unset id as we cannot compare and don't care
        $this->assertNotNull($result->id);
        unset($result->id);

        $this->assertJsonStringEqualsJsonString(
            json_encode($document),
            json_encode($result)
        );
    }

    /**
     * Provides test sets for the testPost() test.
     *
     * @return array
     */
    public static function postCreationDataProvider(): array
    {
        $basePath = dirname(__FILE__).'/../resources/';

        return array(
            'minimal' => array($basePath.'showcase-minimal.json'),
            'complete' => array($basePath.'showcase-complete.json')
        );
    }

    /**
     * test if we can save & retrieve extrefs inside 'free form objects'
     *
     * @return void
     */
    public function testFreeFormExtRefs()
    {
        $minimalExample = $this->postCreationDataProvider()['minimal'][0];

        $document = json_decode(
            file_get_contents($minimalExample),
            false
        );

        $document->id = 'dynextreftest';

        // insert some refs!
        $document->unstructuredObject = new \stdClass();
        $document->unstructuredObject->testRef = new \stdClass();
        $document->unstructuredObject->testRef->{'$ref'} = 'http://localhost/hans/showcase/500';

        // let's go more deep..
        $document->unstructuredObject->go = new \stdClass();
        $document->unstructuredObject->go->more = new \stdClass();
        $document->unstructuredObject->go->more->deep = new \stdClass();
        $document->unstructuredObject->go->more->deep->{'$ref'} = 'http://localhost/hans/showcase/500';

        // array?
        $document->unstructuredObject->refArray = [];
        $document->unstructuredObject->refArray[0] = new \stdClass();
        $document->unstructuredObject->refArray[0]->{'$ref'} = 'http://localhost/core/app/dude';
        $document->unstructuredObject->refArray[1] = new \stdClass();
        $document->unstructuredObject->refArray[1]->{'$ref'} = 'http://localhost/core/app/dude2';

        $client = static::createRestClient();
        $client->put('/hans/showcase/'.$document->id, $document);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/'.$document->id);

        // all still the same?
        $this->assertEquals($document, $client->getResults());
    }

    /**
     * are extra fields denied?
     *
     * @return void
     */
    public function testExtraFieldPost()
    {
        ini_set('date.timezone', 'UTC');
        $document = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-minimal.json'),
            false
        );
        $document->extraFields = "nice field";
        $document->anotherExtraField = "one more";

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString(
            'additional properties',
            $client->getResults()[1]->message
        );
    }

    /**
     * Test RQL select statement
     *
     * @return void
     */
    public function testRqlSelect()
    {
        $filtred = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/showcase-rql-select-filtred.json'),
            false
        );

        $fields = [
            'someFloatyDouble',
            'contact',
            'contactCode.text',
            'unstructuredObject.booleanField',
            'unstructuredObject.hashField.someField',
            'unstructuredObject.nestedArrayField.anotherField',
            'nestedCustomers',
            'choices'
        ];
        $rqlSelect = 'select('.implode(',', array_map([$this, 'encodeRqlString'], $fields)).')';

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?'.$rqlSelect);

        /* expect empty arrays */
        $filtred = array_map(
            function ($entry) {
                $entry->contacts = [];
                $entry->nestedArray = [];
                $entry->nestedApps = [];
                return $entry;
            },
            $filtred
        );

        $this->assertEquals($filtred, $client->getResults());

        foreach ([
                     '500' => $filtred[0],
                     '600' => $filtred[1],
                 ] as $id => $item) {
            $client = static::createRestClient();
            $client->request('GET', '/hans/showcase/'.$id.'?'.$rqlSelect);
            $this->assertEquals($item, $client->getResults());
        }
    }

    /**
     * Test to see if we can do like() searches on identifier fields
     *
     * @return void
     */
    public function testLikeSearchOnIdentifierField()
    {
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?like(id,5*)');

        // we should only get 1 ;-)
        $this->assertEquals(1, count($client->getResults()));

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?like(id,*0)');

        // this should get both
        $this->assertEquals(2, count($client->getResults()));
    }

    /**
     * Test PATCH for deep nested attribute
     *
     * @return void
     */
    public function testPatchDeepNestedProperty()
    {
        // Apply PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/unstructuredObject/hashField/anotherField',
                    'value' => 'changed nested hash field with patch'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Get changed showcase
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(
            'changed nested hash field with patch',
            $result->unstructuredObject->hashField->anotherField
        );
    }

    /**
     * Test success PATCH method - response headers contains link to resource
     *
     * @return void
     */
    public function testPatchSuccessResponseHeaderContainsResourceLink()
    {
        // Apply PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/testField/en',
                    'value' => 'changed value'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            '/hans/showcase/500',
            $client->getResponse()->headers->get('Content-Location')
        );
    }

    /**
     * Test PATCH method - remove/change ID not allowed
     *
     * @return void
     */
    public function testPatchRemoveAndChangeIdNotAllowed()
    {
        // Apply PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'remove',
                    'path' => '/id'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Test PATCH: add property to free object structure
     *
     * @return void
     */
    public function testPatchAddPropertyToFreeObject()
    {
        // Apply PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'add',
                    'path' => '/unstructuredObject/hashField/newAddedField',
                    'value' => 'new field value'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Get changed showcase
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(
            'new field value',
            $result->unstructuredObject->hashField->newAddedField
        );
    }

    /**
     * Test PATCH for $ref attribute
     *
     * @return void
     * @incomplete
     */
    public function testApplyPatchForRefAttribute()
    {
        // Apply PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/nestedApps/0',
                    'value' => [
                        '$ref' => 'http://localhost/core/app/admin'
                    ]
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check patched result
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(
            'http://localhost/core/app/admin',
            $result->nestedApps[0]->{'$ref'}
        );
    }

    /**
     * Test PATCH: apply patch which results to invalid Showcase schema
     *
     * @return void
     */
    public function testPatchToInvalidShowcase()
    {
        // Apply PATCH request, remove required field
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'remove',
                    'path' => '/anotherInt'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // Check that Showcase has not been changed
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertObjectHasProperty('anotherInt', $result);
    }

    /**
     * Test PATCH: remove element from array
     *
     * @return void
     */
    public function testRemoveFromArrayPatch()
    {
        // Apply PATCH request, remove nested app, initially there are 2 apps
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'remove',
                    'path' => '/nestedApps/0'
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check patched result
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(1, count($result->nestedApps));
        $this->assertEquals('http://localhost/core/app/admin', $result->nestedApps[0]->{'$ref'});
    }

    /**
     * Test PATCH: add new element to array
     *
     * @return void
     */
    public function testAddElementToSpecificIndexInArrayPatch()
    {
        // Apply PATCH request, add new element
        $client = static::createRestClient();
        $newElement = ['name' => 'element three'];
        $patchJson = json_encode(
            [
                [
                    'op' => 'add',
                    'path' => '/nestedArray/1',
                    'value' => $newElement
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check patched result
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(3, count($result->nestedArray));
        $this->assertJsonStringEqualsJsonString(
            json_encode($newElement),
            json_encode($result->nestedArray[1])
        );
    }

    /**
     * Test PATCH: add complex object App to array
     *
     * @group ref
     * @return void
     */
    public function testPatchAddComplexObjectToSpecificIndexInArray()
    {
        // Apply PATCH request, add new element
        $client = static::createRestClient();
        $newApp = ['$ref' => 'http://localhost/core/app/admin'];
        $patchJson = json_encode(
            [
                [
                    'op' => 'add',
                    'path' => '/nestedApps/0',
                    'value' => $newApp
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check patched result
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(3, count($result->nestedApps));
        $this->assertEquals(
            'http://localhost/core/app/admin',
            $result->nestedApps[0]->{'$ref'}
        );
    }

    /**
     * Test PATCH: add complex object App to array
     *
     * @group ref
     * @return void
     */
    public function testPatchAddComplexObjectToTheEndOfArray()
    {
        // Apply PATCH request, add new element
        $client = static::createRestClient();
        $newApp = ['$ref' => 'http://localhost/core/app/test'];
        $patchJson = json_encode(
            [
                [
                    'op' => 'add',
                    'path' => '/nestedApps/-',
                    'value' => $newApp
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check patched result
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(3, count($result->nestedApps));
        $this->assertEquals(
            'http://localhost/core/app/test',
            $result->nestedApps[2]->{'$ref'}
        );
    }

    /**
     * Test PATCH: test operation to undefined index
     *
     * @group ref
     * @return void
     */
    public function testPatchTestOperationToUndefinedIndexThrowsException()
    {
        // Apply PATCH request, add new element
        $client = static::createRestClient();
        $newApp = ['ref' => 'http://localhost/core/app/test'];
        $patchJson = json_encode(
            [
                [
                    'op' => 'test',
                    'path' => '/nestedApps/9',
                    'value' => $newApp
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * Test PATCH: add complex object App to array
     *
     * @group ref
     * @return void
     */
    public function testPatchAddElementToUndefinedIndexResponseAsBadRequest()
    {
        // Apply PATCH request, add new element
        $client = static::createRestClient();
        $newApp = ['ref' => 'http://localhost/core/app/admin'];
        $patchJson = json_encode(
            [
                [
                    'op' => 'add',
                    'path' => '/nestedApps/9',
                    'value' => $newApp
                ]
            ]
        );
        $client->request('PATCH', '/hans/showcase/500', [], [], [], $patchJson);
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // Check that patched document not changed
        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/500');

        $result = $client->getResults();
        $this->assertEquals(2, count($result->nestedApps));
    }

    /**
     * Encode string value in RQL
     *
     * @param string $value String value
     * @return string
     */
    private function encodeRqlString($value)
    {
        return strtr(
            rawurlencode($value),
            [
                '-' => '%2D',
                '_' => '%5F',
                '.' => '%2E',
                '~' => '%7E',
            ]
        );
    }

    /**
     * Trigger a 301 Status code
     *
     * @param string $url         requested url
     * @param string $redirectUrl redirected url
     * @dataProvider rqlDataProvider
     * @return void
     */
    public function testTrigger301($url, $redirectUrl)
    {
        $client = static::createRestClient();
        $client->request('GET', $url);
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
        $this->assertEquals($redirectUrl, $client->getResponse()->headers->get('Location'));
    }

    /**
     * Provides urls for the testTrigger301() test.
     *
     * @return array
     */
    public static function rqlDataProvider(): array
    {
        return [
            'rql' => ['url' => '/hans/showcase?id=blah' , 'redirect_url' => 'http://localhost/hans/showcase/?id=blah'],
            'noRql' => ['url' => '/hans/showcase' , 'redirect_url' => 'http://localhost/hans/showcase/']
        ];
    }

    /**
     * Here we test the client expectation in "id" property exposing in the json schema.
     *
     * They want
     * * "id" of an extref object should *not* be described/present in schema
     * * "id" of others, including embedded objects, *should* be described/present in schema
     *
     * @return void
     */
    public function testCorrectIdExposingInSchema()
    {
        // get the schema
        $client = static::createRestClient();
        $client->request('GET', '/schema/hans/showcase/openapi.json');

        $schema = $client->getResults();

        // get the nested app schema
        $nestedAppSchema = $schema->components->schemas->{'ShowCaseNestedApps'};
        $this->assertIsObject($nestedAppSchema);

        // make sure we have an extref field here
        $this->assertEquals('extref', $nestedAppSchema->properties->{'$ref'}->format);
        // and that 'id' is not there
        $this->assertObjectNotHasProperty('id', $nestedAppSchema->properties);

        // embed case - check the embedded 'contactCode'
        $contactCodeSchema = $schema->components->schemas->{'ShowCaseContactCode'};
        $this->assertIsObject($contactCodeSchema);

        //$this->assertStringEndsWith('Embedded', $schema->properties->contactCode->{'x-documentClass'});
        $this->assertObjectNotHasProperty('id', $contactCodeSchema->properties);
    }

    /**
     * test finding of showcases by ref
     *
     * @dataProvider findByExtrefProvider
     *
     * @param string  $field which reference to search in
     * @param mixed   $url   ref to search for
     * @param integer $count number of results to expect
     *
     * @return void
     */
    public function testFindByExtref($field, $url, $count)
    {
        $url = sprintf(
            '/hans/showcase/?%s=%s',
            $this->encodeRqlString($field),
            $this->encodeRqlString($url)
        );

        $client = static::createRestClient();
        $client->request('GET', $url);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount($count, $client->getResults());
    }

    /**
     * @return array
     */
    public static function findByExtrefProvider(): array
    {
        return [
            'find a linked record when searching for "tablet" ref by array field' => [
                'nestedApps.0.$ref',
                'http://localhost/core/app/tablet',
                1
            ],
            'find a linked record when searching for "admin" ref by array field' => [
                'nestedApps.0.$ref',
                'http://localhost/core/app/admin',
                1
            ],
            'find nothing when searching for inextistant (and unlinked) ref by array field' => [
                'nestedApps.0.$ref',
                'http://localhost/core/app/inexistant',
                0
            ],
            'return nothing when searching with incomplete ref by array field' => [
                'nestedApps.0.$ref',
                'http://localhost/core/app',
                0
            ],
        ];
    }
}
