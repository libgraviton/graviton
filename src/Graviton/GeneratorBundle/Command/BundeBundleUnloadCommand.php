<?php
/**
 * remove stuff from BundleBundle dynamically from ENV
 *
 * if you define an ENV that has the name
 * DYN_GROUP_[HANS]=somepattern
 *
 * and then you define an ENV named = DYN_HAS_HANS=false
 * then all bundles matching the name "somepattern" will be removed from the bundlebundle
 */

namespace Graviton\GeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BundeBundleUnloadCommand extends Command
{

    /**
     * start string in the generated bundlebundle
     *
     * @var string
     */
    private $startString = '/* START BUNDLE LIST */';

    /**
     * end string in the generated bundlebundle
     *
     * @var string
     */
    private $endString = '/* END BUNDLE LIST */';

    /**
     * prefix of the group defining ENV
     *
     * @var string
     */
    private $envDynGroupPrefix = 'DYN_GROUP_';

    /**
     * prefix of the disabling defining ENV
     *
     * @var string
     */
    private $envDynSettingPrefix = 'DYN_HAS_';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            'baseDir',
            InputArgument::REQUIRED,
            'GravitonDyn base dir'
        )
        ->setName('graviton:generate:bundlebundleunload')
        ->setDescription(
            'Remove stuff from DynBundleBundle depending on ENV'
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = Finder::create()
            ->in($input->getArgument('baseDir'))
            ->name('GravitonDynBundleBundle.php');

        $files = iterator_to_array($finder);

        if (empty($files)) {
            throw new \LogicException('Could not locate BundleBundle source file.');
        }

        $bundleFile = array_pop($files);
        $bundleFilePath = $bundleFile->getPathname();
        $content = file_get_contents($bundleFilePath);

        $contentData = $this->getContentData($content);

        $bundleList = explode(',', $contentData['bundleList']);
        if (empty($bundleList)) {
            return;
        }

        $newBundleList = $this->filterBundleList($bundleList);

        // all the same? don't do anything..
        if (count($bundleList) == count($newBundleList)) {
            return;
        }

        $output->writeln(
            'Writing new DynBundleBundle file, now at '.count($newBundleList).
            ' bundles, before we had '.count($bundleList)
        );

        file_put_contents(
            $bundleFilePath,
            $this->getNewContent($content, $newBundleList)
        );
    }

    /**
     * gets our positions and the bundlelist
     *
     * @param string $content the bundlebundle content
     *
     * @return array data
     */
    private function getContentData($content)
    {
        $res = [];
        $res['startStringPos'] = strpos($content, $this->startString) + strlen($this->startString);
        $res['endStringPos'] = strpos($content, $this->endString);
        $res['bundleList'] = substr($content, $res['startStringPos'], ($res['endStringPos'] - $res['startStringPos']));
        return $res;
    }

    /**
     * replaces the stuff in the content
     *
     * @param string $content       the bundlebundle content
     * @param array  $newBundleList new bundle list
     *
     * @return string new content
     */
    private function getNewContent($content, array $newBundleList)
    {
        $contentData = $this->getContentData($content);

        $beforePart = substr($content, 0, $contentData['startStringPos']);
        $endPart = substr($content, $contentData['endStringPos']);

        return $beforePart.PHP_EOL.implode(','.PHP_EOL, $newBundleList).PHP_EOL.$endPart;
    }

    /**
     * filters the bundlelist according to ENV vars
     *
     * @param array $bundleList bundle list
     *
     * @return array filtered bundle list
     */
    private function filterBundleList(array $bundleList)
    {
        $groups = [];
        foreach ($_ENV as $name => $val) {
            if (substr($name, 0, strlen($this->envDynGroupPrefix)) == $this->envDynGroupPrefix) {
                $groups[substr($name, strlen($this->envDynGroupPrefix))] = $val;
            }
        }

        if (empty($groups)) {
            // no groups.. exit
            return $bundleList;
        }

        foreach ($groups as $groupName => $filter) {
            if (isset($_ENV[$this->envDynSettingPrefix.$groupName]) &&
                $_ENV[$this->envDynSettingPrefix.$groupName] == 'false'
            ) {
                $bundleList = array_filter(
                    $bundleList,
                    function ($value) use ($filter) {
                        if (preg_match('/'.$filter.'/i', $value) || empty(trim($value))) {
                            return false;
                        }
                        return true;
                    }
                );
            }
        }

        return $bundleList;
    }
}
