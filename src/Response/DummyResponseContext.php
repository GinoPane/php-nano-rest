<?php

namespace GinoPane\NanoRest\Response;

/**
 * Class DummyResponseContext
 *
 * Dummy result context, can be used as default
 *
 * @package GinoPane\NanoRest\Response
 */
class DummyResponseContext extends ResponseContext
{
    /**
     * Get raw result data
     *
     * @param array $options
     *
     * @return string
     */
    public function getRaw(array $options = array()): string
    {
        return $this->content;
    }

    /**
     * Get result data as array
     *
     * @param array $options
     *
     * @return array
     */
    public function getArray(array $options = array()): array
    {
        return (array)$this->content;
    }

    /**
     * Get result data as object
     *
     * @param array $options
     *
     * @return mixed
     */
    public function getObject(array $options = array())
    {
        return (object)$this->content;
    }

    /**
     * String representation of response for debug purposes
     *
     * @return string
     */
    public function __toString(): string
    {
        return print_r($this->content, true);
    }

    /**
     * Dummy test, everything is valid for this context
     *
     * @param string $content
     * @param string $error
     *
     * @return bool
     */
    public function isValid(string $content, string &$error): bool
    {
        return true;
    }
}
