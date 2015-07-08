<?php
namespace Graviton\GeneratorBundle\Definition;

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
     * Schema
     *
     * @var Schema\Definition
     */
    private $def;

    /**
     * Composed namespace of this definition, must be explicitly set
     *
     * @var string
     */
    private $namespace;

    /**
     * Constructor
     *
     * @param Schema\Definition $definition
     */
    public function __construct(Schema\Definition $definition)
    {
        $this->def = $definition;
    }

    /**
     * @return Schema\Definition
     */
    public function getDef()
    {
        return $this->def;
    }

    /**
     * Returns this loads ID
     *
     * @return string ID
     */
    public function getId()
    {
        if ($this->def->getId() === null) {
            throw new \RuntimeException('No id found for document');
        }

        return $this->def->getId();
    }

    /**
     * Returns the description
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->def->getDescription();
    }

    /**
     * Returns whether this definition requires the generation
     * of a controller. normally yes, but sometimes not ;-)
     *
     * @return bool true if yes, false if no
     */
    public function hasController()
    {
        return $this->def->getService() !== null &&
            $this->def->getService()->getRouterBase() !== null;
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
        return $this->def->getIsSubDocument();
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
        if ($this->def->getService() === null) {
            return false;
        }

        return $this->def->getService()->getReadOnly();
    }

    /**
     * Returns whether this service has fixtures
     *
     * @return bool true if yes, false if not
     */
    public function hasFixtures()
    {
        return count($this->getFixtures()) > 0;
    }

    /**
     * Returns the fixtures or empty array if none
     *
     * @return array fixtures
     */
    public function getFixtures()
    {
        if ($this->def->getService() === null) {
            return [];
        }

        return $this->def->getService()->getFixtures();
    }

    /**
     * Returns the order number at which order this fixture should be loaded.
     * this is needed if we have relations/references between the fixtures..
     *
     * @return int order
     */
    public function getFixtureOrder()
    {
        if ($this->def->getService() === null ||
            $this->def->getService()->getFixtureOrder() === null) {
            return 100;
        }

        return $this->def->getService()->getFixtureOrder();
    }

    /**
     * Returns a router base path. false if default should be used.
     *
     * @return string router base, i.e. /bundle/name/
     */
    public function getRouterBase()
    {
        if ($this->def->getService() === null ||
            $this->def->getService()->getRouterBase() === null) {
            return false;
        }

        $routerBase = $this->def->getService()->getRouterBase();
        if (substr($routerBase, 0, 1) !== '/') {
            $routerBase = '/' . $routerBase;
        }
        if (substr($routerBase, -1) === '/') {
            $routerBase = substr($routerBase, 0, -1);
        }

        return $routerBase;
    }

    /**
     * Returns the Controller classname this services' controller shout inherit.
     * Defaults to the RestController of the RestBundle of course.
     *
     * @return string base controller
     */
    public function getBaseController()
    {
        if ($this->def->getService() === null ||
            $this->def->getService()->getBaseController() === null) {
            return 'RestController';
        }

        return $this->def->getService()->getBaseController();
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
        if ($this->def->getService() === null ||
            $this->def->getService()->getParent() === null) {
            return 'graviton.rest.controller';
        }

        return $this->def->getService()->getParent();
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
        foreach ($this->getFields() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Returns the field definition
     *
     * @return JsonDefinitionField[] Fields
     */
    public function getFields()
    {
        if ($this->def->getTarget() === null) {
            return [];
        }

        $result = [];

        $fields = [];
        $relations = $this->getRelations();

        foreach ($this->def->getTarget()->getFields() as $field) {
            $field = new JsonDefinitionField($field);
            $fields[$field->getName()] = $field;

            // embed rel?
            if (isset($relations[$field->getName()])) {
                if ($relations[$field->getName()]->getType() === 'embed') {
                    $field->setRelType(JsonDefinitionField::REL_TYPE_EMBED);
                }
            }
        }

        // object generation (dot-notation parsing)
        $fieldHierarchy = [];
        $arrayHashes = [];
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
                $result[$fieldName] = $field;
            }
        }

        foreach ($fieldHierarchy as $fieldName => $subElements) {
            $hashField = new JsonDefinitionHash(
                $fieldName,
                $subElements
            );
            $hashField->setParent($this);
            $hashField->setRelType(JsonDefinitionHash::REL_TYPE_EMBED);

            if (in_array($fieldName, $arrayHashes)) {
                $hashField->setIsArrayHash(true);
            }
            $result[$fieldName] = $hashField;
        }

        return $result;
    }

    /**
     * Get target relations which are explictly defined
     *
     * @return Schema\Relation[] relations
     */
    public function getRelations()
    {
        if ($this->def->getTarget() === null) {
            return [];
        }

        $relations = [];
        foreach ($this->def->getTarget()->getRelations() as $relation) {
            $relations[$relation->getLocalProperty()] = $relation;
        }

        return $relations;
    }

    /**
     * Provides the role set defined in the service section.
     *
     * @return array
     */
    public function getRoles()
    {
        if ($this->def->getService() === null) {
            return [];
        }

        return $this->def->getService()->getRoles();
    }
}
