<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileControllerTestRestrictedConditional extends FileControllerTestRestricted
{

    /**
     * custom environment
     *
     * @var string
     */
    protected $environment = 'test_restricted_conditional';

    /**
     * custom client options
     *
     * @var string[]
     */
    protected $clientOptions = ['environment' => 'test_restricted_conditional'];
}
