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
    public function getRaw(array $options = array())
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
    public function getArray(array $options = array())
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
    public function __toString()
    {
        return json_encode(json_decode($this->content), JSON_PRETTY_PRINT);
    }

    /**
     * Checks whether the passed JSON string is valid. RegExp testing has almost the same speed as @json_decode
     * with error check (generally faster for failed test, almost the same for passed),
     * moreover it improves compatibility with old PHP version
     *
     * @param mixed $content
     * @param string $error
     * @return bool
     *
     * @link  http://stackoverflow.com/questions/2583472/regex-to-validate-json
     */
    public function isValid($content, &$error = '')
    {
        $pcreRegex = '/
          (?(DEFINE)
             (?<number>   -? (?= [1-9]|0(?!\d) ) \d+ (\.\d+)? ([eE] [+-]? \d+)? )
             (?<boolean>   true | false | null )
             (?<string>    " ([^"\n\r\t\\\\]* | \\\\ ["\\\\bfnrt\/] | \\\\ u [0-9a-f]{4} )* " )
             (?<array>     \[  (?:  (?&json)  (?: , (?&json)  )*  )?  \s* \] )
             (?<pair>      \s* (?&string) \s* : (?&json)  )
             (?<object>    \{  (?:  (?&pair)  (?: , (?&pair)  )*  )?  \s* \} )
             (?<json>   \s* (?: (?&number) | (?&boolean) | (?&string) | (?&array) | (?&object) ) \s* )
          )
          \A (?&json) \Z
          /six';

        preg_match($pcreRegex, $content, $matches);

        if ((bool)($matches)) {
            return true;
        } else {
            @json_decode($content);

            if (json_last_error() !== JSON_ERROR_NONE) {
                if (version_compare(phpversion(), '5.5.0', '>=')) {
                    $error = json_last_error_msg();
                } else {
                    $errors = array(
                        JSON_ERROR_NONE => 'No error',
                        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                        JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                        JSON_ERROR_SYNTAX => 'Syntax error',
                        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
                    );

                    $error = json_last_error();

                    $error = __CLASS__ . " : "
                        . (isset($errors[$error]) ? $errors[$error] : 'Unknown error')
                        . "\nInvalid Content:\n" . $content;
                }
            }
        }
    }

}