<?php
/**
 * authenticator test
 */

namespace Graviton\Tests\Rest\Authenticator;

use Graviton\SecurityBundle\Authenticator\UserHeaderAuthenticator;
use Graviton\SecurityBundle\Entities\AnonymousUser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class UserHeaderAuthenticatorTest extends TestCase
{

    /**
     * test
     *
     * @dataProvider handlingData
     *
     * @param array  $server         server
     * @param bool   $allowAnonymous anon
     * @param string $userBadge      user
     * @param string $exp            exp
     *
     * @return void
     */
    public function testHandling($server, $allowAnonymous, $userBadge = null, $exp = null)
    {
        $sut = new UserHeaderAuthenticator(
            $this->getMockForAbstractClass(LoggerInterface::class),
            'x-user',
            $allowAnonymous
        );

        $request = new Request([], [], [], [], [], $server);

        $this->assertTrue($sut->supports($request));

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
                ],
                true,
                AnonymousUser::USERNAME
            ],
            'normal-handling' => [
                [
                    'HTTP_X-USER' => 'test10'
                ],
                true,
                'test10'
            ],
            'normal-handling-no-anon' => [
                [
                    'HTTP_X-USER' => 'test11'
                ],
                false,
                'test11'
            ],
            'no-anonymous' => [
                [
                ],
                false,
                null,
                CustomUserMessageAuthenticationException::class
            ]
        ];
    }
}
