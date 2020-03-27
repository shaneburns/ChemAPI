<?php
namespace ChemAPI;
use ChemAPI\controller;
/**
 *
 */
class verifiedController extends controller
{
    function __construct($chem = null)
    {
        parent::__construct($chem, false);
        // Check for presence of verification token immediately
        if(isset($_SERVER['HTTP_AUTH']) && $this->hasAction(VERIFY_ACCESS_ACTION) && $this->{VERIFY_ACCESS_ACTION}()){
                // if(isset($_GET['origRequest']) && isset($_GET['controller']) && isset($_GET['action'])) {
                //     $this->chem->catalyst->setQueryString(null);
                //     $this->redirectToControllerAction($_GET['controller'], $_GET['action']);
                // }
                //else $this->redirectToSplashScreen();// check if original request parameters are present else go to security index splash screen
                $this->response = $this->invokeAction();
        }
        // No verification token or username/pw found in request
        // FULLY DENY!
        // Figure it out
        else $this->response = new Result([], 401);

    }
    public function buildHeaderValuePairings($selector, $val, $encode = false){
        $goods = $selector . ":" . $val;
        if($encode) $goods = \base64_encode($goods);
        return $goods;
    }
    public function seperateValuePairings($valPairing, $decode = false){
        if($decode) $valPairing = \base64_decode($valPairing);
        $goods = preg_split("#:#", $valPairing, 2, PREG_SPLIT_NO_EMPTY);
        return $goods;
    }

}
