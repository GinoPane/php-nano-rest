<?php

namespace GinoPane\NanoRest\Response;

/**
 * Class JsonResponseContext
 *
 * Response context with JSON handling
 *
 * @package GinoPane\NanoRest\Response
 */
class JsonResponseContext extends ResponseContext
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
        return json_decode($this->content, true);
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
        return json_decode($this->content, false);
    }

    /**
     * String representation of response for debug purposes
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode(json_decode($this->content), JSON_PRETTY_PRINT);
    }

    /**
     * Checks whether the passed JSON string is valid
     *
     * @param string $content
     * @param string $error
     * @return bool
     */
    public function isValid(string $content, string &$error)
    {
        @json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();

            return false;
        }

        return true;
    }
}
