<?php
/**
 * basic app model
 */

namespace Graviton\TaxonomyBundle\Model;

use Graviton\RestBundle\Model\Doctrine\ODM as Model;
use Graviton\TaxonomyBundle\Repository\CountryRepository;

/**
 * Model based on Graviton\RestBundle\Model\Doctrine\ODM.
 *
 * For now this gets a repository through constructor
 * injection. This needs to be changed properly according
 * to what we end up doing with the rest of Graviton\RestBundle\Model.
 *
 * @category GravitonTaxonomyBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class Country extends Model
{
    /**
     * create new app model
     *
     * @param CountryRepository $countries Repository for constructor injection
     *
     * @return void
     */
    public function __construct(CountryRepository $countries)
    {
        $this->repository = $countries;
    }
}
