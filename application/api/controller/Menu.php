<?php
namespace app\api\controller;

class Menu extends Common
{
    public function menu_list()
    {
        /**********用户所在组**********/
        $user_groups = db('auth_group_access')->where('uid', session('uid'))->column('group_id');
        //dump($user_groups);die;
        /**********查询用户所在组的权限**********/
        $auth_menu = db('auth_group')->where('id', 'in', $user_groups)->column('rules');
        $auth_menu = implode(',', $auth_menu);
        // dump($auth_menu);die;
        /**********查询menu**********/
        $menus = db('auth_rule')->where('id', 'in', $auth_menu)->select();
        dump($menus);die;
        /**********将查询数据转为tree**********/
        $res = $this->tree($menus);
        $this->return_msg(200, 'success', $menus);
    }

    public function add_menu()
    {
        echo "add menu";
    }
}
