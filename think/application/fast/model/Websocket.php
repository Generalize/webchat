<?php
namespace app\fast\model;
use GlobalData\Client;

class Websocket
{
	public $url = 'websocket://0.0.0.0:8989';
	/**
     * 收到信息
     * @param $connection
     * @param $data
     */
	public function onMessage($connection, $data)
    {
		try{
			$data = json_decode($data,true);
			$action = '';
			if(is_array($data) && isset($data['a']) && isset($data['token'])){  //判断表单数据合格性 array a token
				$action = $data['a'];
			}else{  //判断为非法请求  
				$msg = json_encode(['m' => 'nothing']);
				throw new \Exception($msg);
			}
			
			$data['token'] = decrypt($data['token']);
			$data['r'] = decrypt($data['r']);
			$user = \think\Db::table('user')->
					where('id',$data['token'])->
					find();
			$room = \think\Db::table('room')->
					where('id',$data['r'])->
					find();
			if(empty($user) || empty($room)){  //判断用户传过来的token是否正确
				$msg = json_encode(['m' => 'nothing']);
				throw new \Exception($msg);
			}
			$this->$action($connection,$data);
		}catch(\Exception $e){
			$msg = $e->getMessage();
			print_r($e->getLine());
			print_r($e->getMessage());
			$connection->send($msg);
		}
    }
	
	public function exitRoom($conn,$data){
		echo "用户{$conn->uid}已经退出房间{$conn->rid}\n";
		$conn->rid = null;
		$msg = json_encode(['m' => 'exit success']);
		$conn->send($msg);
	}
	
	public function room($conn,$data){
		$rul = \think\Db::table('room_user_real')->
				where(['uid' => $conn->uid , 'rid' => $data['r']])->
				find();
		if(!$rul){
			throw new \Exception(json_encode(['m' => 'not join']));
		}
		$conn->rid = $data['r'];
		echo "用户{$conn->uid}已经进入房间{$conn->rid}\n";
		$msg = json_encode(['m' => 'room success']);
		$conn->send($msg);
	}
	
	public function showMsg($conn,$data){
		$page = is_numeric($data['page']) ? $data['page'] : 0 ;
		$rid = $data['r'];
		if($rid != $conn->rid){
			$msg = json_encode(['m' => 'show nothing']);
			throw new \Exception($msg);
		}
		//$max = \think\Db::table('speak_view')->value('max(`id`)');
				
		$msgs = \think\Db::table('speak_view')->
				where('rid' , $rid)->
				limit($page*20,20)->
				select();
		
		foreach ($msgs as &$msg){  //处理图片地址
			if($msg['image']){
				$msg['image'] = json_decode($msg['image'],true);
				if(is_array($msg['image'])){
					foreach($msg['image'] as &$img){
						$img['src']  = config('path.images') . $img['src'];   //拼接图片路径
					}
					$msg['image'] = json_encode($msg['image']);
				}else{
					$msg['image'] = '';
				}
				$msg['time'] = strtotime($msg['time']);
			}
			$user = \think\Db::table('user')->
					where('id',$msg['uid'])->
					find();
			$msg['nickName'] = $user['nickName'];
			$msg['avatarUrl'] = config('path.images') . $user['avatarUrl'];
			if($msg['audio']){
				$msg['audio'] = config('path.audio') . $msg['audio'];  
			}
			if($conn->uid == $msg['uid']){  //判断是不是自己
				$msg['self'] = 1;
			}else{
				$msg['self'] = 0;
			}
			unset($msg['uid']);
			unset($msg['rid']);
		}
		$msgs = json_encode($msgs);
		$conn->send($msgs);
		
		$userMsgTable = 'msg_' . $conn->uid; //清零未读消息
		\think\Db::table($userMsgTable)->
				where(['rid' => $rid])->
				update(['read' => 1]);
	}
	
	public function send($conn,$data){
		$msg = [];
		$data['uid'] = $uid = $data['token'];
		$data['rid'] = $rid = $data['r'];
		if( !(isset($conn->rid) && $conn->rid == $rid) ){ //判断为非法请求  当前连接处于已经进入房间状态 并且 $rid 与表单传过来的rid相等
			$ex = json_encode(['m' => 'nothing']);
			throw new \Exception($ex);
		}
		unset($data['a']);
		unset($data['token']);
		unset($data['r']);
		if($data['image']){
			$data['image'] = json_encode($data['image']);
		}
		$rd = \think\Db::table('speak')-> //记录这次发言
				data($data)->
				insert();
		
		$last_speak = \think\Db::table('speak')->getLastInsID();
		\think\Db::table('room')->   //更新该房间最后一次发言
				where('id',$rid)->
				update(['last_speak' => $last_speak]);
		
		$user = \think\Db::table('user')->
					where('id',$uid)->
					find();
		
		$rul = \think\Db::table('room_user_real')->  //找到加入这个房间的并且为非黑非退的用户
			field('uid')->
			where(['rid' => $rid , 'exit' => 0 ,'black' => 0])->
			select();
		
		$msg = \think\Db::table('speak')->
				where('id',$last_speak)->
				find();
		$msg['time'] = time();
		if($msg['image']){   //判断发来的消息中有没有图片 格式为 {1:path,2:path}
			$msg['image'] = json_decode($msg['image'],true);  //将图片信息解析为关联数组
			if(is_array($msg['image'])){
				foreach($msg['image'] as &$img){
					$img['src']  = config('path.images') . $img['src'];   //拼接图片路径
				}
				$img['image'] = json_encode($msg['image']);  //在将数组解析成json字符串
			}else{
				$img['image'] = '';
			}
		}
		if($msg['audio']){
			$msg['audio'] = config('path.audio') . $msg['audio'];  
		}
		$msg['isMsg'] = 1;
		$msg['avatarUrl'] = config('path.images'). $user['avatarUrl'];
		//print_r($msg);exit;
		  //获取到所有连接上来的用户
		foreach (\think\worker\Server::$worker[0]->connections as $online){
			echo '|'.count(\think\worker\Server::$worker[0]->connections)."|\n";
			if($online->rid == $rid){  //判断此在线用户是否为同一个房间的用户
				foreach ($rul as $k => $r){  //筛选出所有不在线或在线但是不在此房间的用户
					if($r['uid'] == $online->uid){
						unset($rul[$k]);
					}
				}
				if($conn->uid == $online->uid){ //判断是不是自己
					$msg['self'] = 1;
				}else{
					$msg['self'] = 0;
				}
				$online->send(json_encode($msg));//将消息解析为字符串
			}
		}
		
		$userMsgTableName = '';
		foreach($rul as $r){  //给所有未在线的用户的未读消息表里增加一条数据
			$userMsgTableName = 'msg_' . $r['uid'];
			$msgData = [
				'sid' => $last_speak,
				'rid' => $rid,
			];
			\think\Db::table($userMsgTableName)->
					data($msgData)->
					insert();
		}
	}

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {		
		$connection->uid = '';
		$connection->onWebSocketConnect = function($connection , $http_header)
		{
			$uid = '';
			$connection->uid = $uid;
			$rid = '';
			$connection->uid = $rid;
			if(isset($_GET['token'])){
				$uid = decrypt($_GET['token']);
			}
			if(isset($_GET['r'])){
				$rid = decrypt($_GET['r']);
			}
			
			$user = \think\Db::table('user')->
					where('id',$uid)->
					find();
			
			if(empty($user)){
				$msg = json_encode([
					'reason' => false
				]);
				$connection->uid = 'notoken';
				$connection->close($msg);
			}else{
				$connection->uid = $uid;
				echo "用户{$connection->uid}已连接\n";
				$msg = json_encode([
					'reason' => '连接成功'
				]);
				$connection->send($msg);
				$room = \think\Db::table('room')->
					where('id',$rid)->
					find();
				if($room){
					$connection->rid = $rid;
					echo "用户{$connection->uid}已进入房间{$connection->rid}\n";
				}
			}
		};
		
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo "用户{$connection->uid}已断开连接\n";
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
    }
	
	
}
