<?php

namespace GinoPane\NanoRest\Response;

use GinoPane\NanoRest\Exceptions\ResponseContextException;

/**
 * Class JsonResponseContext
 *
 * Response context with JSON handling
 */
class JsonResponseContext extends ResponseContext
{
    /**
     * Get raw result data
     *
     * @param array $options
     *
     * @return string|null
     */
    public function getRaw(array $options = array()): ?string
    {
        return $this->getContent();
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
        $content = $this->getContent();

        if (is_null($content)) {
            return (array)$content;
        }

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
        $content = $this->getContent();

        if (is_null($content)) {
            return (object)$content;
        }

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
     * Makes sure that $content is valid for this AbstractResponseContext instance
     *
     * @param string $content
     *
     * @throws ResponseContextException
     *
     * @return bool
     */
    protected function assertIsValid(string $content): bool
    {
        json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();

            throw new ResponseContextException($error);
        }

        return true;
    }
}
