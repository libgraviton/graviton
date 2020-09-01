<?php
/**
 * functional test for /hans/showcase
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData;
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
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/hans/showcase/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/hans/showcase/collection';

    /**
     * suppress setup client and load fixtures of parent class
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadLanguageData::class,
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
                'protocol'  => 'protocol',
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

        $expectedErrors = [];
        $notNullError = new \stdClass();
        $notNullError->propertyPath = 'aBoolean';
        $notNullError->message = 'The property aBoolean is required';
        $expectedErrors[] = $notNullError;
        // test choices field (string should not be blank)
        $notNullErrorChoices = new \stdClass();
        $notNullErrorChoices->propertyPath = 'choices';
        $notNullErrorChoices->message = 'The property choices is required';
        $expectedErrors[] = $notNullErrorChoices;

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
    }

    /**
     * see how our empty fields are explained to us
     *
     * @return void
     */
    public function testEmptyAllFields()
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

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );

        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'choices',
                    'message'       => 'The property choices is required',
                ],
                (object) [
                    'propertyPath'  => 'aBoolean',
                    'message'       => 'String value found, but a boolean is required',
                ],
                (object) [
                    'propertyPath'  => 'contact.type',
                    'message'       => 'Must be at least 1 characters long',
                ],
                (object) [
                    'propertyPath'  => 'contact.protocol',
                    'message'       => 'Must be at least 1 characters long',
                ],
                (object) [
                    'propertyPath'  => 'contact.value',
                    'message'       => 'Must be at least 1 characters long',
                ]
            ],
            $client->getResults()
        );
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
        $this->assertObjectNotHasAttribute('hiddenField', $client->getResults());
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
        $this->assertObjectNotHasAttribute('contact', $client->getResults());
        $this->assertObjectNotHasAttribute('someOtherField', $client->getResults());
        $this->assertEquals([], $client->getResults()->contacts);

        $client = static::createRestClient();
        $client->request('GET', '/hans/showcase/?eq(id,string:500)&deselect(contact,someOtherField,contacts)');
        $this->assertTrue(($client->getResults()[0] instanceof \stdClass));
        $this->assertObjectNotHasAttribute('contact', $client->getResults()[0]);
        $this->assertObjectNotHasAttribute('someOtherField', $client->getResults()[0]);
        $this->assertEquals([], $client->getResults()[0]->contacts);
    }

    /**
     * see how our empty fields are explained to us
     *
     * @return void
     */
    public function testEmptyFields()
    {
        $document = [
            'anotherInt'  => 6555488894525,
            'testField'   => ['en' => 'a test string'],
            'aBoolean'    => true,
            'contactCode' => [
                'text'     => ['en' => 'Some Text'],
                'someDate' => '1984-05-01T00:00:00+0000',
            ],
            'contact'     => [
                'type'      => 'abc',
                'value'     => '',
                'protocol'  => '',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/hans/showcase', $document);

        $this->assertEquals(
            Response::HTTP_BAD_REQUEST,
            $client->getResponse()->getStatusCode()
        );

        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'choices',
                    'message'       => 'The property choices is required',
                ],
                (object) [
                    'propertyPath'  => 'contact.protocol',
                    'message'       => 'Must be at least 1 characters long',
                ],
                (object) [
                    'propertyPath'  => 'contact.value',
                    'message'       => 'Must be at least 1 characters long',
                ],
            ],
            $client->getResults()
        );
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

        $expectedErrors = [];
        $expectedErrors[0] = new \stdClass();
        $expectedErrors[0]->propertyPath = "choices";
        $expectedErrors[0]->message = 'Does not have a value in the enumeration ["<",">","=",">=","<=","<>"]';

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
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

        $expectedErrors = [
            (object) [
                'propertyPath' => "nestedApps[0].\$ref",
                'message' =>
                    'Value "http://localhost/core/module/name" does not refer to a correct collection for this extref.'
            ],
            (object) [
                'propertyPath' => "nestedApps[1].\$ref",
                'message' =>
                    'Does not match the regex pattern (\/core\/app\/)([a-zA-Z0-9\-_\+\040\'\.]+)$'
            ]
        ];

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
        );
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
    public function postCreationDataProvider()
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

        $expectedErrors = [];
        $expectedErrors[0] = new \stdClass();
        $expectedErrors[0]->propertyPath = "";
        $expectedErrors[0]->message = 'The property extraFields is not defined and the definition '.
            'does not allow additional properties';
        $expectedErrors[1] = new \stdClass();
        $expectedErrors[1]->propertyPath = "";
        $expectedErrors[1]->message = 'The property anotherExtraField is not defined and the definition '.
            'does not allow additional properties';

        $this->assertJsonStringEqualsJsonString(
            json_encode($expectedErrors),
            json_encode($client->getResults())
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
        $this->assertObjectHasAttribute('anotherInt', $result);
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
    public function rqlDataProvider()
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
        $client->request('GET', '/schema/hans/showcase/item');

        $schema = $client->getResults();

        // make sure we have an extref field here
        $this->assertEquals('extref', $schema->properties->nestedApps->items->properties->{'$ref'}->format);
        // and that 'id' is not there
        $this->assertObjectNotHasAttribute('id', $schema->properties->nestedApps->items->properties);

        // embed case - check the embedded 'contactCode'
        $this->assertStringEndsWith('Embedded', $schema->properties->contactCode->{'x-documentClass'});
        $this->assertObjectHasAttribute('id', $schema->properties->contactCode->properties);
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
    public function findByExtrefProvider()
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
