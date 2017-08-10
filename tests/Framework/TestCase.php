<?php

namespace Omnipay\BlueSnap\Test\Framework;

use ReflectionObject;
use Guzzle\Http\Message\RequestInterface as GuzzleRequestInterface;
use Guzzle\Common\Event;
use Guzzle\Http\Message\Response;

class TestCase extends \Omnipay\Tests\TestCase
{
    /**
     * This has to be added since the Omnipay TestCase declares private variables
     * instead of protected variables.
     *
     * @var array
     */
    protected $substitutableMockHttpRequests = array();

    /**
     * Overriding to provide return type
     *
     * @return \Guzzle\Http\Client
     */
    public function getHttpClient()
    {
        /**
         * @var \Guzzle\Http\Client
         */
        return parent::getHttpClient();
    }

    /**
     * Overriding to provide return type
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getHttpRequest()
    {
        /**
         * @var \Symfony\Component\HttpFoundation\Request
         */
        return parent::getHttpRequest();
    }

    /**
     * Get a mock response for a client by mock file name. Overrides the Omnipay
     * default to add support for substitutions, as described in setMockHttpResponse.
     *
     * @param string $path
     * @param array<string, string> $substitutions
     * @return Response|false
     */
    public function getMockHttpResponse($path, $substitutions = array())
    {
        if ($path instanceof Response) {
            return $path;
        }

        $ref = new ReflectionObject($this);
        $dir = dirname($ref->getFileName() ?: '');

        $fullPath = $dir . '/Mock/' . $path;
        // if mock file doesn't exist, check parent directory
        if (!file_exists($fullPath) && file_exists($dir . '/../Mock/' . $path)) {
            $fullPath = $dir . '/../Mock/' . $path;
        }

        return MockPlugin::getMockFile($fullPath, $substitutions);
    }

    /**
     * Set a mock response from a mock file on the next client request.
     *
     * This method assumes that mock response files are located under the
     * tests/Mock/ subdirectory. A mock response is added to the next
     * request sent by the client.
     *
     * An array of path can be provided and the next x number of client requests are
     * mocked in the order of the array where x = the array length.
     *
     * This is an override of the default Omnipay function that adds support for
     * setting an array of substitutions that can be used for randomizing test data.
     * For example, if $substitutions is array('NAME' => 'Fake Name'),
     * then the function will replace all instances of '[NAME]' in the
     * response with 'Fake Name'. Substitutions are not required.
     *
     * @param array<string>|string $paths
     * @param array<string, string> $substitutions
     *
     * @return MockPlugin
     */
    public function setMockHttpResponse($paths, $substitutions = array())
    {
        $this->substitutableMockHttpRequests = array();
        $that = $this;
        $mock = new MockPlugin(null, true);
        $this->getHttpClient()->getEventDispatcher()->removeSubscriber($mock);
        $mock->getEventDispatcher()->addListener(
            'mock.request',
            // @codingStandardsIgnoreStart
            /**
             * @param Event $event
             * @return void
             */
            function (Event $event) use ($that) {
                // @codingStandardsIgnoreEnd
                /**
                 * @var \Guzzle\Http\Message\Request
                 */
                $request = $event->offsetGet('request');
                $that->addMockedHttpRequest($request);
            }
        );

        if (is_string($paths)) {
            $paths = array($paths);
        }
        foreach ($paths as $path) {
            $mock->addResponse($this->getMockHttpResponse($path, $substitutions) ?: '');
        }

        $this->getHttpClient()->getEventDispatcher()->addSubscriber($mock);

        return $mock;
    }

    /**
     * Mark a request as being mocked
     *
     * @param GuzzleRequestInterface $request
     * @return static
     */
    public function addMockedHttpRequest(GuzzleRequestInterface $request)
    {
        $this->substitutableMockHttpRequests[] = $request;
        return $this;
    }


    /**
     * Get all of the mocked requests
     *
     * @return array
     */
    public function getMockedRequests()
    {
        return $this->substitutableMockHttpRequests;
    }
}
