<?php
/**
 * creates mongodb views from analytics definition
 */

namespace Graviton\AnalyticsBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AnalyticsBase\Pipeline\PipelineAbstract;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticsCreateViewCommand extends Command
{
    /**
     * @var DocumentManager
     */
    private $manager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var array
     */
    private $services;

    /**
     * @param DocumentManager $manager      manager
     * @param string          $databaseName database name
     * @param array           $services     services
     */
    public function __construct(
        DocumentManager $manager,
        $databaseName,
        array $services
    ) {
        $this->manager = $manager;
        $this->databaseName = $databaseName;
        $this->services = $services;
        parent::__construct();
    }

    /**
     * set up command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('graviton:analytics:create-views')
            ->setDescription(
                'Create views as defined in analytics definitions'
            );
    }

    /**
     * run command
     *
     * @param InputInterface  $input  input interface
     * @param OutputInterface $output output interface
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mongoClient = $this->manager->getConnection()
                                     ->getMongoClient()
                                     ->getClient();
        $db = $mongoClient->selectDatabase($this->databaseName);

        foreach ($this->services as $service) {
            if (!isset($service['class']) ||
                !isset($service['createView']) ||
                (isset($service['createView']) && $service['createView'] !== true)
            ) {
                continue;
            }

            $classes = $service['class'];
            if (!is_array($classes)) {
                $classes = [$classes];
            }

            foreach ($classes as $name => $class) {
                $inst = new $class();
                if ($inst instanceof PipelineAbstract) {
                    $inst->setParams(['forView' => 'yes']);

                    if (!is_numeric($name)) {
                        if (!isset($service['collection'][$name])) {
                            $output->writeln(
                                "ERROR on class " . $class . ", could not determine collection for '" . $name . "'"
                            );
                            continue;
                        }
                        $collectionName = $service['collection'][$name];
                    } else {
                        $collectionName = $service['collection'];
                    }

                    $classReflect = new \ReflectionClass($class);
                    $viewName = 'AnalyticsView' . $classReflect->getShortName();

                    // drop the view..
                    $db->dropCollection($viewName);

                    $db->command(
                        [
                            'create' => $viewName,
                            'viewOn' => $collectionName,
                            'pipeline' => $inst->get()
                        ]
                    );

                    $output->writeln(
                        'Created MongoDB analytics view "' . $viewName . '" on collection "' . $collectionName . '"'
                    );

                    // field spec?
                    if (isset($service['exportFields']) && is_array($service['exportFields'])) {
                        $this->createFieldSpecCollection($viewName, $service['exportFields'], $db);
                        $output->writeln(
                            'Saved field spec for "' . $viewName . '"'
                        );
                    }
                }
            }
        }
    }

    /**
     * creates the field spec in a separate collection
     *
     * @param string   $viewName  view name
     * @param array    $fieldSpec field spec
     * @param Database $db        db
     *
     * @return void
     */
    private function createFieldSpecCollection($viewName, $fieldSpec, Database $db)
    {
        $collectionName = $viewName.'FieldSpec';
        $collection = $db->selectCollection($collectionName);

        $collection->deleteMany([]);

        foreach ($fieldSpec as $field) {
            $collection->insertOne($field);
        }
    }
}
