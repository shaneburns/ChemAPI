<?php
namespace ChemAPI;
use ChemAPI\controller;
/**
 *
 */
class baseVerifiedController extends controller
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

}
