<?php
namespace ChemAPI;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
//use Result;
//use utils;
use Tightenco\Overload\Overloadable;

class controller{
    use Overloadable{
		overload as trylessOverload;
	}

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
        if($invokeAction) $this->response = $this->invokeAction();
        return $this->response;
    }
    
    public function overload($args, $signatures){
        try {
            return $this->trylessOverload($args, $signatures);
        } catch (\Exception $e) {
            return new Result($e, 404);
        }
    }

    public function getResponse(){
        return $this->response;
    }

    public function hasAction(string $action){
        return method_exists($this, $action);
    }

    public function invokeAction(){
        // Check if the action exists
        if($this->hasAction($this->chem->catalyst->getAction())){
            try {// invoke the action
                if($this->chem->catalyst->hasParameters()) return $this->{$this->chem->catalyst->getAction()}(...array_values($this->mapOverloadableParameters()));
                else return $this->{$this->chem->catalyst->getAction()}();
            } catch (\Exception $e) {
                // TODO: Log this error stat dude...
                return new Result($e, 404, []);
            }
        }
        else {
            // 404 response
            // TODO: CREATE A FREAKING 404 RESPONSE BROOO
            return new Result("The page specified was not found.", 404, []);
        }
    }

    public function mapOverloadableParameters()
    {
        $origParams = $this->chem->catalyst->getParameters();
        $params = $this->chem->catalyst->getParameters();
        $paramCount = count($params);
        $actionCandidates = $this->{$this->chem->catalyst->getAction().'Actions'};
        $skippedCount = 0;
        $valid = false;
        foreach( $actionCandidates as $key => $val){
            if(gettype($val) == 'object') $func = new ReflectionFunction($val);
            else if(gettype($val) == 'string') $func = new ReflectionMethod($this, $val);
            if($paramCount == $func->getNumberOfParameters()){ // check if the parameter counts match
                $fParams = $func->getParameters();// Get a list of ParameterReflection classes for the function parameters
                $this->mapParameters($params, $fParams, $valid);
                if($valid) break;
                else $params = $origParams;
            }else if($paramCount < $func->getNumberOfParameters()) $skippedCount++;
        }
        if(!$valid && $skippedCount == 0) {
            $result = new Result([], 404);
            $result->display();
            die();
        }
        return $params;
    }

    public function mapParameters(&$params, &$fParams, &$valid){
        for($i = 0;  $i <= count($params) - 1; $i++){// loop through those params
            if(gettype($params[$i]) == 'object' && gettype($fParams[$i]->getClass()) == gettype($params[$i])){// check types for objects
                if(!$fParams[$i]->getClass()->isInternal()){// see if it's not an internal class
                    $instance = $fParams[$i]->getClass()->newInstance(); // create a new instance
                    if(!utils::compareObjectProperties($params[$i], $instance)){ // do a full compare
                        $valid = false;
                        break; // somin ain't right here
                    }
                    try{
                        $params[$i] = utils::classCast($params[$i], $instance); // cast that ish
                    }catch(\Exception $e){
                        // TODO: Bad Mapping -> log this error stat dude...
                        $valid = false;
                        break;
                    }
                }
            }else if(gettype($params[$i]) != $fParams[$i]->getType() && $fParams[$i]->getType() != null){ // check basic type matching
                $valid = false;
                break; // check basic class types
            }
            $valid = true;
        }
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
