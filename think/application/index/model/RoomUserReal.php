<?php
namespace app\index\model;

class RoomUserReal extends \think\Model
{	
	protected $table = 'room_user_real';
	public function loadRoom(){  //加载房间列表
		$uid = decrypt(input('get.token'));
		$room = [];
		$rid = \think\Db::table('room_user_real')->
				field('rid')->
				where(['uid' => $uid,'exit' => 0,'black' => 0])->
				select();
		foreach ($rid as $id){
			$room[] = \think\Db::table('room')->
				where('id',$id['rid'])->
				find();
		}
		$uTableName = 'msg_'.$uid;
		foreach ($room as &$r){
			$record = \think\Db::table($uTableName)->
					field('count(*)')->
					where(['rid' => $r['id'] , 'read' => 0])->
					find();
			$r['count'] = $record['count(*)'];
			$lastSpeak = \think\Db::table('speak')->
					where(['rid' => $r['id'],'id' => $r['last_speak']])->
					find();
			if($lastSpeak){
				$r['updated'] = $lastSpeak['time'];
			}
			
			$speaker = \think\Db::table('user')->
					where('id',$lastSpeak['uid'])->
					find();
			$r['text'] = $speaker['nickName'] . ':' . $lastSpeak['text'] . (!empty($lastSpeak['image']) ? '[图片]' : '');
			if($r['text'] == ':'){
				$r['text'] = '';
			}
			$r['avatar'] = config('path.images') . $r['avatar'];
			$r['r'] = encrypt($r['id']);
			unset($r['id']);
			unset($r['create_time']);
		}
		return $room;
	}
}
