<?php

namespace Omnipay\BlueSnap\Test\Framework;

use Guzzle\Http\Message\Response;
use InvalidArgumentException;

class MockPlugin extends \Guzzle\Plugin\Mock\MockPlugin
{
    /**
     * Get a mock response from a file
     *
     * This extension adds supports for substitutions in the file, as described
     * in \Omnipay\BlueSnap\OmnipayBlueSnapTestCase::setMockHttpResponse
     *
     * @param string $path File to retrieve a mock response from
     * @param array<string, string> $substitutions default array()
     * @return Response|false
     * @throws InvalidArgumentException if the file is not found
     */
    public static function getMockFile($path, $substitutions = array())
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('Unable to open mock file: ' . $path);
        }

        $fileContents = file_get_contents($path) ?: '';
        foreach ($substitutions as $search => $replace) {
            $fileContents = str_replace('[' . $search . ']', $replace, $fileContents);
        }

        /**
         * @var Response|false
         */
        return Response::fromMessage($fileContents);
    }

    /**
     * Add a response to the end of the queue
     *
     * @param Response|string $response Response object or path to response file
     *
     * @return MockPlugin
     * @throws InvalidArgumentException if a string or Response is not passed or if a Response object cannot be created
     *                                  from the provided response file path.
     *
     * @psalm-suppress FailedTypeResolution because we want run time checks
     * @psalm-suppress RedundantConditionGivenDocblockType because we want run time checks on $response
     */
    public function addResponse($response)
    {
        if (!($response instanceof Response)) {
            if (!is_string($response)) {
                throw new InvalidArgumentException('Invalid response');
            }
            $response = self::getMockFile($response);

            if ($response === false) {
                throw new InvalidArgumentException('Unable to create a response object from the file path');
            }
        }

        $this->queue[] = $response;

        return $this;
    }
}
