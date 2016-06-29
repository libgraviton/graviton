<?php
/**
 * generate params array for various calls
 */

namespace Graviton\GeneratorBundle\Generator\ResourceGenerator;

use Symfony\Component\DependencyInjection\Container;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ParameterBuilder
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param string $name  parameter name
     * @param mixed  $value parameter to set
     *
     * @return self
     */
    public function setParameter($name, $value)
    {
        if ($name === 'basename') {
            $this->parameters['bundle_basename'] = $value;
            $this->parameters['extension_alias'] = Container::underscore($value);
        } elseif ($name === 'json') {
            $this->parameters['json'] = $value;
            // if we have data for id field, pass it along
            $idField = $value->getField('id');
            if (!is_null($idField)) {
                $this->parameters['idField'] = $idField->getDefAsArray();
            } else {
                // if there is a json file and no id defined - so we don't do one here..
                // we leave it in the document though but we don't wanna output it..
                $this->parameters['noIdField'] = true;
            }
            $this->parameters['parent'] = $value->getParentService();
        } elseif ($name === 'recordOriginModifiable' && $value !== null) {
            $this->parameters[$name] = ($value) ? 'true' : 'false';
        } else {
            $this->parameters[$name] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
