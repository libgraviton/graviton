<?php
/**
 * authenticator test
 */

namespace Graviton\SecurityBundle\Tests\Authenticator;

use Graviton\SecurityBundle\Authenticator\SameSubnetAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SameSubnetAuthenticatorTest extends TestCase
{

    /**
     * test
     *
     * @dataProvider handlingData
     *
     * @param array  $server    server
     * @param string $subnet    subnet
     * @param bool   $supports  supports
     * @param string $userBadge user
     * @param string $exp       exp
     *
     * @return void
     */
    public function testHandling($server, $subnet, $supports, $userBadge = null, $exp = null)
    {
        Request::setTrustedProxies(['172.0.0.1'], Request::HEADER_FORWARDED);
        $request = new Request([], [], [], [], [], $server);

        $sut = new SameSubnetAuthenticator($subnet, 'x-client');
        $this->assertEquals($supports, $sut->supports($request));

        if (!$supports) {
            return;
        }

        if (!is_null($exp)) {
            $this->expectException($exp);
        }

        $passport = $sut->authenticate($request);

        if (!is_null($userBadge)) {
            $this->assertEquals(
                $userBadge,
                $passport->getBadge(UserBadge::class)->getUserIdentifier()
            );
        }
    }

    /**
     * data provider
     *
     * @return array[] data
     */
    public static function handlingData(): array
    {
        return [
            'no-handling' => [
                [
                    'REMOTE_ADDR' => '127.0.0.1'
                ],
                '127.0.0.1',
                false
            ],
            'normal-handling' => [
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'HTTP_X-CLIENT' => 'test22'
                ],
                '127.0.0.1',
                true,
                'test22'
            ],
            'normal-handling-reverse-proxy' => [
                [
                    'REMOTE_ADDR' => '172.0.0.1',
                    'HTTP_FORWARDED' => 'by=172.0.0.1;for=192.168.1.0;host=testl.local;proto=http',
                    'HTTP_X-CLIENT' => 'test33'
                ],
                '192.168.1.0/16',
                true,
                'test33'
            ],
            'not-matching-handling' => [
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'HTTP_X-CLIENT' => 'test23'
                ],
                '128.0.0.1',
                true,
                null,
                CustomUserMessageAuthenticationException::class
            ]
        ];
    }
}
