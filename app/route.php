<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------


// // yuyi 定义路由
// use think\Route;
// // 1.3.1注册
// Route::rule('api/user/register.php', 'api/user/register');
// // 1.3.2登陆
// Route::rule('api/user/login.php', 'api/register/login');
// Route::rule('api/register/login', 'api/register/login');
// // 1.3.3文件上传
// Route::rule('api/file/upload.php', 'api/register/upload');
// // 1.3.4个人信息
// Route::rule('api/user/info.php', 'api/register/getInfo');
// // 1.3.5密码修改
// Route::rule('api/user/password_modify.php', 'api/register/password_modify');
// // 1.3.6密码重置
// Route::rule('api/user/password_reset.php', 'api/register/password_reset');
// Route::rule('api/register/password_reset', 'api/register/password_reset');
// Route::rule('api/register/password_reset.php', 'api/register/password_reset');
// Route::rule('api/register/password_reset_ui.php', 'api/register/password_reset_ui');
// Route::rule('api/user/password_reset_ui.php', 'api/register/password_reset_ui');

// // 1.3.7设备绑定
// Route::rule('api/device/bind.php', 'api/device/bind');
// // 1.3.8设备解绑
// Route::rule('api/device/unbind.php', 'api/device/unbind');
// // 1.3.9设备信息
// Route::rule('api/device/info.php', 'api/device/info');
// // 1.3.10设备切换
// Route::rule('api/device/active.php', 'api/device/active');


// // 1.3.11步数保存
// Route::rule('api/data/step_save.php', 'api/data/step_save');
// // 1.3.12步数获取
// Route::rule('api/data/step_get.php', 'api/data/step_get');
// // 1.3.13睡眠保存
// Route::rule('api/data/sleep_save.php', 'api/data/sleep_save');
// // 1.3.14睡眠获取
// Route::rule('api/data/sleep_get.php', 'api/data/sleep_get');
// // 1.3.15心率保存
// Route::rule('api/data/heart_rate_save.php', 'api/data/heart_rate_save');
// // 1.3.16心率获取
// Route::rule('api/data/heart_rate_get.php', 'api/data/heart_rate_get');
// // 1.3.17血压保存
// Route::rule('api/data/blood_pressure_save.php', 'api/data/blood_pressure_save');
// // 1.3.18血压获取
// Route::rule('api/data/blood_pressure_get.php', 'api/data/blood_pressure_get');
// // 1.3.19运动保存
// Route::rule('api/data/sport_save.php', 'api/data/sport_save');
// // 1.3.20运动获取
// Route::rule('api/data/sport_get.php', 'api/data/sport_get');





if (file_exists(CMF_ROOT . "data/conf/route.php")) {
    $runtimeRoutes = include CMF_ROOT . "data/conf/route.php";
} else {
    $runtimeRoutes = [];
}

return $runtimeRoutes;