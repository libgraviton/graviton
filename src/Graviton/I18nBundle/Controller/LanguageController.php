<?php
/**
 * Language controller
 */

namespace Graviton\I18nBundle\Controller;

use Graviton\I18nBundle\Service\I18nCacheUtils;
use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Language controller
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LanguageController extends RestController
{

    /**
     * @var I18nCacheUtils
     */
    private $utils;

    /**
     * sets I18nCacheUtils
     *
     * @param I18nCacheUtils $cacheUtils cache utils
     *
     * @return void
     */
    public function setUtils(I18nCacheUtils $cacheUtils)
    {
        $this->utils = $cacheUtils;
    }

    /**
     * Writes a new Entry to the database
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of action with data (if successful)
     */
    public function postAction(Request $request)
    {
        $this->invalidateLanguageCache();
        return parent::postAction($request);
    }

    /**
     * Update a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @throws MalformedInputException
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function putAction($id, Request $request)
    {
        $this->invalidateLanguageCache();
        return parent::putAction($id, $request);
    }

    /**
     * Deletes a record
     *
     * @param Number $id ID of record
     *
     * @return Response $response Result of the action
     */
    public function deleteAction($id)
    {
        $this->invalidateLanguageCache();
        return parent::deleteAction($id);
    }

    /**
     * delete the Language cache
     *
     * @return void
     */
    private function invalidateLanguageCache()
    {
        $this->utils->clearLanguages();
    }
}
