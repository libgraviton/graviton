<?php
namespace Graviton\GeneratorBundle\Definition;

use Graviton\GeneratorBundle\Definition\Schema\Constraint;
use Graviton\GeneratorBundle\Definition\Schema\Service;
use Graviton\SchemaBundle\Constraint\VersionServiceConstraint;

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
     * Returns the title
     *
     * @return string Title
     */
    public function getTitle()
    {
        return $this->def->getTitle();
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
     * Returns whether this service is versioning
     *
     * @return bool true if yes, false if not
     */
    public function isVersionedService()
    {
        if ($this->def->getService() === null || !$this->def->getService()->getVersioning()) {
            return false;
        }

        return (boolean) $this->def->getService()->getVersioning();
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
     * @return DefinitionElementInterface The field
     */
    public function getField($name)
    {
        $fields = $this->getFields();
        return isset($fields[$name]) ? $fields[$name] : null;
    }

    /**
     * Returns the field definition
     *
     * @return DefinitionElementInterface[] Fields
     */
    public function getFields()
    {
        $hierarchy = [];
        foreach ($this->def->getTarget()->getFields() as $field) {
            $hierarchy = array_merge_recursive(
                $hierarchy,
                $this->createFieldHierarchyRecursive($field, $field->getName())
            );
        }

        $fields = [];

        /*******
         * CONDITIONAL GENERATED FIELD AREA
         *
         * so simplify things, you can put fields here that should be conditionally created by Graviton.
         * @TODO refactor into a FieldBuilder* type of thing where different builders can add fields conditionally.
         */

        // Versioning field, for version control.
        if ($this->def->getService() && $this->def->getService()->getVersioning()) {
            $definition = new Schema\Field();
            $constraint = new Constraint();
            $constraint->setName('versioning');
            $definition->setName(VersionServiceConstraint::FIELD_NAME)->setTitle('Version')->setType('int')
                       ->setConstraints([$constraint])
                       ->setDescription('Document version. You need to send current version if you want to update.');
            $fields['version'] = $this->processSimpleField('version',  $definition);
        }

        /*******
         * add fields as defined in the definition file.
         */

        foreach ($hierarchy as $name => $definition) {
            $fields[$name] = $this->processFieldHierarchyRecursive($name, $definition);
        }

        return $fields;
    }

    /**
     * @param Schema\Field $definition Raw field definition
     * @param string       $path       Relative field path
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function createFieldHierarchyRecursive(Schema\Field $definition, $path)
    {
        if (!preg_match('/^(?P<name>[^\.]+)(\.(?P<sub>.+))?$/', $path, $matches)) {
            throw new \InvalidArgumentException(sprintf('Invalid field name "%s" definition', $definition->getName()));
        }

        $name = ctype_digit($matches['name']) ? "\x00array" : $matches['name'];
        if (isset($matches['sub'])) {
            $definition = $this->createFieldHierarchyRecursive($definition, $matches['sub']);
        } else {
            $definition = ["\x00field" => $definition];
        }

        return [$name => $definition];
    }

    /**
     * @param string $name Field name
     * @param array  $data Field data
     *
     * @return DefinitionElementInterface
     */
    private function processFieldHierarchyRecursive($name, $data)
    {
        // array field
        if (isset($data["\x00array"])) {
            return new JsonDefinitionArray(
                $name,
                $this->processFieldHierarchyRecursive($name, $data["\x00array"])
            );
        }

        // simple field
        if (array_keys($data) === ["\x00field"]) {
            return $this->processSimpleField($name, $data["\x00field"]);
        }


        // hash field
        $fields = [];
        $definition = null;
        foreach ($data as $subname => $subdata) {
            if ($subname === "\x00field") {
                $definition = $subdata;
            } else {
                $fields[$subname] = $this->processFieldHierarchyRecursive($subname, $subdata);
            }
        }
        return new JsonDefinitionHash($name, $this, $fields, $definition);
    }

    /**
     * @param string       $name       Field name
     * @param Schema\Field $definition Field
     *
     * @return DefinitionElementInterface
     */
    private function processSimpleField($name, Schema\Field $definition)
    {
        if (strpos($definition->getType(), 'class:') === 0) {
            $field = new JsonDefinitionRel($name, $definition, $this->getRelation($name));
        } else {
            $field = new JsonDefinitionField($name, $definition);
        }

        if (substr($definition->getType(), -2) === '[]') {
            $field = new JsonDefinitionArray($name, $field);
        }

        return $field;
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
     * Get relation by field name
     *
     * @param string $field Field name
     * @return Schema\Relation|null
     */
    private function getRelation($field)
    {
        $relations = $this->getRelations();
        return isset($relations[$field]) ? $relations[$field] : null;
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

    /**
     * Can record origin be modified
     *
     * @return bool
     */
    public function isRecordOriginModifiable()
    {
        $retVal = false;
        if ($this->isRecordOriginFlagSet()) {
            $retVal = $this->def->getService()->getRecordOriginModifiable();
        }

        return $retVal;
    }

    /**
     * check if the RecordOriginModifiable flag is set
     *
     * @return bool
     */
    public function isRecordOriginFlagSet()
    {
        $retVal = false;
        if ($this->def->getService() !== null
            && is_object($this->def->getService())
            && $this->def->getService()->getRecordOriginModifiable() !== null) {
            $retVal = true;
        }

        return $retVal;
    }

    /**
     * @return string
     */
    public function getServiceCollection()
    {
        $collectionName = $this->getId();

        if ($this->def->getService() instanceof Service
            && $this->def->getService()->getCollectionName()) {

            $collectionName = $this->def->getService()->getCollectionName();
        }

        return $collectionName;
    }

    /**
     * @return string[]
     */
    public function getIndexes()
    {
        $indexes = [];
        if ($this->def->getTarget()->getIndexes()) {
            $indexes = $this->def->getTarget()->getIndexes();
        }
        return $indexes;
    }

    /**
     * @return string[]
     */
    public function getSearchables()
    {
        $indexes = [];
        if ($fields = $this->def->getTarget()->getFields()) {
            foreach ($fields as $field) {
                if ($value = (int) $field->getSearchable()) {
                    $indexes[$field->getName()] = $value;
                }
            }
        }
        return $indexes;
    }

    /**
     * @return string[]
     */
    public function getTextIndexes()
    {
        $indexes = [];
        if ($keys = $this->def->getTarget()->getTextIndexes()) {
            foreach ($keys as $key) {
                if ($value = (int) $key['weight']) {
                    $indexes[$key['field']] = $value;
                }
            }
        }
        return $indexes;
    }

    /**
     * Combine in one array the Search text indexes
     *
     * @return array
     */
    public function getAllTextIndexes()
    {
        return array_merge(
            $this->getSearchables(),
            $this->getTextIndexes()
        );
    }
}
