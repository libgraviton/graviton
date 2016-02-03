<?php
/** A compiler pass that passes $_SERVER/$_ENV vars into the container parameters bag */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EnvParametersCompilerPass implements CompilerPassInterface
{

    /**
     * this is a workaround for a new symfony feature:
     * https://github.com/symfony/symfony/issues/7555
     *
     * we *need* to be able to override any param with our env variables..
     * so we do again, what the kernel did already here.. ;-)
     *
     * Since fabpot seems to have said bye to this feature we are
     * re-implementing it here. We are also adding some fancy json
     * parsing for hashes and arrays while at it.
     *
     * @todo add proper documentation on this "feature" to a README
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        foreach (array_merge($_SERVER, $_ENV) as $key => $value) {
            if (0 === strpos($key, 'SYMFONY__')) {
                if (substr($value, 0, 1) == '[' || substr($value, 0, 1) == '{') {
                    $value = json_decode($value, true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \RuntimeException(
                            sprintf('error "%s" in env variable "%s"', json_last_error_msg(), $key)
                        );
                    }
                }
                $container->setParameter(strtolower(str_replace('__', '.', substr($key, 9))), $value);
            }
        }
    }
}
