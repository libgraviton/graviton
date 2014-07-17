<?php

namespace Graviton\GeneratorBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Graviton\BundleBundle\GravitonBundleInterface;

/**
 * change the code of a GravitonBundleInterface based bundle
 *
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
     * @param string $bundle bundle class name
     *
     * @return boolean
     *
     * @throws  \RuntimeException If bundle is already defined
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

        var_dump($lines);
        // Don't add same bundle twice
        if (false !== strpos(implode('', $lines), $shortName)) {
            throw new \RuntimeException(sprintf('Bundle "%s" is already defined in "AppKernel::registerBundles()".', $shortName));
        }

        $this->setCode(token_get_all('<?php '.implode('', $lines)), $method->getStartLine());
        while ($token = $this->next()) {
            // $bundles
            if (T_VARIABLE !== $token[0] || '$bundles' !== $token[1]) {
                continue;
            }

            // =
            $this->next();

            // array
            $token = $this->next();
            if (T_ARRAY !== $token[0]) {
                return false;
            }

            // add the bundle at the end of the array
            while ($token = $this->next()) {
                // look for );
                if (')' !== $this->value($token)) {
                    continue;
                }

                if (';' !== $this->value($this->peek())) {
                    continue;
                }

                // ;
                $this->next();

                $lines = array_merge(
                    array_slice($src, 0, $this->line - 2),
                    // Appends a separator comma to the current last position of the array
                    array(rtrim(rtrim($src[$this->line - 2]), ',') . ",\n"),
                    array(sprintf("            new %s(),\n", $bundle)),
                    array_slice($src, $this->line - 1)
                );

                file_put_contents($this->reflected->getFilename(), implode('', $lines));

                return true;
            }
        }

    }
}
