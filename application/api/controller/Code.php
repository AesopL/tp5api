<?php

/**
 * @Author: AeopL
 * @Date:   2018-05-02 10:49:50
 * @Last Modified by:   AeopL
 * @Last Modified time: 2018-05-10 16:40:02
 */
namespace app\api\controller;

use alisms\SendSms;
use PHPMailer\PHPMailer\PHPMailer;
use think\Env;

class Code extends Common
{
    public function get_code()
    {
        $username = $this->params['user_name'];
        $exist = $this->params['is_exist'];
        $username_type = $this->check_username($username);
        /**********根据类型执行方法**********/
        switch ($username_type) {
            case 'phone':
                $this->get_code_by_username($username, 'phone', $exist);
                break;
            case 'email':
                $this->get_code_by_username($username, 'email', $exist);
        }
    }

    /**
     * 给手机/邮箱发送短信
     * @param  [string] $username [手机/邮箱]
     * @param  [string] $type [账号类型，手机号或者邮箱]
     * @param  [int] $exist    [手机号/邮箱是否应该存在数据库中1：是，0：否]
     * @return [json]           [api返回的json数据]
     */
    public function get_code_by_username($username, $type, $exist)
    {
        $type_name = $type == 'phone' ? '手机' : '邮箱';
        /**********检测手机号/邮箱是否存在**********/
        $this->check_exist($username, $type, $exist);
        /**********检查验证码请求频率 30秒一次**********/
        if (session("?" . $username . '_last_send_time')) {
            if (time() - session($username . '_last_send_time') < 30) {
                $this->return_msg(400, $type_name . '验证码30秒只能请求一次');
            }
        }
        /**********生成验证码**********/
        $code = $this->make_code(6);
        $md5_code = md5($username . '_' . $code);
        /**********使用session存储验证码，方便比对，md5加密**********/
        session($username . '_code', $md5_code);
        /**********使用session存储验证码发送时间**********/
        session($username . '_last_send_time', time());
        /**********发送验证码**********/
        if ($type == 'phone') {
            $this->send_code_to_phone($username, $code);
        } else {
            $this->send_code_to_email($username, $code);
        }
    }
    public function make_code($num)
    {
        $min = pow(10, $num - 1);
        $max = pow(10, $num) - 1;
        return rand($min, $max);
    }

    public function send_code_to_phone($phone, $code)
    {
        //echo $code;
        //获取对象，如果上面没有引入命名空间，可以这样实例化：$sms = new \alisms\SendSms()
        $sms = new SendSms();
        //设置关键的四个配置参数，其实配置参数应该写在公共或者模块下的config配置文件中，然后在获取使用，这里我就直接使用了。
        $sms->accessKeyId = Env::get('accessKeyId');
        $sms->accessKeySecret = Env::get('accessKeySecret');
        //签名
        $sms->signName = Env::get('signName');
        //模板
        $sms->templateCode = Env::get('templateCode');

        //$mobile为手机号
        $mobile = $phone;
        //模板参数，自定义了随机数，你可以在这里保存在缓存或者cookie等设置有效期以便逻辑发送后用户使用后的逻辑处理
        //$code = mt_rand();
        $templateParam = array("code" => $code);
        $m = $sms->send($mobile, $templateParam);
        //类中有说明，默认返回的数组格式，如果需要json，在自行修改类，或者在这里将$m转换后在输出
        //dump($m);die;
        if ($m['Code'] !== 'OK') {
            $this->return_msg(400, '短信发送失败');
        } else {
            $this->return_msg(200, '短信验证码发送成功,每天发送五次，请在一分钟内验证');

        }

    }
    /**
     * 向邮箱发送验证码
     * @param  [string] $email [目标邮箱]
     * @param  [stirng] $code  [验证码]
     * @return [json]        [返回发送结果]
     */
    public function send_code_to_email($email, $code)
    {
        $toemail = $email;
        //echo (extension_loaded('openssl') ? 'SSL loaded' : 'SSL not loaded');
        $mail = new PHPMailer();

        $mail->CharSet = 'utf8';
        //设置邮件使用SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.126.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'aesopl@126.com';
        $mail->Password = 'yWScUcVk8uiH5EV';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('aesopl@126.com', 'Aesopl接口测试');
        $mail->addAddress($toemail, 'test');
        $mail->addReplyTo('aesopl@126.com', 'Reply');
        $mail->Subject = '您有新的验证码';
        $mail->Body = "这是一个测试邮件，您的$code,验证码的有效期为1分钟，本邮件请勿回复";
        if (!$mail->send()) {
            $this->return_msg(400, $mail->ErrorInfo);
        } else {
            $this->return_msg(200, '验证码已经发送成功，请注意查收!');
        }

    }
}
