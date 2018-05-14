<?php
namespace app\api\controller;

use think\auth\Auth;
use think\Controller;
use think\Db;
use think\Image;
use think\Request;
use think\Validate;

/**
 *
 */
class Common extends Controller
{
    protected $request;
    protected $validater; //用来验证参数/数据
    protected $params; //过滤后符合要求的参数
    protected $rules = [
        'User' => [
            /**********登陆**********/
            'login' => [
                'user_name' => ['require'],
                'user_pwd' => ['require', 'length' => 32],
            ],
            /**********注册**********/
            'register' => [
                'user_name' => ['require'],
                'user_pwd' => ['require', 'length' => 32],
                'code' => ['require', 'number', 'length' => 6],
            ],
            /**********上传头像**********/
            'upload_head_img' => [
                'user_id' => 'require',
                'user_icon' => 'require|image|fileSize:2000000|fileExt:jpg,png,bmp,jpeg',
            ],
            /**********修改密码**********/
            'change_pwd' => [
                'user_name' => ['require'],
                'user_ini_pwd' => ['require', 'length' => 32],
                'user_pwd' => ['require', 'length' => 32],
            ],
            /**********找回密码**********/
            'find_pwd' => [
                'user_name' => ['require'],
                'user_pwd' => ['require', 'length' => 32],
                'code' => ['require', 'number', 'length' => 6],
            ],
            /**********绑定手机**********/
            'bind_phone' => [
                'user_id' => ['require', 'number'],
                'phone' => ['require', 'regex' => '/^1[345678]\d{9}/'],
                'code' => ['require', 'number', 'length' => 6],
            ],
            /**********绑定邮箱**********/
            'bind_email' => [
                'user_id' => ['require', 'number'],
                'email' => ['require', 'email'],
                'code' => ['require', 'number', 'length' => 6],
            ],
            'bind_username' => [
                'user_id' => ['require', 'number'],
                'code' => ['require', 'number', 'length' => 6],
                'username' => ['require'],
            ],
            'set_nickname' => [
                'user_id' => ['require', 'number'],
                'user_nickname' => ['require', 'chsDash'],
            ],
        ],
        /**********验证码**********/
        'Code' => [
            'get_code' => [
                'user_name' => 'require',
                'is_exist' => 'require|number|length:1',
            ],
        ],
        /**********文章**********/
        'Article' => [
            'add_article' => [
                'article_uid' => ['require', 'number'],
                'article_title' => ['require', 'chsDash'],
            ],
            'article_list' => [
                'user_id' => ['require', 'number'],
                'num' => ['number'],
                'page' => ['number'],
            ],
            'article_detail' => [
                'article_id' => ['require', 'number'],
            ],
            'update_article' => [
                'article_id' => ['require', 'number'],
                'article_title' => ['chsDash'],
            ],
            'del_article' => [
                'article_id' => ['require', 'number'],
            ],
        ],
        /**********menu导航**********/
        'Menu' => [
            'menu_list' => [
            ],
        ],
    ];
    public function _initialize()
    {
        parent::_initialize();
        $this->request = Request::instance();
        // $this->check_auth();
        // $this->check_time($this->request->only('time'));
        // $this->check_token($this->request->param());
        $this->params = $this->check_params($this->request->param(true));
    }

    /**
     * 验证时间戳
     * @param  [array] $arr [包含时间戳的$arr]
     * @return [json]      [检测结果]
     */
    public function check_time($arr)
    {
        if (!isset($arr['time']) || intval($arr['time']) <= 1) {
            $this->returnMsg(400, '时间戳不正确');
        }

        if (time() - intval($arr['time']) > 60) {
            $this->returnMsg(401, '验证超时');
        }
    }
    /**
     * api数据返回
     * @param  [int] $code [结果码  200：成功/4**：数据问题/5**:服务器问题]
     * @param  string $msg  [提示信息]
     * @param  array  $data [数据]
     * @return [json]       [组合提示信息]
     */
    public function return_msg($code, $msg = '', $data = [])
    {
        /*********组合数据*********/
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        /*********返回并终止脚本*******/
        echo json_encode($return_data);die;
    }
    /**
     * [验证token(防止数据篡改)]
     * @param  [array] $arr [全部请求参数]
     * @return [json]      [token验证结果]
     */
    public function check_token($arr)
    {
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg('400', 'token值不能为空');
        }
        //api传过来的token
        $app_token = $arr['token'];
        /***********服务端生成token**********/
        unset($arr['token']);
        $service_token = '';
        foreach ($arr as $key => $value) {
            $service_token .= md5($value);
        }
        $service_token = md5('api_' . $service_token . 'api_');
        //dump($service_toke);die;
        if ($app_token !== $service_token) {
            $this->return_msg(400, 'token值不正确');
        }
    }

    /**
     * [验证参数 参数过滤]
     * @param  [array] $arr [除time和token以外的参数]
     * @return [return]      [合格的参数数据]
     */
    public function check_params($arr)
    {
        /************获取参数的验证规则***********/
        $rule = $this->rules[$this->request->controller()][$this->request->action()];
        $this->validater = new Validate($rule);
        if (!$this->validater->check($arr)) {
            $this->return_msg(400, $this->validater->getError());
        }
        return $arr;
    }
    /**
     * 检测用户名并返回用户类型
     * @param  [string] $username [用户名可能是邮箱，也可能是手机号]
     * @return [string]           [email/phone]
     */
    public function check_username($username)
    {
        /*********检测是否为email*********/
        $is_email = Validate::is($username, 'email') ? 1 : 0;
        /*********检测是否为phone*********/
        $is_phone = preg_match('/^1[345678]\d{9}/', $username) ? 4 : 2;
        $flag = $is_email + $is_phone;
        switch ($flag) {
            case 2:
                $this->return_msg(400, '邮箱或手机号不正确');
                break;
            case 3:
                return 'email';
                break;
            case 4:
                return 'phone';
                break;
        }
    }
    /**
     * 手机号/邮箱的检测结果
     * @param  [string] $value [手机号/邮箱]
     * @param  [string] $type  [用户类型:手机/邮箱]
     * @param  [int] $exist [0/1]
     * @return [return]        [返回提示信息]
     */
    public function check_exist($value, $type, $exist)
    {
        $type_num = $type == 'phone' ? 2 : 4;
        $flag = $type_num + $exist;
        $phone_res = db('user')->where('user_phone', $value)->find();
        $email_res = db('user')->where('user_email', $value)->find();
        switch ($flag) {
            case 2:
                if ($phone_res) {
                    $this->return_msg(400, '此手机号已被占用');
                }
                break;
            case 3:
                if (!$phone_res) {
                    $this->return_msg(400, '此手机号不存在');
                }
                break;
            case 4:
                if ($email_res) {
                    $this->return_msg(400, '此邮箱已被占用');
                }
                break;
            case 5:
                if (!$email_res) {
                    $this->return_msg(400, '此邮箱不存在');
                }
                break;
        }
    }

    /**
     * 检查验证码
     * @param  [string] $user_name [用户名]
     * @param  [int] $code      [验证码]
     * @return [json]            [api返回的json信息]
     */
    public function check_code($user_name, $code)
    {
        /**********检查验证码是否超时**********/
        $last_time = session($user_name . '_last_send_time');
        if (time() - $last_time > 600) {
            $this->return_msg(400, '验证码超时，请在一分钟内验证');
        }

        /**********检查验证码是否正确**********/
        $md5_code = md5($user_name . '_' . $code);
        if (session($user_name . '_code') !== $md5_code) {
            $this->return_msg(400, '验证码不正确!');
        }
        /**********不管正确与否，每个验证码值验证一次**********/
        session($user_name . '_code', null);
    }

    public function upload_file($file, $type = '')
    {
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
            $path = '/uploads/' . $info->getSaveName();
            if (!empty($type)) {
                $this->image_edit($path, $type);
            }
            return str_replace('\\', '/', $path);
        } else {
            $this->return_msg(400, $file->getError());
        }
    }
    /**
     * [图片裁剪]
     * @param  [string] $path [图片路径]
     * @param  [string] $type [图片场景]
     *
     */
    public function image_edit($path, $type)
    {
        //dump(ROOT_PATH . 'public' . $path);die;
        $image = Image::open(ROOT_PATH . 'public' . $path);
        switch ($type) {
            case 'head_img':
                $image->thumb(200, 200, Image::THUMB_CENTER)->save(ROOT_PATH . 'public' . $path);
                break;
        }
    }
    /**
     * 检查用户权限
     *
     * @return [json]    api返回的json信息
     */
    public function check_auth()
    {
        $allow = ['api/User/login', 'api/User/logout'];
        $module = $this->request->module();
        //dump($module);die;
        $controller = $this->request->controller();
        $action = $this->request->action();
        $route = $module . '/' . $controller . '/' . $action;
        $auth = new Auth();
        if (!in_array($route, $allow)) {
            if (!$auth->check($controller . '-' . $action, session('uid'))) {
                $this->return_msg(400, '你没有权限访问');
            }
        }
    }

    /**
     * 无限极分类
     * @param  [array]  &$list [数组]
     * @param  integer $pid   [父级id]
     * @param  integer $level [分级]
     * @param  string  $html  [横线的长度]
     * @return [type]         [分类后的]
     */
    public function tree(&$list, $pid = 0, $level = 0, $html = '--')
    {
        static $tree = array();
        foreach ($list as $v) {
            if ($v['pid'] == $pid) {
                $v['sort'] = $level;
                $v['html'] = str_repeat($html, $level);
                $tree[] = $v;
                $this->tree($list, $v['id'], $level + 1);
            }
        }
        return $tree;
    }

    /**
     * 此方法由@Tonton 提供
     * http://my.oschina.net/u/918697
     * @date 2012-12-12
     */
    public function genTree5($items)
    {
        foreach ($items as $item) {
            $items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];
        }

        return isset($items[0]['son']) ? $items[0]['son'] : array();
    }

    /**
     * 将数据格式化成树形结构
     * @author Xuefen.Tong
     * @param array $items
     * @return array
     */
    public function genTree9($items)
    {
        $tree = array(); //格式化好的树
        foreach ($items as $item) {
            if (isset($items[$item['pid']])) {
                $tree[$item['pid']]['son'][] = &$items[$item['id']];
                //dump($items);
            } else {
                $tree[] = &$items[$item['id']];
            }
        }

        return $tree;
    }
}
