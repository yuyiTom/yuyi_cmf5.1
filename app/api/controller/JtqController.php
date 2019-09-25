<?php
/*
家庭圈接口

*/ 


namespace app\api\controller;

use think\Db; 
use cmf\controller\HomeBaseController;
class JtqController extends HomeBaseController
{
/*
添加 家庭圈，并把圈主 添加到成员表中，方便转让

http://localhost/shouhuan/public/api/jtq/addFamily?token=44&f_name=44fn2

http://shouhuan.taoyt.cn/api/jtq/addFamily

*/ 

	public function addFamily(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$f_name=isset($_REQUEST['f_name'])?$_REQUEST['f_name']:'';

		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');


		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		}
		if($token==''||$f_name==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}

		$find_f=$jtq_family->where(array('f_name'=>$f_name,'token'=>$token))->find();
		if($find_f){
			$err=array();
			$err['errno']=105;
			$err['error']="该名称已存在！";
			$err=json_encode($err);
			echo($err);
			exit();
		}

		$arr=array();
		$arr['f_name']=$f_name;
		$arr['token']=$token;
		$arr['create_time']=time();
		$f_id=$jtq_family->insertGetId($arr);
		if($f_id){
			$arr_m=array();
			$arr_m['f_id']=$f_id;
			$arr_m['f_name']=$f_name;
			$arr_m['token']=$token;
			$arr_m['master']=1;
			$arr_m['create_time']=time();
			$jtq_family_member->insert($arr_m);

		}

		$up_d=array();
		$up_d['errno']=0;
		$up_d['error']="Success.";
		$up_d['f_id']=$f_id;
		$up_d['f_name']=$f_name;
		print_r(json_encode($up_d));


	}


/*
添加成员
http://localhost/shouhuan/public/api/jtq/addMenber?token=44&f_id=1&f_name=44name&phone=111

*/
	public function addMenber(){

		// 家庭圈主人token
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$f_id=isset($_REQUEST['f_id'])?$_REQUEST['f_id']:'';
		$f_name=isset($_REQUEST['f_name'])?$_REQUEST['f_name']:'';
		// 要添加的成员 的 手机号，通过手机号来搜索
		$phone=isset($_REQUEST['phone'])?$_REQUEST['phone']:'';
		if($phone==''||$f_id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');

		$find_f=$jtq_family->where(array('id'=>$f_id,'token'=>$token))->find();
		if(!$find_f){
			$err=array();
			$err['errno']=105;
			$err['error']="该家庭圈不存在！";
			$err=json_encode($err);
			echo($err);
			exit();
		}

		$fd_info=$jtq_user->where(array('phone'=>$phone))->find();
		if(!$fd_info){
			$err=array();
			$err['errno']=105;
			$err['error']="该手机号未被任何用户绑定！";
			$err=json_encode($err);
			echo($err);
			exit();
		}else{

			$find_m=$jtq_family_member->where(array('f_id'=>$f_id,'token'=>$fd_info['token']))->find();
			if(!$find_m){
				$arr_m=array();
				$arr_m['f_id']=$f_id;
				$arr_m['f_name']=$f_name;
				$arr_m['token']=$fd_info['token'];
				$arr_m['master']=0;
				$arr_m['create_time']=time();
				$jtq_family_member->insert($arr_m);
			}

			$up_d=array();
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

		}

	}


/*
获取家庭圈列表 以及设备列表
http://localhost/shouhuan/public/api/jtq/getFamilyDevice?token=44

[{"f_id":3,"f_name":"44fn","user":[{"id":4,"token":"44","phone":"44444","avatar":null,"alias":null,"active_id":null,"device_id":null},{"id":2,"token":"2","phone":"22222","avatar":null,"alias":null,"active_id":null,"device_id":""}]},{"f_id":4,"f_name":"44fn2","user":[{"id":4,"token":"44","phone":"44444","avatar":null,"alias":null,"active_id":null,"device_id":null},{"id":3,"token":"33","phone":"33333","avatar":"http:\/\/xxx.xxx.com\/123.png","alias":"jack","active_id":"1111","device_id":"1234567890ABCDEF__1111","dec_info":[{"id":"1234567890ABCDEF","name":"F8301","device":"AA:BB:CC:DD:EE","alias":"Test01","time":"2019-08-31 16:29:36","active":0,"is_bind":1},{"id":"1111","name":"F8301","device":"1111","alias":"Test01","time":"2019-08-31 16:31:33","active":1,"is_bind":1}]}]}]

*/
	public function getFamilyDevice(){

		// 用户token  不一定是圈主
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');

		// 查询用户所在的家庭圈  多个 所有用 select
		$get_f_m=$jtq_family_member->where(array('token'=>$token))->select()->toArray();

		$lists=array();

		if(!$get_f_m){

			$err=array();
			$err['errno']=105;
			$err['error']="该用户不属于任何家庭圈！";
			$err=json_encode($err);
			echo($err);
			exit();

		}else{

			// 循环每个家庭圈 查询改圈下 所有成员
			foreach ($get_f_m as $key => $val) {
				$f_id=$val['f_id'];
				$family=array();
				$family['f_id']=$f_id;
				$family['f_name']=$val['f_name'];
				$get_2=$jtq_family_member->where(array('f_id'=>$f_id))->select()->toArray();

				// 循环每个成员，查询成员的基本信息
				foreach ($get_2 as $k_2 => $val_2) {
					# code...
					$user=array();
					$get_2_token=$val_2['token'];
					$get_3=$jtq_user->where(array('token'=>$get_2_token))->find();
					$user['id']=$get_3['id'];
					$user['token']=$get_3['token'];
					$user['phone']=$get_3['phone'];
					$user['avatar']=$get_3['avatar'];
					$user['alias']=$get_3['alias'];
					$user['active_id']=$get_3['active_id'];
					$user['device_id']=$get_3['device_id'];
					$user['dec_info']=array();

					// 查询设备基本信息
					$device_id=$get_3['device_id'];
					if($device_id){
						$device_id = explode("__", $device_id);
						foreach ($device_id as $key => $val) {
							$dev_info=array();
							$dev_info=$jtq_device->where(array('id'=>$val))->find();
							$user['dec_info'][]=$dev_info;
						}

					}

					$family['user'][]=$user;

				}
				$lists[]=$family;

			}

		}

		print_r(json_encode($lists));
		exit();

	}


/*
退出家庭圈，圈主清退或 家人自己退

*/ 

	public function delMember(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$f_id=isset($_REQUEST['f_id'])?$_REQUEST['f_id']:'';

		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');


		$info=$jtq_family_member->where(array('token'=>$token,'f_id'=>$f_id))->find();






	}








}

	