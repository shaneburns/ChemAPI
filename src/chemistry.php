<?php
namespace ChemAPI;
use const PROJECT_NAMESPACE;
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
    public $tdbmService;
    private $controller;
    private $result;

    function __construct(startup $config, bool $loadOnInit = true){
        // Store config locally
        $this->config = $config;
        
        $this->DefineEnvironmentVars($config->settings);

        if(is_null(PROJECT_NAMESPACE) || is_null(ENV_DETAILS_PATH)){
            $this->result = new Result("FATAL CHEMISTRY APPLICATION ERROR :: - \nThe expected PROJECT_NAMESPACE or ENV_DETAILS_PATH variables were not located in the defined constants scope.");
            die();
        }

        // Parse .env file for
        $this->putEnvVars(parse_ini_file(ENV_DETAILS_PATH));
        // Require SSL if denoted
        if(getenv('requireSSL') && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) $this->sslRedirect();
        // Start TDBM services
        $this->startTDBMService();
        // Create a routing catalyst
        $this->catalyst = new routingCatalyst();
        if($loadOnInit) $this->instantiateController($loadOnInit);
        $this->result = $this->controller->getResponse();
        $this->printResult($this->result);
    }
    public function putEnvVars(array $vars)
    {
        foreach($vars as $key => $val) putenv($key."=".$val);
    }
    
    public function DefineEnvironmentVars(array $varsToAdd){
        foreach($varsToAdd as $var => $val ){
            if(!defined($var)){
                define($var, $val);
            }else{

            }
        }
    }

    private function sslRedirect()
    {
        // Permanently redirect
        $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $location);
        exit;
    }
    
    private function startTDBMService(){
        $config = new DBAL\Configuration();

        $connectionParams = array(
            'user' => getenv('username'),
            'password' => getenv('password'),
            'host' => getenv('servername'),
            'driver' => getenv('driver'),
            'dbname' => getenv('myDB')
        );

        $dbConnection = DBAL\DriverManager::getConnection($connectionParams, $config);

        // The bean and DAO namespace that will be used to generate the beans and DAOs. These namespaces must be autoloadable from Composer.
        $baseSpace = (CORE_NAMESPACE != null) ? CORE_NAMESPACE : PROJECT_NAMESPACE;
        $beanNamespace = $baseSpace .'\\Beans';
        $daoNamespace = $baseSpace .'\\Daos';

        $cache = new Common\Cache\ApcuCache();

        $logger = new Logger('cantina-app'); // $logger must be a PSR-3 compliant logger (optional).

        // Let's build the configuration object
        $configuration = new TDBM\Configuration(
            $beanNamespace,
            $daoNamespace,
            $dbConnection,
            null,    // An optional "naming strategy" if you want to change the way beans/DAOs are named
            $cache,
            null,    // An optional SchemaAnalyzer instance
            $logger, // An optional logger
            []       // A list of generator listeners to hook into code generation
        );

        // The TDBMService is created using the configuration object.
        $this->tdbmService = new TDBM\TDBMService($configuration);
    }

    public function instantiateController(bool $invokeAction = false){
        // form the class path
        $path = $this->catalyst->getControllerPath();
        if (strpos($path, 'favicon') !== false || is_null($path)) return false;
        try{// pull the trigger
            // Instantiate the class and 
            $this->controller = new $path($this, $invokeAction);
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
