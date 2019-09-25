<?php
namespace app\shouhuan\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class ShowController extends AdminBaseController
{



	public function index(){

		$jtq_user= Db::name('jtq_user');
		$jtq_device=Db::name('jtq_device');
		$where=array();
		$where_ands=array();
		$params=$this->request->param();
		if(isset($params['alias'])&&!empty($params['alias'])){
			$alias=$params['alias'];
			$alias="%$alias%";
			array_push($where_ands, "alias like '$alias'");
			$where= join(" and ", $where_ands);

		}
		
		$count=$jtq_user->where($where)->count();
/*
->paginate(20,false,['query'=>request()->param()])
*/ 
    	$lists = $jtq_user
    	->order("id DESC")
    	->where($where)
    	->paginate(20)->each(function($item, $key){

    		$jtq_device=Db::name('jtq_device');
    		$active_id=$item['active_id'];
    		$info_def=$jtq_device->where(array('id'=>$active_id))->find();
			$item['active_name']=$info_def['name'];
			$item['active_device']=$info_def['device'];
			$item['active_alias']=$info_def['alias'];

			return $item;

    	});

    	$this->assign('lists', $lists);
    	$this->assign('count', $count);
		$lists->appends($params);
		$this->assign('page', $lists->render());
		return $this->fetch();

	}


/*
设备列表 & 基本信息 与定位
*/ 

	public function show_device(){

		$param=$this->request->param();
		$token=$param['token'];
		$jtq_user= Db::name('jtq_user');
		$jtq_device=Db::name('jtq_device');

		$info=$jtq_user->where(array('token'=>$token))->find();
		$device_id=$info['device_id'];
		$lists=array();
		if($device_id){
			$device_id = explode("__", $device_id);
			foreach ($device_id as $key => $val) {
				$ff=array();
				$id=$val;
				$ff=$jtq_device->where(array('id'=>$id))->find();
				if($ff){
					$lists[]=$ff;
				}
			}
			$this->assign('lists', $lists);
			return $this->fetch();
		}else{
			$this->error("暂无设备数据！");
		}

	}



/*
展示设备步数
*/ 
	public function show_device_step(){

		$param=$this->request->param(); // 另一种写法 $device_id=request()->param('device_id');
		
		$device_id= $param['device_id']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_step=Db::name('jtq_data_step');

		// 日期检索 与 前台返回走error时 冲突
		$where=array();
		$where['device_id']=$device_id;
		if(isset($param['sel_date'])){
			$where['date']=$param['sel_date'];
			$this->assign('sel_date', $param['sel_date']);
		}

		// 分页展示
		$f_step=$jtq_data_step->where($where)->order("date desc ,time desc ")
		->paginate(20)->each(function($val_1, $key){

				$info_data=$val_1['data'];
				$info_data=stripslashes($info_data);
				$info_data=json_decode($info_data,true);
				$val_1['data']=$info_data;
				$step=0;
				// 先转换格式 之后判断是否存在，因为判断原格式总是有值。
				if($info_data){
					
					foreach ($info_data as $k2 => $val2) {
						$step+=$val2['step'];
					}
				}
				// else{
				//  // each中不能用报错:  Cannot break/continue 1 level
				// 	continue;
				// }
				$val_1['stepAll']=$step;
				return $val_1;

		});
		
			// 判断是否有 $f_step->count()

			$this->assign('f_step', $f_step);
			$this->assign('device_id', $device_id);
			$f_step->appends($param);
			$this->assign('page', $f_step->render());

			return $this->fetch();

		// 	// ,url('show/show_device_step',array('device_id'=>$device_id))
		// 	// $this->error("暂无步数数据！");
		// 	return $this->fetch('error/error');
		

	}


/*
睡眠数据
*/ 
	public function show_device_sleep(){

		$param=$this->request->param();
		$device_id= $param['device_id']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_sleep=Db::name('jtq_data_sleep');

		$where=array();
		$where['device_id']=$device_id;

		$f_sleep=$jtq_data_sleep->where($where)->order("date desc ,time desc ")
		->paginate(20)->each(function($val_1, $key){
				$info_data=$val_1['data'];
				$info_data=stripslashes($info_data);
				$info_data=json_decode($info_data,true);
				$val_1['data']=$info_data;
				return $val_1;
		});

		if($f_sleep->count()){

			$this->assign('f_sleep', $f_sleep);
			$this->assign('device_id', $device_id);
			$f_sleep->appends($param);
			$this->assign('page', $f_sleep->render());
			return $this->fetch();
			// 不分页  则用  ->select()->toArray(); 配合foreach

		}else{
			$this->error("暂无步数数据！");
		}

		

	}



/*
心率数据
由于是拆分data数组，条数不定  所以不能分页,只能用筛选
*/ 
	public function show_device_heart_rate(){

		$param=$this->request->param();
		$device_id= $param['device_id']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_heart_rate=Db::name('jtq_data_heart_rate');
		$where=array();
		$where['device_id']=$device_id;
		if(isset($param['sel_date'])){
			$where['date']=$param['sel_date'];
			$this->assign('sel_date', $param['sel_date']);
		}


		$f_heart_rate=$jtq_data_heart_rate->where($where)->order("date desc")->select()->toArray();

		if($f_heart_rate){

			$info=array();
			foreach ($f_heart_rate as &$val_1) {
				$info_data=$val_1['data'];
				$info_data=stripslashes($info_data);
				$info_data=json_decode($info_data,true);
				foreach ($info_data as $k2 => $val2) {
					$info2=array();

					$info2['device_id']=$val_1['device_id'];
					$info2['date']=$val_1['date'];
					$info2['time']=$val2['time'];
					$info2['bmp']=$val2['bmp'];
					$info[]=$info2;

				}
				
			}

			$f_heart_rate=$info;
			$this->assign('f_heart_rate', $f_heart_rate);
			$this->assign('device_id', $device_id);
			return $this->fetch();

		}else{
			$this->error("暂无心率数据！");
		}

		

	}


/*
血压数据
由于是拆分data数组，条数不定  所以不能分页,只能用筛选
*/ 
	public function show_device_blood_pressure(){

		// 

		$param=$this->request->param();
		$device_id= $param['device_id']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_blood_pressure=Db::name('jtq_data_blood_pressure');

		$where=array();
		$where['device_id']=$device_id;
		if(isset($param['sel_date'])){
			$where['date']=$param['sel_date'];
			$this->assign('sel_date', $param['sel_date']);
		}

		$f_blood_pressure=$jtq_data_blood_pressure->where($where)->order("date desc")->select()->toArray();

		if($f_blood_pressure){

			$info=array();
			foreach ($f_blood_pressure as &$val_1) {
				$info_data=$val_1['data'];
				$info_data=stripslashes($info_data);
				$info_data=json_decode($info_data,true);
				// $val_1['data']=$info_data;
				// unset($val_1['data']);
				foreach ($info_data as $k2 => $val2) {
					$info2=array();
					$info2['device_id']=$val_1['device_id'];
					$info2['date']=$val_1['date'];
					$info2['time']=$val2['time'];
					$info2['shrink']=$val2['shrink'];
					$info2['diastole']=$val2['diastole'];
					$info[]=$info2;

				}
				
			}


			$f_blood_pressure=$info;
			$this->assign('device_id', $device_id);
			$this->assign('f_blood_pressure', $f_blood_pressure);
			return $this->fetch();


		}else{
			$this->error("暂无血压数据！");
		}

	}


/*
运动数据
*/ 
	public function show_device_sport(){

		$param=$this->request->param();
		$device_id= $param['device_id']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_sport=Db::name('jtq_data_sport');

		$where=array();
		$where['device_id']=$device_id;

		$f_sport=$jtq_data_sport->where($where)->order("time desc")
		->paginate(20)->each(function($val_1, $key){

				$info_data=$val_1['path'];
				$info_data=stripslashes($info_data);
				$info_data=json_decode($info_data,true);
				$val_1['path']=$info_data;
				// if($info_data){
				// 	foreach ($info_data as $k2 => $v2) {

				// 		$log=$v2['lon'];
				// 	}
					
				// }
				// else{
				// 	continue;
				// }
				
				return $val_1;

		});
		
		if($f_sport->count()){

			$this->assign('f_sport', $f_sport);
			$this->assign('device_id', $device_id);
			$f_sport->appends($param);
			$this->assign('page', $f_sport->render());

			return $this->fetch();


		}else{
			$this->error("暂无运动数据！");
		}
		

	}


/*
展示运动路径
R4DBZ-SJWC4-HYAU6-XHFRK-3ZD6J-BCBOX

*/ 


	public function map_sport_path(){


		$param=$this->request->param();
		$device_id= $param['device_id']; 
		$time= $param['time']; 
		$jtq_device=Db::name('jtq_device');
		$jtq_data_sport=Db::name('jtq_data_sport');
		$f_sport=$jtq_data_sport->where(array('device_id'=>$device_id,'time'=>$time))->find();

		if(!empty($f_sport)&&!empty($f_sport['path'])){
			$info_data=null;
			$info_data=$f_sport['path'];
			$info_data=stripslashes($info_data);
			$info_data=json_decode($info_data,true);
			$info_data = $this->arraySequence($info_data,'duration');

			// print_r(json_encode($info_data[0]));
			// 只展示最新一条
			// $this->assign('info_data', json_encode($info_data[0]));
			// 传全部
			$this->assign('info_data', json_encode($info_data));


		}else{
			$this->error("暂无运动轨迹！");
		}

		return $this->fetch();


	}


// 用户定位  只获取最后一次
	public function map_location(){


		$param=$this->request->param();
		$token= $param['token']; // add_time
		$jtq_user_location=Db::name('jtq_user_location');

		$info=$jtq_user_location->where(array('token'=>$token))->order("add_time desc")->find();
		if($info){
			$add_time=$info['add_time'];
			$add_time=date("Y-m-d H:i:s",$add_time);

			$location=$info['location'];
			$location=stripslashes($location);

			$this->assign('add_time', $add_time);
			$this->assign('location', $location);

			return $this->fetch();

		}else{
			$this->error("暂无定位数据！");
		}


		

	}









/*
 二维数组排序  一个子段
 $f_heart_rate=$this->arraySequence($f_heart_rate,'date');
 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
*/
	function arraySequence($array, $field, $sort = 'SORT_DESC') {
		 $arrSort = array();
		 foreach ($array as $uniqid => $row) {
		  foreach ($row as $key => $value) {
		   $arrSort[$key][$uniqid] = $value;
		  }
		 }
		 array_multisort($arrSort[$field], constant($sort), $array);
		 return $array;
	}


/**
 * 二维数组按照指定的多个字段进行排序
 *
 * 调用示例：sortArrByManyField($arr,'id',SORT_ASC,'age',SORT_DESC);

   但是不好使啊  一直报错  unexpected '$args' 


	function sortArrByManyField(){
		 $args = func_get_args();
		 if(empty($args)){
		  return null;
		 }
		 $arr = array_shift($args);
		 if(!is_array($arr)){
		  throw new Exception("第一个参数应为数组");
		 }
		 foreach($args as $key => $field){
		  if(is_string($field)){
		   $temp = array();
		   foreach($arr as $index=> $val){
		    $temp[$index] = $val[$field];
		   }
		   $args[$key] = $temp;
		  }
		 }
		 $args[] = &$arr;//引用值
		 call_user_func_array('array_multisort',$args);
		 return array_pop($args);
	}
 */






}

