<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//api.tp5.test====>www.tp5.test/index.php/api
Route::domain('api', 'api');

//api.tp5.test/user====>www.tp5.test/index/api/user/login
Route::post('user', 'user/login');

//验证码
Route::get('code/:time/:token/:user_name/:is_exist', 'code/get_code');

//用户注册
Route::post('user/register', 'user/register');

//用户登录
Route::post('user/login', 'user/login');

//用户头像
Route::post('user/icon', 'user/upload_head_img');

//用户修改密码
Route::post('user/change_pwd', 'user/change_pwd');

//用户找回密码
Route::post('user/find_pwd', 'user/find_pwd');

//绑定手机号
Route::post('user/bind_phone', 'user/bind_phone');

//绑定邮箱
Route::post('user/bind_email', 'user/bind_email');

//绑定用户名或邮箱
Route::post('user/bind_username', 'user/bind_username');

//设置用户昵称
Route::post('user/nickname', 'user/set_nickname');

//添加文章
Route::post('article', 'article/add_article');

//文章列表
Route::get('articles/:time/:token/:user_id/[:num]/[:page]', 'article/article_list');

//单个文章
Route::get('article/:time/:token/:article_id', 'article/article_detail');

//修改/更新文章
Route::put('article', 'article/update_article');

//删除文章
Route::delete('article/:time/:token/:article_id', 'article/del_article');

//menu
Route::get('menu/menulist', 'menu/menulist');


Route::get('index/test','index/index/test');
