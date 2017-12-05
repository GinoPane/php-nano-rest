<?php

namespace GinoPane\NanoRest\Request;

use Closure;

/**
 * Class HttpBuildQueryBehavior
 *
 * By default http_build_query encodes arrays using php square brackets syntax,
 * which sometimes won't work for some endpoints which would expect simple
 * duplication of keys
 *
 * @link https://stackoverflow.com/questions/6243051/how-to-pass-an-array-within-a-query-string
 *
 * @author Sergey <Gino Pane> Karavay
 */
trait HttpBuildQueryBehavior
{
    /**
     * Set to true
     *
     * @var bool
     */
    private $encodeArraysUsingDuplication = false;

    /**
     * @var Closure
     */
    private $httpQueryCustomProcessor = null;

    /**
     * @return Closure|null
     */
    public function getHttpQueryCustomProcessor(): ?Closure
    {
        return $this->httpQueryCustomProcessor;
    }

    /**
     * @param Closure $httpQueryCustomProcessor
     *
     * @return HttpBuildQueryBehavior|static
     */
    public function setHttpQueryCustomProcessor(Closure $httpQueryCustomProcessor): self
    {
        $this->httpQueryCustomProcessor = $httpQueryCustomProcessor;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEncodeArraysUsingDuplication(): bool
    {
        return $this->encodeArraysUsingDuplication;
    }

    /**
     * @param bool $encodeArraysUsingDuplication
     *
     * @return HttpBuildQueryBehavior|static
     */
    public function setEncodeArraysUsingDuplication(bool $encodeArraysUsingDuplication): self
    {
        $this->encodeArraysUsingDuplication = $encodeArraysUsingDuplication;

        return $this;
    }

    /**
     * Wrapper for http_build_query
     *
     * @param array $data
     *
     * @return string
     */
    private function httpBuildQuery(array $data): string
    {
        $queryString = http_build_query($data, '', '&');

        return $this->postProcessHttpQueryString($queryString, $data);
    }

    /**
     * @param string $query
     * @param array $data
     *
     * @return string
     */
    private function postProcessHttpQueryString(string $query, array $data): string
    {
        if ($this->getEncodeArraysUsingDuplication()) {
            $query = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $query);
        }

        if (!is_null($processor = $this->getHttpQueryCustomProcessor())) {
            $query = $processor($query, $data);
        }

        return $query;
    }
}
