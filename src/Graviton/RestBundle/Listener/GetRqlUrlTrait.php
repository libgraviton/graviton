<?php
/**
 * The logic used to make an url for use in a Link header that contains rql
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
trait GetRqlUrlTrait
{
    /**
     * @param string $url url with rql query that needs to be sanitized
     *
     * @return string
     */
    protected function getRqlUrl(Request $request, $url)
    {
        if ($request->attributes->get('hasRql', false)) {
            $rawRql = $request->attributes->get('rawRql');

            $url = str_replace('q=' . urlencode($rawRql), 'q=' . str_replace(',', '%2C', $rawRql), $url);
        }
        return $url;
    }
}
