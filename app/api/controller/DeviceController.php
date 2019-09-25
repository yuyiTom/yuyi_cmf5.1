<?php
/*
设备接口


*/ 



namespace app\api\controller;

use think\Db; 
use cmf\controller\HomeBaseController;
class DeviceController extends HomeBaseController
{

// 375b01b9f192f5349ac5a2c62a23ba72
// 07084314a8570b2d4e5c76f7c102d411

/*  1.3.7设备绑定
http://localhost/shouhuan/public/api/device/bind.php?token=375b01b9f192f5349ac5a2c62a23ba72&name=F8301&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF&alias=Test01

https://shouhuan.taoyt.cn/api/device/bind.php?token=07084314a8570b2d4e5c76f7c102d411&name=F8301&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF&alias=Test01


http://39.108.219.58:8808/api/device/bind.php?token=MTAwMTEwfHx8MTc0YTcxYjQxMmI1NmNkMmYzYjhkNjYxNjkzZWFkMTQ4YzU2NTMzZXx8fDIwMTktMDgtMjIgMDA6NDE6MjY=&name=F8301&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF&alias=Test01
*/ 

/*
设备绑定的逻辑
1、接收到数据之后去 device 表 判断一下是否添加，没有则添加，但不设置使用即 active=false，并设置是否绑定字段 is_bind=1
2、之后根据token值 将设备id 存到  user表中的 device_id(存成数组)

3、若已经添加了，则判断是否绑定即is_bind是否=1，若是 (无论是否是自己绑定的)则 提示{"errno":108,"error":"Device already exists!!!"}
	若否即is_bind=0 则 根据token值 将设备id 存到  user表中的 device_id(存成数组)

4、注： device表中 is_bind的值与  user表中 device_id 数组中的内容 同步更新， is_bind=1 则 device_id数组 中添加 device表中的id
		is_bind=0 则 从 device_id 数组 中去掉 对应device表中的id（解绑）
5、 因为  is_bind  与 device_id 同步，为方便判断，所以只判断 is_bind 

6、原逻辑 推测可能是 绑定时数据库添加，解绑时数据库删除。

*/ 
	public function bind(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$name=isset($_REQUEST['name'])?$_REQUEST['name']:'';
		$device=isset($_REQUEST['device'])?$_REQUEST['device']:'';//设备物理地址；
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$alias=isset($_REQUEST['alias'])?$_REQUEST['alias']:'';

		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		if($name==''||$device==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}

		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		
		// 只判断了 id    没有判断 'device'=>$device
		$d_info=$jtq_device->where(array('id'=>$id))->find();
		if(!$d_info){
			$ins_arr=array();
			$ins_arr['id']=$id;
			$ins_arr['name']=$name;
			$ins_arr['device']=$device;
			$ins_arr['alias']=$alias;
			$ins_arr['active']=1;
			$ins_arr['is_bind']=1;
			$ins_arr['time']=date("Y-m-d H:i:s",time());
			$bind=$jtq_device->insert($ins_arr);

			$device_id=$f_info['device_id'];
			if($device_id==''){
				$device_id=$id;
			}else{
				$device_id = explode("__", $device_id);

				// 将该用户绑定的其他设备的 active清空
				foreach ($device_id as $key => $val) {
					$jtq_device->where(array('id'=>$val))->update(array('active'=>0));
				}


				array_push($device_id, $id);
				$device_id = implode("__", $device_id);
			}

			$up=$jtq_user->where(array('token'=>$token))->update(array('device_id'=>$device_id,'active_id'=>$id));
			$err=array();
			$err['errno']=0;
			$err['error']="Success.";
			$err=json_encode($err);
			echo($err);
			exit();

		}else{

			// 解绑之后 想要重新绑定 

			$is_bind=$d_info['is_bind'];
			// if($is_bind==0){

				$device_id=$f_info['device_id'];
				if($device_id==''){
					$device_id=$id;
				}else{
					$device_id = explode("__", $device_id);
					// 将该用户绑定的其他设备的 active清空
					foreach ($device_id as $key => $val) {
						$jtq_device->where(array('id'=>$val))->update(array('active'=>0));
					}

					if($is_bind==0){
						array_push($device_id, $id);
					}
					$device_id = implode("__", $device_id);


				}

				$up=$jtq_user->where(array('token'=>$token))->update(array('device_id'=>$device_id,'active_id'=>$id));
				$time=date("Y-m-d H:i:s",time());
				$up_d=$jtq_device->where(array('id'=>$id))->update(array('is_bind'=>1,'time'=>$time,'active'=>1));


				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err=json_encode($err);
				echo($err);
				exit();

			// }
			// else{
			// 	$err=array();
			// 	$err['errno']=108;
			// 	$err['error']="Device already exists!!!";
			// 	$err=json_encode($err);
			// 	echo($err);
			// 	exit();

			// }

		}

	}


// 设备解绑 
//  http://39.108.219.58:8808/api/device/unbind.php?token=MTAwMTEwfHx8MTc0YTcxYjQxMmI1NmNkMmYzYjhkNjYxNjkzZWFkMTQ4YzU2NTMzZXx8fDIwMTktMDgtMjIgMDA6NDE6MjY=&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF
/* 解绑逻辑
验证参数与token
1、获取device_id数组，拆分，判断输入的id 是否在device_id数组中
2、存在 则查找所对应的key值，之后删除，拼接成字符串，更新user表，更新device表
3、不在则 在device判断表中是否真有，真有则No auth 没有则 Device not exists!!! 


*/ 

// http://localhost/shouhuan/public/api/Device/unbind?token=375b01b9f192f5349ac5a2c62a23ba72&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF
// https://shouhuan.taoyt.cn/api/Device/unbind?token=07084314a8570b2d4e5c76f7c102d411&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF

	public function unbind(){


		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$device=isset($_REQUEST['device'])?$_REQUEST['device']:'';//设备物理地址；
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		if($device==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}else{
			$device_id=$f_info['device_id'];
			$device_id = explode("__", $device_id);
			$is_in=in_array($id,$device_id);
			if($is_in){
				$key=array_search($id,$device_id);
				unset($device_id[$key]);
				$device_id = implode("__", $device_id);

				// 若正在使用的设备id  与需要解绑的设备id 相同,则清空 active_id
				$up_user=array();
				$up_user['device_id']=$device_id;
				if($f_info['active_id']==$id){
					$up_user['active_id']=null;
				}

				$up=$jtq_user->where(array('token'=>$token))->update($up_user);
				$up_d=$jtq_device->where(array('id'=>$id))->update(array('is_bind'=>0,'active'=>0));

				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err=json_encode($err);
				echo($err);
				exit();


			}else{

				$find_de=$jtq_device->where(array('id'=>$id))->find();
				if(!$find_de){
					$err=array();
					$err['errno']=109;
					$err['error']="Device not exists!!!";
					$err=json_encode($err);
					echo($err);
					exit();

				}else{
					// 解绑别人的，所以权限不够
					$err=array();
					$err['errno']=110;
					$err['error']="No auth!!!";
					$err=json_encode($err);
					echo($err);
					exit();
				}

				

			}

		}


	}



	// 设备列表
// MTAwMTEwfHx8OTA0Zjg5YmViZjJmNTE5MjY4NWY3ODJmNDUxZTVhZGRmZDM5ZjExM3x8fDIwMTktMDgtMjIgMDc6MDU6MDc
	// http://39.108.219.58:8808/api/device/info.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=


/*
	{
		"list":[
					{"name":"F8301","device":"AA:BB:CC:DD:AA","id":"1234567890ABCDEA","alias":"Test01","time":"2019-08-22 11:58:57","active":false},
					{"name":"F8301","device":"AA:BB:CC:DD:EE","id":"1234567890ABCDEF","alias":"Test01","time":"2019-08-22 11:30:49","active":false},
					{"name":"F8301","device":"AA:BB:CC:DD:EE","id":"1234567890ABCDEF","alias":"Test01","time":"2019-08-22 11:31:53","active":false},
					{"name":"F8301","device":"AA:BB:CC:DD:EE","id":"1234567890ABCDEF","alias":"Test01","time":"2019-08-22 11:31:56","active":false},
					{"name":"F8301","device":"AA:BB:CC:DD:EE","id":"1234567890ABCDEF","alias":"Test01","time":"2019-08-22 11:33:48","active":false},
					{"name":"F8301","device":"sAA:BB:CC:DD:E","id":"s1234567890ABCDE","alias":"Test01","time":"2019-08-22 11:34:21","active":false}
				],
		"errno":0,
		"error":"Success."

	}

	http://39.108.219.58:8808/api/device/info.php?token=MTAwMTExfHx8NzYxN2M1OWQ1MmUwMDJjYzQxMzdhMWIwNWYxZmVlODAzZDAxZDFiYXx8fDIwMTktMDgtMjIgMDY6Mzg6MDM=

	{"list":[],"errno":0,"error":"Success."}

*/ 

// 
// http://localhost/shouhuan/public/api/Device/info.php?token=375b01b9f192f5349ac5a2c62a23ba72
// https://shouhuan.taoyt.cn/api/device/info.php?token=07084314a8570b2d4e5c76f7c102d411

	public function info(){
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
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
		}else{
			$list=array();
			$device_id=$f_info['device_id'];
			$device_id = explode("__", $device_id);


			if(!empty($device_id[0])){

				foreach ($device_id as $val) {
					$info=$jtq_device->where(array('id'=>$val))
					->field("name,device,id,alias,time,active")
					->find();
					if($info){
						if($info['active']==1){
							$info['active']=true;
						}else{
							$info['active']=false;
						}
						$list['list'][]=$info;
					}

				}
			}else{
				$list['list']=array();
			}


			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));echo "<br>";

		}

	}



// 设备切换
// http://localhost/shouhuan/public/api/Device/active.php?token=375b01b9f192f5349ac5a2c62a23ba72&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF
// https://shouhuan.taoyt.cn/api/Device/active.php?token=07084314a8570b2d4e5c76f7c102d411&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF


// http://39.108.219.58:8808/api/device/active.php?token=MTAwMTEwfHx8OTA0Zjg5YmViZjJmNTE5MjY4NWY3ODJmNDUxZTVhZGRmZDM5ZjExM3x8fDIwMTktMDgtMjIgMDc6MDU6MDc=&device=AA:BB:CC:DD:EE&id=1234567890ABCDEF

//  切换到别人的设备 没有权限
// {"errno":110,"error":"No auth!!!"}
		public function active(){

			$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
			$device=isset($_REQUEST['device'])?$_REQUEST['device']:'';//设备物理地址；
			$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
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
			}else{

				$d_info=$jtq_device->where(array('device'=>$device,'id'=>$id))->find();

				if(!$d_info){
					$err=array();
					$err['errno']=109;
					$err['error']="Device not exists!!!";
					$err=json_encode($err);
					echo($err);
					exit();
				}else{

					$device_id=$f_info['device_id'];
					$device_id = explode("__", $device_id);
					$is_in=in_array($id,$device_id);
					/*
					切换设备 逻辑
					数组中若有传过来的id，则循环数组device_id，根据每个id
						去 device 表中更改相对应的active的值，传过来的改为1
						其他全改为0.
					
					注：若传过来的id值对应的active本来就=1，返回也为success

					*/ 
					if($is_in){

						foreach ($device_id as  $val) {
							$arr=array();
							$arr['active']=0;
							if($val==$id){
								$arr['active']=1;
								$jtq_user->where(array('token'=>$token))->update(array('active_id'=>$id));

							}

							$info=$jtq_device->where(array('id'=>$val))
							->update($arr);

						}

						$err=array();
						$err['errno']=0;
						$err['error']="Success.";
						$err=json_encode($err);
						echo($err);
						exit();

					}else{

						$err=array();
						$err['errno']=110;
						$err['error']="No auth!!!";
						$err=json_encode($err);
						echo($err);
						exit();

					}

				}

			}

		}





}

