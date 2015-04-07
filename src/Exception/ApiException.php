<?php

namespace SendWithUs\Api\Exception;

class ApiException extends \Exception
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var string
     */
    protected $json;

    /**
     * @param string $message
     * @param string $status
     * @param string $body
     * @param string $json
     */
    public function __construct($message, $status = null, $body = null, $json = null)
    {
        parent::__construct($message);

        $this->status = $status;
        $this->body = $body;
        $this->json = $json;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param string $json
     */
    public function setJson($json)
    {
        $this->json = $json;
    }
}
