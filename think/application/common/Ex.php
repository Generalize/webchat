<?php
namespace app\common;

class Ex extends \Exception
{
	protected $msg  = '';
	public function __construct($msg) {
		$this->msg = $msg;
	}
	
	public function getEx(){
		return $this->msg;
	}
}
