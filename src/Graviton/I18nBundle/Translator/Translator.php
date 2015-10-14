<?php
/**
 * a small child of the symfony Translator in order to load more dynamically
 */

namespace Graviton\I18nBundle\Translator;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Translator extends BaseTranslator {

    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
    {

        print_r($options);
        parent::__construct($container, $selector, $loaderIds, $options);
        print_r($this->options);
        die;

    }

    protected function initialize()
    {
        print_r($this->loaderIds);
        die;
        parent::initialize();
    }

}
