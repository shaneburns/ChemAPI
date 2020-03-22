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
        if(isset($_SERVER['HTTP_AUTH']) && $this->validateAccess()){
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
    public function verificationRedirect()
    {
        // if($this->chem->catalyst->getController() === DEFAULT_VERIFICATION_CONTROLLER
        //     && $this->hasAction()){ // Already on the verification controller at a valid action
        //         $this->invokeAction();
        // }else{ // Let's redirect to the login
        //     $this->storeOrigRequest();
        //     // Send to the form
        //     $this->redirectToControllerAction(DEFAULT_VERIFICATION_CONTROLLER,DEFAULT_VERIFICATION_ACTION);
        // }
    }

    public function storeOrigRequest()
    {
        if(isset($_GET['origRequest'])) return;
        // build array
        $data = array(
            'origRequest' => true,
            'controller' => $this->chem->catalyst->getController(),
            'action' => $this->chem->catalyst->getAction()
        );
        $queryString = \http_build_query($data);
        $this->chem->catalyst->setQueryString($queryString);
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
