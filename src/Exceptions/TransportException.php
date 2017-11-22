<?php

namespace GinoPane\NanoRest\Exceptions;

class TransportException extends \Exception
{
    /**
     * Additional data that can be passed to exception handlers
     *
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
