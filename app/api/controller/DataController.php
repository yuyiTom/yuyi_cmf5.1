<?php
/*
设备接口


*/ 


namespace app\api\controller;

use cmf\controller\HomeBaseController;
use think\Db; 
class DataController extends HomeBaseController
{

// MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA
// 07084314a8570b2d4e5c76f7c102d411

/*
步数保存
http://39.108.219.58:8808/api/data/step_save.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=&id=3234567890ABCDEF&data=[{"date":"2018-09-10","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]},{"date":"2018-09-11","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]}]
http://39.108.219.58:8808/api/data/step_save.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&data=[]

{"date":["2018-09-10","2018-09-11"],"errno":0,"error":"Success."}
{"date":[],"errno":0,"error":"Success."}
{"errno":109,"error":"Device not exists!!!"}
{"errno":110,"error":"No auth!!!"}
*/ 

/*  逻辑   验证token与设备id之后
以天为单位 是data数组，下的每一条中的date 为天 一天一条，重复的覆盖。time为具体时间（也被覆盖）
		每一条date下的data为每个小时的步数 是数组，装换格式之后保存或更新


http://localhost/shouhuan/public/api/Data/step_save.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&data=[{"date":"2018-09-10","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]},{"date":"2018-09-11","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]}]
http://localhost/shouhuan/public/api/Data/step_save?token=037958c5e1239f45b08a5e3f2ebf3711&id=1234567890ABCDEF&data=[]

https://shouhuan.taoyt.cn/api/Data/step_save.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&data=[{"date":"2018-09-10","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]},{"date":"2018-09-11","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]}]

*/ 
	public  function  step_save(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$data=isset($_REQUEST['data'])?$_REQUEST['data']:'';


		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_step= Db::name('jtq_data_step');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($data==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){
			// 将json字符串转换成数组
			$data=is_string($data)?json_decode($data,true):$data;

			$up_d=array();
			$up_d['date']=array();

			foreach ($data as $key => $val) {
				$arr_d=array();

				$flag_d=null;


				$arr_d['device_id']=$id;

				$arr_d['date']=isset($val['date'])?$val['date']:'';
				$arr_d['time']=isset($val['time'])?$val['time']:'';
				$arr_d['target']=isset($val['target'])?$val['target']:'';
				$arr_d['calorie']=isset($val['calorie'])?$val['calorie']:'';
				$arr_d['duration']=isset($val['duration'])?$val['duration']:'';
				$arr_d['distance']=isset($val['distance'])?$val['distance']:'';

				$d_d=isset($val['data'])?$val['data']:'';
				$d_d=json_encode($d_d);
				$arr_d['data']=addslashes($d_d);

				$f_step=$jtq_data_step->where(array('date'=>$val['date'],'device_id'=>$id))->find();
				if($f_step){
					$flag_d=1;
					$jtq_data_step->where(array('date'=>$val['date'],'device_id'=>$id))->update($arr_d);
				}else{
					$flag_d=$jtq_data_step->insert($arr_d);
				}
				if($flag_d){
					$up_d['date'][]=$val['date'];
				}

			}

			
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));


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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}



// 步数读取
// {"id":"3234567890ABCDEF","data":[{"date":"2018-09-10","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":100},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]},{"date":"2018-09-11","time":"10:00:00","target":10000,"calorie":200,"duration":300,"distance":1000,"data":[{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":100},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":200},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0},{"step":0}]}],"errno":0,"error":"Success."}
// {"id":"3234567890ABCDEF","data":[],"errno":0,"error":"Success."}
// {"errno":102,"error":"Invalid token!!!"}

// http://39.108.219.58:8808/api/data/step_get.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]
// http://39.108.219.58:8808/api/data/step_get.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&date=[]

// http://localhost/shouhuan/public/api/Data/step_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&date=["2018-09-10","2018-09-11"]
// http://localhost/shouhuan/public/api/Data/step_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// https://shouhuan.taoyt.cn/api/Data/step_get?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]

	public function step_get(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$date=isset($_REQUEST['date'])?$_REQUEST['date']:''; //[天1,天2]
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_step= Db::name('jtq_data_step');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($id==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}


		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){
			$date=is_string($date)?json_decode($date,true):$date;
			if(!$date){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['data']=array();
				$err['id']=$id;
				$err=json_encode($err);
				echo($err);
				exit();
			}

			$list=array();
			$list['id']=$id;
			$list['data']=array();

			foreach ($date as $key => $val) {
				$info=null;;

				$f_step=$jtq_data_step->where(array('date'=>$val,'device_id'=>$id))->find();
				if(!$f_step){
					continue;
				}
				$info['date']=$f_step['date'];
				$info['time']=$f_step['time'];
				$info['target']=$f_step['target'];
				$info['calorie']=$f_step['calorie'];
				$info['duration']=$f_step['duration'];
				$info['distance']=$f_step['distance'];
				$info_data=$f_step['data']; //stripslashes
				$info_data=stripslashes($info_data);
				$info['data']=json_decode($info_data);
				$list['data'][]=$info;


			}

			
			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));



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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}


		}


	}


// 睡眠保存
/*

http://39.108.219.58:8808/api/data/sleep_save.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&data=[{"date":"2018-09-10","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]},{"date":"2018-09-11","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]}]
{"date":["2018-09-10","2018-09-11"],"errno":0,"error":"Success."}


http://localhost/shouhuan/public/api/Data/sleep_save.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&data=[{"date":"2018-09-10","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]},{"date":"2018-09-11","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]}]

http://localhost/shouhuan/public/api/Data/sleep_save?token=037958c5e1239f45b08a5e3f2ebf3711&id=1234567890ABCDEF&data=[]

https://shouhuan.taoyt.cn/api/Data/sleep_save.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&data=[{"date":"2018-09-10","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]},{"date":"2018-09-11","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]}]
*/ 


	public function sleep_save(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$data=isset($_REQUEST['data'])?$_REQUEST['data']:'';


		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_sleep= Db::name('jtq_data_sleep');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($data==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){

			// 将json字符串转换成数组
			$data=is_string($data)?json_decode($data,true):$data;

			$up_d=array();
			$up_d['date']=array();

			foreach ($data as $key => $val) {
				$arr_d=array();

				$flag_d=null;

				$arr_d['device_id']=$id;

				$arr_d['date']=isset($val['date'])?$val['date']:'';
				$arr_d['time']=isset($val['time'])?$val['time']:'';
				$arr_d['total']=isset($val['total'])?$val['total']:'';
				$arr_d['deep']=isset($val['deep'])?$val['deep']:'';
				$arr_d['shallow']=isset($val['shallow'])?$val['shallow']:'';
				$arr_d['sober']=isset($val['sober'])?$val['sober']:'';

				$d_d=isset($val['data'])?$val['data']:'';
				$d_d=json_encode($d_d);
				$arr_d['data']=addslashes($d_d);


				$f_step=$jtq_data_sleep->where(array('date'=>$val['date'],'device_id'=>$id))->find();
				if($f_step){
					$flag_d=1;
					$jtq_data_sleep->where(array('date'=>$val['date'],'device_id'=>$id))->update($arr_d);
				}else{
					$flag_d=$jtq_data_sleep->insert($arr_d);
				}
				if($flag_d){
					$up_d['date'][]=$val['date'];
				}

			}

			
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}



// 睡眠获取
// http://39.108.219.58:8808/api/data/sleep_get.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// {"id":"3234567890ABCDEF","data":[],"errno":0,"error":"Success."}
// {"id":"3234567890ABCDEF","data":[{"date":"2018-09-10","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]},{"date":"2018-09-11","time":"21:00:00","total":400,"deep":120,"shallow":200,"sober":80,"data":[{"quality":1},{"quality":0},{"quality":0},{"quality":0},{"quality":2},{"quality":0},{"quality":1},{"quality":0},{"quality":1},{"quality":1}]}],"errno":0,"error":"Success."}

// http://localhost/shouhuan/public/api/Data/sleep_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// http://localhost/shouhuan/public/api/Data/sleep_get?token=037958c5e1239f45b08a5e3f2ebf3711&id=1234567890ABCDEF&date=[]

// https://shouhuan.taoyt.cn/api/Data/sleep_get?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&date=[%222018-09-10%22,%222018-09-11%22]

public function sleep_get(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$date=isset($_REQUEST['date'])?$_REQUEST['date']:''; //[天1,天2]
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_sleep= Db::name('jtq_data_sleep');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($id==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}


		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){
			$date=is_string($date)?json_decode($date,true):$date;
			if(!$date){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['data']=array();
				$err['id']=$id;
				$err=json_encode($err);
				echo($err);
				exit();
			}

			$list=array();
			$list['id']=$id;
			$list['data']=array();

			foreach ($date as $key => $val) {
				$info=null;;

				$f_step=$jtq_data_sleep->where(array('date'=>$val,'device_id'=>$id))->find();
				if(!$f_step){
					continue;
				}
				$info['date']=$f_step['date'];
				$info['time']=$f_step['time'];
				$info['total']=$f_step['total'];
				$info['deep']=$f_step['deep'];
				$info['shallow']=$f_step['shallow'];
				$info['sober']=$f_step['sober'];
				$info_data=$f_step['data']; //stripslashes
				$info_data=stripslashes($info_data);
				$info['data']=json_decode($info_data);
				$list['data'][]=$info;


			}

			
			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));



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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}






// 心率保存  
// http://39.108.219.58:8808/api/data/heart_rate_save.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]},{"date":"2018-09-11","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]}]
// {"date":["2018-09-10","2018-09-11"],"errno":0,"error":"Success."}
// http://localhost/shouhuan/public/api/Data/heart_rate_save.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]},{"date":"2018-09-11","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]}]

// http://localhost/shouhuan/public/api/Data/heart_rate_save?token=037958c5e1239f45b08a5e3f2ebf3711&id=1234567890ABCDEF&data=[]

// https://shouhuan.taoyt.cn/api/Data/heart_rate_save.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]},{"date":"2018-09-11","data":[{"time":"08:00:00","bmp":80},{"time":"09:00:00","bmp":83}]}]

	public function heart_rate_save(){


		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$data=isset($_REQUEST['data'])?$_REQUEST['data']:'';


		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_heart_rate= Db::name('jtq_data_heart_rate');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($data==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){

			// 将json字符串转换成数组
			$data=is_string($data)?json_decode($data,true):$data;

			$up_d=array();
			$up_d['date']=array();

			foreach ($data as $key => $val) {
				$arr_d=array();
				$flag_d=null;

				$arr_d['device_id']=$id;
				$arr_d['date']=isset($val['date'])?$val['date']:'';

				$d_d=isset($val['data'])?$val['data']:'';
				$d_d=json_encode($d_d);
				$arr_d['data']=addslashes($d_d);


				$f_step=$jtq_data_heart_rate->where(array('date'=>$val['date'],'device_id'=>$id))->find();
				if($f_step){
					$flag_d=1;
					$jtq_data_heart_rate->where(array('date'=>$val['date'],'device_id'=>$id))->update($arr_d);
				}else{
					$flag_d=$jtq_data_heart_rate->insert($arr_d);
				}
				if($flag_d){
					$up_d['date'][]=$val['date'];
				}

			}

			
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}



// 心率获取 
// http://39.108.219.58:8808/api/data/heart_rate_get.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// 注：Json单次数据 是直接覆盖，因为条数、顺序、参数完全一样 使用不是单独存一个表
// http://localhost/shouhuan/public/api/Data/heart_rate_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// https://shouhuan.taoyt.cn/api/Data/heart_rate_get?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]

	public function heart_rate_get(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$date=isset($_REQUEST['date'])?$_REQUEST['date']:''; //[天1,天2]
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_heart_rate= Db::name('jtq_data_heart_rate');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($id==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}


		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){
			$date=is_string($date)?json_decode($date,true):$date;
			if(!$date){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['data']=array();
				$err['id']=$id;
				$err=json_encode($err);
				echo($err);
				exit();
			}

			$list=array();
			$list['id']=$id;
			$list['data']=array();

			foreach ($date as $key => $val) {
				$info=null;;

				$f_step=$jtq_data_heart_rate->where(array('date'=>$val,'device_id'=>$id))->find();
				if(!$f_step){
					continue;
				}
				$info['date']=$f_step['date'];
				$info_data=$f_step['data']; //stripslashes
				$info_data=stripslashes($info_data);
				$info['data']=json_decode($info_data);
				$list['data'][]=$info;


			}

			
			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));



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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}

// 血压保存
// http://39.108.219.58:8808/api/data/blood_pressure_save.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","shrink":120,"diastole":80},{"time":"09:00:00","shrink":121,"diastole":81}]},{"date":"2018-09-11","data":[{"time":"08:00:00","shrink":121,"diastole":81},{"time":"09:00:00","shrink":122,"diastole":82}]}]

// {"date":["2018-09-10","2018-09-11"],"errno":0,"error":"Success."}
// {"date":[],"errno":0,"error":"Success."}

// http://localhost/shouhuan/public/api/Data/blood_pressure_save.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","shrink":120,"diastole":80},{"time":"09:00:00","shrink":121,"diastole":81}]},{"date":"2018-09-11","data":[{"time":"08:00:00","shrink":121,"diastole":81},{"time":"09:00:00","shrink":122,"diastole":82}]}]

// http://localhost/shouhuan/public/api/Data/blood_pressure_save?token=037958c5e1239f45b08a5e3f2ebf3711&id=1234567890ABCDEF&data=[]

// https://shouhuan.taoyt.cn/api/Data/blood_pressure_save.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&data=[{"date":"2018-09-10","data":[{"time":"08:00:00","shrink":120,"diastole":80},{"time":"09:00:00","shrink":121,"diastole":81}]},{"date":"2018-09-11","data":[{"time":"08:00:00","shrink":121,"diastole":81},{"time":"09:00:00","shrink":122,"diastole":82}]}]

	public function blood_pressure_save(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$data=isset($_REQUEST['data'])?$_REQUEST['data']:'';


		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_blood_pressure= Db::name('jtq_data_blood_pressure');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($data==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){

			// 将json字符串转换成数组
			$data=is_string($data)?json_decode($data,true):$data;

			$up_d=array();
			$up_d['date']=array();

			foreach ($data as $key => $val) {
				$arr_d=array();
				$flag_d=null;

				$arr_d['device_id']=$id;
				$arr_d['date']=isset($val['date'])?$val['date']:'';

				$d_d=isset($val['data'])?$val['data']:'';
				$d_d=json_encode($d_d);
				$arr_d['data']=addslashes($d_d);


				$f_step=$jtq_data_blood_pressure->where(array('date'=>$val['date'],'device_id'=>$id))->find();
				if($f_step){
					$flag_d=1;
					$jtq_data_blood_pressure->where(array('date'=>$val['date'],'device_id'=>$id))->update($arr_d);
				}else{
					$flag_d=$jtq_data_blood_pressure->insert($arr_d);
				}
				if($flag_d){
					$up_d['date'][]=$val['date'];
				}

			}

			
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}


	}



// 血压获取
// http://39.108.219.58:8808/api/data/blood_pressure_get.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]
// {"id":"3234567890ABCDEF","data":[{"date":"2018-09-10","data":[{"time":"08:00:00","shrink":150,"diasssstole":80},{"time":"09:00:00","shrink":121,"diastole":81}]},{"date":"2018-09-11","data":[{"time":"08:00:00","shrink":121,"diastole":81},{"time":"09:00:00","shrink":122,"diastole":82}]}],"errno":0,"error":"Success."}
// {"id":"3234567890ABCDEF","data":[{"date":"2018-09-10","data":[{"time":"08:00:00","shrink":120,"diastole":80},{"time":"09:00:00","shrink":121,"diastole":81}]}],"errno":0,"error":"Success."}
// {"id":"1234567890ABCDEF","data":[{"date":"2018-09-10","data":[{"time":"18:00:00","shrink":120,"diastole":80},{"time":"09:00:00","shrink":151,"diastole":81}]},{"date":null,"data":null}],"errno":0,"error":"Success."}

// http://localhost/shouhuan/public/api/Data/blood_pressure_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&date=["2018-09-10","2018-09-11"]

// https://shouhuan.taoyt.cn/api/Data/blood_pressure_get.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&date=["2018-09-10","2018-09-11"]


	public function blood_pressure_get(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$date=isset($_REQUEST['date'])?$_REQUEST['date']:''; //[天1,天2]
		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_blood_pressure= Db::name('jtq_data_blood_pressure');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($id==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}


		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){
			$date=is_string($date)?json_decode($date,true):$date;
			if(!$date){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['data']=array();
				$err['id']=$id;
				$err=json_encode($err);
				echo($err);
				exit();
			}

			$list=array();
			$list['id']=$id;
			$list['data']=array();

			foreach ($date as $key => $val) {
				$info=null;;

				$f_step=$jtq_data_blood_pressure->where(array('date'=>$val,'device_id'=>$id))->find();
				if(!$f_step){
					// 跳过没有的日期  否则会出现 {"date":null,"data":null}
					continue;
				}
				$info['date']=$f_step['date'];
				$info_data=$f_step['data']; //stripslashes
				$info_data=stripslashes($info_data);
				$info['data']=json_decode($info_data);
				$list['data'][]=$info;


			}

			
			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));



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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}

	}


// 运动保存
/*
http://39.108.219.58:8808/api/data/sport_save.php?token=MTAwMTEwfHx8MzhkMTQ3NjZlY2MxYjNmNTE0MjBiZmI1ZTI1YzE0MjA2OTg3ZWNhMXx8fDIwMTktMDgtMjMgMDA6Mzg6NDM=&id=3234567890ABCDEF&data=[{"type":1,"time":"2018-09-10","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]},{"type":1,"time":"2018-09-11 08:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]}]

{"time":["2018-09-10 08:00:00","2018-09-11 08:00:00"],"errno":0,"error":"Success."}
{"time":["2018-09-10","2018-09-11 08:00:00"],"errno":0,"error":"Success."}
{"date":[],"errno":0,"error":"Success."}

http://localhost/shouhuan/public/api/Data/sport_save.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&data=[{"type":1,"time":"2018-09-10","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]},{"type":1,"time":"2018-09-11 08:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]}]

https://shouhuan.taoyt.cn/api/Data/sport_save.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&data=[{"type":1,"time":"2018-09-10","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]},{"type":1,"time":"2018-09-11 08:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]}]

*/ 

	public function sport_save(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$data=isset($_REQUEST['data'])?$_REQUEST['data']:'';

		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_sport= Db::name('jtq_data_sport');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($data==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){

			// 将json字符串转换成数组
			$data=is_string($data)?json_decode($data,true):$data;

			$up_d=array();
			$up_d['time']=array();

			foreach ($data as $key => $val) {
				$arr_d=array();
				$flag_d=null;

				$arr_d['device_id']=$id;
				$arr_d['time']=isset($val['time'])?$val['time']:'';
				$arr_d['type']=isset($val['type'])?$val['type']:'';
				$arr_d['distance']=isset($val['distance'])?$val['distance']:'';
				$arr_d['speed']=isset($val['speed'])?$val['speed']:'';
				$arr_d['duration']=isset($val['duration'])?$val['duration']:'';
				$arr_d['calorie']=isset($val['calorie'])?$val['calorie']:'';
				$arr_d['min_hr']=isset($val['min_hr'])?$val['min_hr']:'';
				$arr_d['avg_hr']=isset($val['avg_hr'])?$val['avg_hr']:'';
				$arr_d['max_hr']=isset($val['max_hr'])?$val['max_hr']:'';


				$d_d=isset($val['path'])?$val['path']:'';
				$d_d=json_encode($d_d);
				$arr_d['path']=addslashes($d_d);


				$f_step=$jtq_data_sport->where(array('time'=>$val['time'],'device_id'=>$id))->find();
				if($f_step){
					$flag_d=1;
					$jtq_data_sport->where(array('time'=>$val['time'],'device_id'=>$id))->update($arr_d);
				}else{
					$flag_d=$jtq_data_sport->insert($arr_d);
				}
				if($flag_d){
					$up_d['time'][]=$val['time'];
				}

			}

			
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

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
				$err=array();
				$err['errno']=110;
				$err['error']="No auth!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

		}



	}





// 运动获取
// http://39.108.219.58:8808/api/data/sport_get.php?token=MTAwMTEwfHx8MDFhZDhjMWZkY2YwNTZiM2IxZTRkNWFlMWQ0MWQ4OTJmZDgyYjk2ZXx8fDIwMTktMDgtMjQgMDI6MDM6NTQ=&id=3234567890ABCDEF&begin=2018-09-10 00:00:00&end=2018-09-11 23:59:59&type=1
// {"id":"3234567890ABCDEF","data":[{"type":1,"time":"2018-09-10 00:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]},{"type":1,"time":"2018-09-10 08:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]},{"type":1,"time":"2018-09-11 08:00:00","distance":10000,"speed":500,"duration":50,"calorie":200,"min_hr":53,"avg_hr":65,"max_hr":100,"path":[{"duration":0,"lon":113.75,"lat":23.03},{"duration":10,"lon":113.75,"lat":23.03}]}],"errno":0,"error":"Success."}
// {"id":"3234567890ABCDEF","data":[],"errno":0,"error":"Success."}
// http://localhost/shouhuan/public/api/Data/sport_get.php?token=375b01b9f192f5349ac5a2c62a23ba72&id=1234567890ABCDEF&begin=2018-09-10 00:00:00&end=2018-09-11 23:59:59&type=1
	
// https://shouhuan.taoyt.cn/api/Data/sport_get.php?token=07084314a8570b2d4e5c76f7c102d411&id=3234567890ABCDEF&begin=2018-09-10 00:00:00&end=2018-09-11 23:59:59&type=1
	
	public function sport_get(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//设备序号；
		$begin=isset($_REQUEST['begin'])?$_REQUEST['begin']:''; 
		$end=isset($_REQUEST['end'])?$_REQUEST['end']:'';
		$type=isset($_REQUEST['type'])?$_REQUEST['type']:'';


		$jtq_device= Db::name('jtq_device');
		$jtq_user= Db::name('jtq_user');
		$jtq_data_sport= Db::name('jtq_data_sport');

		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($id==''||$begin==''||$end==''||$type==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}

		$device_id=$f_info['device_id'];
		$device_id = explode("__", $device_id);
		$is_in=in_array($id,$device_id);
		if($is_in){

			$list=array();
			$list['id']=$id;
			$list['data']=array();

			$where=array();
			$where_ands=array();

			$sql="time between  '".$begin."' and '".$end."' ";

			array_push($where_ands, "device_id =  '$id' ");
			array_push($where_ands, "type =  '$type' ");
			array_push($where_ands, "time between  '$begin' and '$end' ");
			$where= join(" and ", $where_ands);
			$sel_info=$jtq_data_sport->where($where)->select()->toArray();
			foreach ($sel_info as $key => $val) {
				$info=array();
					# code...
				$info['type']=$val['type'];
				$info['time']=$val['time'];
				$info['distance']=$val['distance'];
				$info['speed']=$val['speed'];
				$info['duration']=$val['duration'];
				$info['calorie']=$val['calorie'];
				$info['min_hr']=$val['min_hr'];
				$info['avg_hr']=$val['avg_hr'];
				$info['max_hr']=$val['max_hr'];
				$path=$val['path'];
				$path=stripslashes($path);
				$info['path']=json_decode($path);
				$list['data'][]=$info;

			}

			$list['errno']=0;
			$list['error']="Success.";

			print_r(json_encode($list));


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

