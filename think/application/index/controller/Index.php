<?php
namespace app\index\controller;

//include_once ""
class Index
{
    public function index()
    {
		$view  = new \think\View();
		$view->fetch('index');
        return $view->fetch('index');
    }

	
	public function sendMsg(){
		$app = new \EasyWeChat\Foundation\Application($this->options);
		$response = $this->app->server->serve();
		// 将响应输出
		return $response;
	}
	
	public function login(){
		$app = new \app\index\model\App();
		$token = $app->registry();
		return ['token' => $token];
	}
	
	public function roomlist(){
		$roomUserReal = new \app\index\model\RoomUserReal();
		try {
			return $roomUserReal->loadRoom();
		} catch (Exception $ex) {
			
		}
	}
	
	
	public function upload(){
		try{
			$app = new \app\index\model\App();
			return $app->saveFile();
		}catch(\app\common\Ex $e){
			return $e->getEx();
		}
	}
	
	public function addRoom(){
		try{
			$app = new \app\index\model\Room();
			return $app->addRoom();
		}catch(\app\common\Ex $e){
			return $e->getEx();
		}
	}
	
	public function searchRoom(){
		try{
			$app = new \app\index\model\Room();
			return $app->searchRoom();
		}catch(\app\common\Ex $e){
			return $e->getEx();
		}
	}
	
	public function joinRoom(){
		try{
			$app = new \app\index\model\Room();
			return $app->joinRoom();
		}catch(\app\common\Ex $e){
			return $e->getEx();
		}
	}
	
}
