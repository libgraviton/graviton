<?php
/**
 * change the code of a GravitonBundleInterface based bundle
 */

namespace Graviton\GeneratorBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * change the code of a GravitonBundleInterface based bundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BundleBundleManipulator extends Manipulator
{
    protected $bundle;
    protected $reflected;

    /**
     * constructor
     *
     * @param GravitonBundleInterface $bundle A GravitonBundleInterface instance
     *
     * @return BundleBundleManipulator
     */
    public function __construct(GravitonBundleInterface $bundle)
    {
        $this->bundle = $bundle;
        $this->reflected = new \ReflectionObject($bundle);
    }

    /**
     * adds a bundle at the end of the existing bundles
     *
     * The number magic in this method is based on the AppKernel manipulator
     * in SensioDistributionBundle. While it's not a nice solution it seems
     * to do the job and I'm having a hard time finding a solution thats not
     * just as insane as this is. We'll probably end up refactoring this
     * if it ever make any problems again.
     *
     * @param string $bundle bundle class name
     *
     * @return boolean
     *
     * @throws \RuntimeException If bundle is already defined
     */
    public function addBundle($bundle)
    {
        if (!$this->reflected->getFilename()) {
            return false;
        }
        $classReflection = new \ReflectionClass($bundle);
        $shortName = $classReflection->getShortName();

        $src = file($this->reflected->getFilename());
        $method = $this->reflected->getMethod('getBundles');
        $lines = array_slice($src, $method->getStartLine() + 2, $method->getEndLine() - $method->getStartLine() - 4);
        $namespaces = array_slice($src, 7, $this->reflected->getEndLine() - $this->reflected->getStartLine() - 12);

        // Don't add same bundle twice
        if (false !== strpos(implode('', $lines), $shortName)) {
            throw new \RuntimeException(
                sprintf(
                    'Bundle "%s" is already defined in "AppKernel::registerBundles()".',
                    $shortName
                )
            );
        }

        $namespaces[] = "use ${bundle};\n";
        $lines[] = "            new ${shortName}(),\n";
        $lines = array_merge(
            array_slice($src, 0, 7),
            $namespaces,
            array_slice(
                $src,
                $this->reflected->getEndLine() - $this->reflected->getStartLine() - 5,
                $method->getStartLine() - count($lines) - 6
            ),
            $lines,
            array_slice($src, $method->getEndLine() - 2)
        );

        // @todo use symfony to write files
        file_put_contents($this->reflected->getFilename(), implode('', $lines));

        return true;
    }
}
