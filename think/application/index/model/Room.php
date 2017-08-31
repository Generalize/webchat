<?php
namespace app\index\model;

class Room extends \think\Model
{
	public function addRoom(){
		$input = request()->param();
		if(!isset($input['token'])){
			throw new \app\common\Ex(['m' => 'notoken']);
		}
		$uid = decrypt($input['token']);
		$user = \think\Db::table('user')->
				where('id',$uid)->
				find();
		if(empty($user)){
			throw new \app\common\Ex(['m' => 'token error']);
		}
		$this->data([
			'name' => $input['name']
		]);
		$this->save();
		\think\Db::table('room_user_real')->
				data(['uid' => $uid,'rid' => $this->id,'role' => 1])->
				insert();
		return ['m' => 'success'];
		
	}
	
	public function searchRoom(){
		$input = request()->param();
		if(!isset($input['token'])){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		if(!isset($input['search'])){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		$uid = decrypt($input['token']);
		$search = trim($input['search']);
		$user = \think\Db::table('user')->
				where('id',$uid)->
				find();
		if(empty($user)){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		
		$join = \think\Db::table('room_user_real')->
				field('rid')->
				where('uid',$uid)->
				select();
		$ids = [];
		foreach($join as $v){
			$ids[] = $v['rid'];
		}
		$ids = implode(',',$ids);
		$room = \think\Db::table('room')->
				where('name','LIKE','%'.$search.'%')->
				where('id','NOT IN',$ids)->
				select();
		foreach ($room as &$r){
			$r['id'] = encrypt($r['id']);
			$r['avatar'] = config('path.images') . $r['avatar'];
		}
		return $room;
	}
	
	public function joinRoom(){
		$input = request()->param();
		if(!isset($input['token'])){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		if(!isset($input['room'])){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		$uid = decrypt($input['token']);
		$rid = decrypt($input['room']);
		$user = \think\Db::table('user')->
				where('id',$uid)->
				find();
		$room = \think\Db::table('room')->
				where('id',$rid)->
				find();
		if(empty($user || empty($room))){
			throw new \app\common\Ex(['m' => 'input error']);
		}
		$num = \think\Db::table('room_user_real')->
				data(['uid' => $uid , 'rid' => $rid])->
				insert();
		if($num){
			return ['m' => 'success'];
		}
	}
			
}


