<?php
namespace ChemAPI;
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
            'ENV_DETAILS_PATH' => null,
            'PROJECT_NAMESPACE' => null,
            'CORE_NAMESPACE' => null,
            'CONTROLLER_NAMESPACE' => 'Controller',
            'DEFAULT_CONTROLLER' => 'home',
            'DEFAULT_GET_ACTION' => 'Get',
            'DEFAULT_POST_ACTION' => 'Post',
            'DEFAULT_PUT_ACTION' => 'Put',
            'DEFAULT_DELETE_ACTION' => 'Delete',
            'DEFAULT_VERIFICATION_CONTROLLER' => 'verification',
            'DEFAULT_VERIFICATION_ACTION' => 'requestAccessForm',
            'VERIFY_ACCESS_ACTION' => 'validateAccess'
        );
        $this->settings = array_merge($this->stdSettings, $settings);
    }

}
