<?php
namespace ChemAPI;
use TheCodingMachine\TDBM\TDBMService;

class Model{
    public $tdbmService;

    public function __construct(TDBMService $tdbmService) {
        $this->tdbmService = $tdbmService;
    }
}