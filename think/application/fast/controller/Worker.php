<?php

namespace app\fast\controller;

use \Workerman\Lib\Timer;
use think\worker\Server;
use think\Db;

class Worker extends Server
{
    
	public function __construct() {
		$websocket = new \app\fast\model\Websocket();
		$this->registryService($websocket);
		$http = new \app\fast\model\Http();
		$this->registryService($http);
		$this->timer();
		parent::__construct();
	}
	
	protected function timer(){
		
	}
}
