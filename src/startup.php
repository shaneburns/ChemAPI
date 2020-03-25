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
    public $stdSettings;
    public $setting;

    function __construct(array $settings)
    {
        // Default settings setup
        $this->stdSettings = array(
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
            'ENV_DETAILS_PATH' => null,
            'CONTROLLER_NAMESPACE' => 'Controller',
            'CORE_NAMESPACE' => null
        );
        $this->settings = array_merge($stdSettings, $settings);
    }

}
