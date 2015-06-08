<?php
/**
 * form type for translatable fields
 */

namespace Graviton\I18nBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TranslatableType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable';
    }
}
