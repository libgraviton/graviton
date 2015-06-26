<?php
namespace Graviton\GeneratorBundle\Definition;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * This class represents the json file that defines the structure
 * of a mongo collection that exists and serves as a base to generate
 * a bundle.
 *
 * @todo     if this json format serves in more places; move this class
 * @todo     validate json
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinition
{

    /**
     * Path to our json file
     *
     * @var string
     */
    private $filename;

    /**
     * Deserialized json
     *
     * @var \stdClass
     */
    private $doc;

    /**
     * Composed namespace of this definition, must be explicitly set
     *
     * @var string
     */
    private $namespace;

    /**
     * Constructor
     *
     * @param string $filename Path to the json file
     *
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->filename = $filename;

        // TODO [lapistano]: THIS SHALL NOT BE HERE!!
        if (!file_exists($this->filename)) {
            throw new FileNotFoundException(
                sprintf(
                    'File %s doesn\'t exist',
                    $this->filename
                )
            );
        }

        $this->doc = json_decode(file_get_contents($this->filename));

        if (empty($this->doc) || !is_object($this->doc)) {
            throw new \RuntimeException(sprintf('Could not load %s', $filename));
        }
    }

    /**
     * Returns this loads ID
     *
     * @return string ID
     */
    public function getId()
    {
        if (!property_exists($this->doc, 'id')) {
            throw new \RuntimeException(sprintf("No id found for document %s", $this->filename));
        }
        return $this->doc->id;
    }

    /**
     * Returns the path of the original file as set in the constructor
     *
     * @returns string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the description
     *
     * @return string Description
     */
    public function getDescription()
    {
        $ret = '';
        if (isset($this->doc->description)) {
            $ret = $this->doc->description;
        }

        return $ret;
    }

    /**
     * Returns whether this definition requires the generation
     * of a controller. normally yes, but sometimes not ;-)
     *
     * @return bool true if yes, false if no
     */
    public function hasController()
    {
        $hasController = true;
        if (!isset($this->doc->service) || (isset($this->doc->service)) && !isset($this->doc->service->routerBase)) {
            $hasController = false;
        }

        return $hasController;
    }

    /**
     * This is a method that allows us to distinguish between a full json spec
     * and a hash defined in a full spec which was divided into a seperate Document (thus, a SubDocument).
     * To be aware what it is mainly serves for the generator to generate them as embedded documents,
     * as subdocuments are always embedded.
     *
     * @return bool true if yes, false if not
     */
    public function isSubDocument()
    {
        $ret = false;
        if (isset($this->doc->isSubDocument) && $this->doc->isSubDocument == true) {
            $ret = true;
        }
        return $ret;
    }

    /**
     * Gets the namespace
     *
     * @return string namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Sets the namespace
     *
     * @param string $namespace namespace
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        // normalize namespace
        $namespace = str_replace('/', '\\', $namespace);

        if (substr($namespace, -1) == '\\') {
            $namespace = substr($namespace, 0, -1);
        }

        $this->namespace = $namespace;
    }

    /**
     * Returns whether this service is read-only
     *
     * @return bool true if yes, false if not
     */
    public function isReadOnlyService()
    {
        // default
        $ret = false;

        if (isset($this->doc->service->readOnly) && (bool) $this->doc->service->readOnly === true) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Returns whether this service has fixtures
     *
     * @return bool true if yes, false if not
     */
    public function hasFixtures()
    {
        // default
        $ret = false;

        if (count($this->getFixtures()) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Returns the fixtures or empty array if none
     *
     * @return array fixtures
     */
    public function getFixtures()
    {
        // default
        $ret = array();

        if (isset($this->doc->service->fixtures) && is_array($this->doc->service->fixtures)) {
            $ret = $this->doc->service->fixtures;
        }

        return $ret;
    }

    /**
     * Returns the order number at which order this fixture should be loaded.
     * this is needed if we have relations/references between the fixtures..
     *
     * @return int order
     */
    public function getFixtureOrder()
    {
        // default
        $ret = 100;
        if (isset($this->doc->service->fixtureOrder)) {
            $ret = (int) $this->doc->service->fixtureOrder;
        }
        return $ret;
    }

    /**
     * Returns a router base path. false if default should be used.
     *
     * @return string router base, i.e. /bundle/name/
     */
    public function getRouterBase()
    {
        $ret = false;

        if (isset($this->doc->service->routerBase) && strlen($this->doc->service->routerBase) > 0) {
            $ret = $this->doc->service->routerBase;
            if (substr($ret, 0, 1) != '/') {
                $ret = '/' . $ret;
            }

            if (substr($ret, -1) == '/') {
                $ret = substr($ret, 0, -1);
            }
        }

        return $ret;
    }

    /**
     * Returns the Controller classname this services' controller shout inherit.
     * Defaults to the RestController of the RestBundle of course.
     *
     * @return string base controller
     */
    public function getBaseController()
    {
        $ret = 'RestController';

        if (isset($this->doc->service->baseController) && strlen($this->doc->service->baseController) > 0) {
            $ret = $this->doc->service->baseController;
        }

        return $ret;
    }

    /**
     * Returns the parent service to use when adding the service xml
     *
     * Defaults to graviton.rest.controller
     *
     * @return string base controller
     */
    public function getParentService()
    {
        $ret = 'graviton.rest.controller';

        if (isset($this->doc->service->parent) && strlen($this->doc->service->parent) > 0) {
            $ret = $this->doc->service->parent;
        }

        return $ret;
    }

    /**
     * Returns a specific field or null
     *
     * @param string $name Field name
     *
     * @return JsonDefinitionField The field
     */
    public function getField($name)
    {
        $ret = null;
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $name) {
                $ret = $field;
                break;
            }
        }

        return $ret;
    }

    /**
     * Returns the field definition
     *
     * @return JsonDefinitionField[] Fields
     */
    public function getFields()
    {
        $fields = array();
        $relations = $this->getRelations();

        foreach ($this->doc->target->fields as $field) {
            $field = new JsonDefinitionField($field);
            $fields[$field->getName()] = $field;

            // embed rel?
            if (isset($relations[$field->getName()]->type)) {
                if ($relations[$field->getName()]->type == 'embed') {
                    $fields[$field->getName()]->setRelType($field::REL_TYPE_EMBED);
                }
            }
        }

        // object generation (dot-notation parsing)
        $fieldHierarchy = array();
        $retFields = array();
        $arrayHashes = array();
        foreach ($fields as $fieldName => $field) {
            if (strpos($fieldName, '.') !== false) {
                $nameParts = explode('.', $fieldName);

                // hm, i'm too uninspired to make this recursive..
                switch (count($nameParts)) {
                    case 2:
                        $fieldHierarchy[$nameParts[0]][$nameParts[1]] = $field;

                        if (preg_match('([0-9]+)', $nameParts[1])) {
                            $arrayHashes[] = $nameParts[0];
                        }

                        break;
                    case 3:
                        // handle "0-9" in second part (like field.0.val)
                        // ..handle as normal hash, but set array property
                        if (preg_match('([0-9]+)', $nameParts[1])) {
                            $fieldHierarchy[$nameParts[0]][$nameParts[2]] = $field;
                            $arrayHashes[] = $nameParts[0];
                        }
                        break;
                }
            } else {
                $retFields[$fieldName] = $field;
            }
        }

        foreach ($fieldHierarchy as $fieldName => $subElements) {
            $retFields[$fieldName] = new JsonDefinitionHash(
                $fieldName,
                $subElements
            );
            $retFields[$fieldName]->setParent($this);
            $retFields[$fieldName]->setRelType(JsonDefinitionHash::REL_TYPE_EMBED);

            if (in_array($fieldName, $arrayHashes)) {
                $retFields[$fieldName]->setIsArrayHash(true);
            }
        }

        return $retFields;
    }

    /**
     * Get target relations which are explictly defined
     *
     * @return array relations
     */
    public function getRelations()
    {
        $ret = array();
        if (isset($this->doc->target->relations) && is_array($this->doc->target->relations)) {
            foreach ($this->doc->target->relations as $rel) {
                $ret[$rel->localProperty] = $rel;
            }
        }
        return $ret;
    }

    /**
     * Provides the role set defined in the service section.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = array();

         if (!empty($this->doc->service->roles)) {
             $roles = $this->doc->service->roles;
         }

        return $roles;
    }
}
