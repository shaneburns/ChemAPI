<?php
namespace ChemAPI;

class Result  
{
    public $body;
    public $headers;
    public $status;

    public function __construct($body, int $status = 0, array $headers = [])
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->status = $status;
    }
}
