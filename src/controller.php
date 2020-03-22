<?php
namespace ChemAPI;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
//use Result;
//use utils;
use Tightenco\Overload\Overloadable;

class controller{
    use Overloadable;

    public $chem;
    public $bond;
    public $response;
    public $GetActions;
    public $PostActions;
    public $PutActions;
    public $DeleteActions;

    function __construct($chem = null, $invokeAction = true){
        if($chem == null) die();
        $this->chem = $chem;
        if($invokeAction) $this->response = $this->invokeAction(); // If action exists the process dies here
        // no action exists to handle the current request is this line is reached
        // TODO: throw a 404 and log it
    }

    public function getResponse(){
        return $this->response;
    }

    public function hasAction(){
        return method_exists($this, $this->chem->catalyst->getAction());
    }

    public function invokeAction(){
        // Check if the action exists
        if($this->hasAction()){
            try {// invoke the action
                if($this->chem->catalyst->hasParameters()) return $this->{$this->chem->catalyst->getAction()}(...array_values($this->chem->catalyst->parametersNeedMapping() ? $this->mapParameters() : $this->chem->catalyst->getParameters()));
                else return $this->{$this->chem->catalyst->getAction()}();
            } catch (\Exception $e) {
                // TODO: Log this error stat dude...
                //return new result($e, [], 404);
            }
        }
        else {
            // 404 response
            // TODO: CREATE A FREAKING 404 RESPONSE BROOO
        }
    }

    public function mapParameters()
    {
        $params = $this->chem->catalyst->getParameters();
        $paramCount = count($params);
        $actionCandidates = $this->{$this->chem->catalyst->getAction().'Actions'};
        $valid = false;
        foreach( $actionCandidates as $key => $val){
            if(gettype($val) == 'object') $func = new ReflectionFunction($val);
            else if(gettype($val) == 'string') $func = new ReflectionMethod($this, $val);
            if($paramCount == $func->getNumberOfParameters()){ // check if the parameter counts match
                $fParams = $func->getParameters();// Get a list of ParameterReflection classes
                for($i = 0;  $i <= $paramCount - 1; $i++){// loop through that list
                    if(gettype($params[$i]) == 'object' && gettype($fParams[$i]->getClass()) == gettype($params[$i])){// check basic class types
                        if(!$fParams[$i]->getClass()->isInternal()){
                            $instance = $fParams[$i]->getClass()->newInstance();
                            if(!utils::compareObjectProperties($params[$i], $instance)){
                                $valid = false;
                                break; // somin ain't right here
                            }
                            try{
                                $params[$i] = utils::classCast($params[$i], $instance);
                            }catch(\Exception $e){
                                // TODO: Bad Mapping -> log this error stat dude...
                                echo $e;
                            }
                        }
                    }else if(gettype($params[$i]) != $fParams[$i]->getType()){ 
                        $valid = false;
                        break; // check basic class types
                    }
                    $valid = true;
                }
                if($valid) break;
            }
        }
        return $params;
    }

    public function redirectToAction($actionName = '') : void
    {
        if(!empty($actionName)){
            $this->chem->catalyst->setAction($actionName);
            $this->redirect();
        }
    }
    public function redirectToControllerAction($controllerName = '', $actionName = '') : void
    {
        if(!empty($controllerName) && !empty($actionName)){
            $this->chem->catalyst->setController($controllerName);
            $this->chem->catalyst->setAction($actionName);
            //$this->chem->loadController(true);
            $this->redirect();
        }
    }

    public function redirect($newLocation = null, $statusCode = 303) : void
    {
        // Build location string
        if(is_null($newLocation))
            $newLocation = $this->chem->catalyst->getLocationString();
        header("Location: " . $newLocation, true, $statusCode);
        die();
    }

    public function Get(...$args){
        return $this->overload($args, $this->GetActions);
    }

    public function Post(...$args)
    {
        return $this->overload($args,$this->PostActions);
    }

    public function Put(...$args){
        return $this->overload($args, $this->PutActions);
    }

    public function Delete(...$args)
    {
        return $this->overload($args,$this->DeleteActions);
    }
}
