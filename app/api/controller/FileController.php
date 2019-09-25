<?php
/*
用户接口

*/ 


namespace app\api\controller;

use think\Db; // /thinkphp/library/think/Loader.php
use cmf\controller\HomeBaseController;
class FileController extends HomeBaseController
{


	public function upload_test(){

		return $this->fetch('file/upload');
		// return $this->fetch('file/text');

	}



// 1.3.3文件上传
// http://localhost/shouhuan/public/api/file/upload.php?token=d4e3a98e5ed367ece367ec80dee11023
// d4e3a98e5ed367ece367ec80dee11023

// http://39.108.219.58:8808/api/file/upload.html
// http://39.108.219.58:8808/api/file/upload.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=
// {"errno":102,"error":"Invalid token!!!"}
// {"errno":105,"error":"Lack of parameters!!!"}
// {"url":"http:\/\/39.108.219.58:8808\/upload\/2019\/08\/26\/100110_20190826054131423.png","errno":0,"error":"Success."}

// {"errno":0,"error":"Success.","url":"http:\/\/localhost\/shouhuan\/public\/upload\/jtq_avatar\/4_1566798215.jpg"}

// https://shouhuan.taoyt.cn/api/user/upload.php?token=d4e3a98e5ed367ece367ec80dee11023

	public function upload(){

		$jtq_user= Db::name('jtq_user');
		$arr=array();
		$data=isset($_REQUEST['token'])?$_REQUEST:'';

		if(!$data){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
		}else{

			$where=array();
			$where['token']=$data['token'];
			$g_info=$jtq_user->where($where)->find();
			if(!$g_info){
				$err=array();
				$err['errno']=102;
				$err['error']="Invalid token!!!";
				$err=json_encode($err);
				echo($err);
			}else{

				if(!isset($_FILES['file'])){
					$err=array();
					$err['errno']=103;
					$err['error']="Invalid parameter!!!";
					$err=json_encode($err);
					echo($err);
					exit();

				}
				$imgname = $_FILES['file']['name'];
			    $tmp = $_FILES['file']['tmp_name'];
			    $ss = explode('.',$imgname);
			    $ss =array_reverse($ss);

			    $avatar='upload/jtq_avatar/'.$g_info['id'].'_'.time().'.'.$ss[0];
				$filepath = CMF_ROOT.'public/'. $avatar;

				// // 同名的覆盖，不用写删除,因为加了time所以需要手动删除
				// $g_avatar=substr($g_info['avatar'],strripos($g_info['avatar'],"public/"));
				// $g_avatar=CMF_ROOT. $g_avatar;
				// if(is_file($g_avatar))
				// {
				// 	$u1=unlink($g_avatar);
				// }

			    if(move_uploaded_file($tmp,$filepath)){
			        $arr_up=array();

			        $arr_up['avatar']= 'http://localhost/shouhuan/public/'.$avatar;
			        // $arr_up['avatar']= 'https://shouhuan.taoyt.cn/'.$avatar;
			        // 不更新
			        // $up_info= $jtq_user->where(array('id'=>$g_info['id']))->update($arr_up);
			        $err=array();
					$err['errno']=0;
					$err['error']="Success.";
					$err['url']=  $arr_up['avatar'];
					$err=json_encode($err);
					echo($err);

			    }else{
			    	echo("上传失败！");
			    }

			}

		}

	}




}

