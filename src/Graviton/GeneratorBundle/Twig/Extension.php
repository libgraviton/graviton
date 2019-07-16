<?php
/**
 * twig extension
 */

namespace Graviton\GeneratorBundle\Twig;

use Graviton\CoreBundle\Util\CoreUtils;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Twig\Extension\ExtensionInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Extension implements ExtensionInterface
{

    /**
     * @var array
     */
    private $exposeSyntheticMap;

    /**
     * Extension constructor.
     *
     * @param array $exposeSyntheticMap setting when to expose synthetic fields
     */
    public function __construct($exposeSyntheticMap = null)
    {
        $this->exposeSyntheticMap = $exposeSyntheticMap;
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return [];
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors()
    {
        return [];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return TwigTest[]
     */
    public function getTests()
    {
        return [];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            // to determine if a given service wants to expose its synthetic fields
            new TwigFunction(
                'exposeSyntheticFields',
                function (JsonDefinition $json) {
                    return CoreUtils::subjectMatchesStringWildcards($this->exposeSyntheticMap, $json->getRouterBase());
                }
            )
        ];
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array<array> First array of unary operators, second array of binary operators
     */
    public function getOperators()
    {
        return [];
    }
}
