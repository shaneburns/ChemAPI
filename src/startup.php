<?php
namespace ChemAPI;
use Doctrine\DBAL;
use Doctrine\Common;
use Monolog\Logger;
use TheCodingMachine\TDBM;
/**
 * Startup
 */
class startup
{
    public $bundleConfig;
    public $tdbmService;

    function __construct(array $settings)
    {
        // Default settings setup
        $stdSettings = array(
            "dr" => $_SERVER['DOCUMENT_ROOT'],
            "ds" => DIRECTORY_SEPARATOR,
            'DEFAULT_CONTROLLER' => 'home',
            'DEFAULT_GET_ACTION' => 'Get',
            'DEFAULT_POST_ACTION' => 'Post',
            'DEFAULT_PUT_ACTION' => 'Put',
            'DEFAULT_DELETE_ACTION' => 'Delete',
            'DEFAULT_VERIFICATION_CONTROLLER' => 'verification',
            'DEFAULT_VERIFICATION_ACTION' => 'requestAccessForm',
            'PROJECT_NAMESPACE' => null,
            'CONTROLLER_NAMESPACE' => 'Controller'

        );
        $settings = array_merge($stdSettings, $settings);
        $this->DefineEnvironmentVars($settings);

        if(is_null(PROJECT_NAMESPACE) || empty(ENV_DETAILS_PATH) || ENV_DETAILS_PATH == null) die();

        // Parse .env file for
        $this->putEnvVars(parse_ini_file(ENV_DETAILS_PATH));
        // Require SSL if denoted
        if(getenv('requireSSL') && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) $this->sslRedirect();
        // Start TDBM services
        $this->startTDBMService();
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
        $beanNamespace = 'core\\Beans';
        $daoNamespace = 'core\\Daos';

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
}
