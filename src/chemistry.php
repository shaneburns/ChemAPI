<?php
namespace ChemAPI;
use ChemAPI\startup;
use ChemAPI\Result;
use Tightenco\Overload\Overloadable;
/**
* Chemistry
 */
class chemistry
{
    use Overloadable;
    public $config;
    public $catalyst;
    public $controller;
    private $result;

    function __construct(startup $config, bool $loadOnInit = true){
        // Store config locally
        $this->config = $config;
        // Create a routing catalyst
        $this->catalyst = new routingCatalyst();
        if($loadOnInit)$this->loadController($loadOnInit);
        $this->printResult($this->result);
    }

    public function loadController(bool $invokeAction = false){
        // form the class path
        $path = $this->catalyst->getControllerPath();
        if (strpos($path, 'favicon') !== false || is_null($path)) return false;
        try{// pull the trigger
            // Instantiate the class and expect result
            $this->controller = new $path($this, $invokeAction);
            $this->result = $this->controller->getResponse();
        }catch(\Exception $e){// that's a dud
            echo $e;
            // log path and error and everything
            // 404 response
        }
    }
    public function printResult(...$args)
    {
        // Gurantee a response, even to say there was no reaction
        $this->overload($args, [
            function (Result $result)
            {
                \http_response_code($result->status);
                if(!empty($result->headers)) foreach($result->headers as $h ) header($h);
                if($result->status > 399 && empty($result->body))  $result->body = ['request'=> 'failed', 'message'=> \http_response_code()];
                echo \json_encode($result->body);
                die();
            },
            function (){
                return $this->printResult(new Result(['request'=> 'failed', 'message'=> 'Try again'], 400));
            },
            function (object $object){
                return $this->printResult(new Result($object, 200));
            },
            function ($whatever){
                return $this->printResult(new Result(['request'=> 'success', 'message'=> $whatever], 200));
            }
        ]);
    }


    public function setStatusCodeHeader(int $code = 0)
    {
        //\http_response_code($result->status);
    }
}
