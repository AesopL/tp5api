<?php

/**
 * @Author: AeopL
 * @Date:   2018-05-09 13:31:18
 * @Last Modified by:   AeopL
 * @Last Modified time: 2018-05-09 15:58:59
 */
namespace app\api\controller;

/**
 *
 */
class Article extends Common
{
    public function add_article()
    {
        /**********接收参数**********/
        $data = $this->params;
        /**********创建时间**********/
        $data['article_ctime'] = time();
        /**********存入数据库并获得id**********/
        $res = db('article')->insertGetId($data);
        if ($res) {
            $this->return_msg(200, '新增文章成功', $res);
        } else {
            $this->return_msg(400, '新增文章失败');
        }
    }

    /**
     * 文章列表
     * @return [json] [api返回的数据信息]
     */
    public function article_list()
    {
        /**********接收参数**********/
        $data = $this->params;
        /**********判断是否有页码和条数**********/
        $data['num'] = isset($data['num']) && !empty($data['num']) ? $data['num'] : 10;

        $data['page'] = isset($data['page']) && !empty($data['page']) ? $data['page'] : 1;
        //dump($data);die;
        $where['article_uid'] = $data['user_id'];
        $where['article_isdel'] = 0;
        $count = db('article')->where($where)->count();
        $page_num = ceil($count / $data['num']);
        $field = 'article_id,article_ctime,article_title,user_nickname';
        $join = [['tp5api_user u', 'a.article_uid=u.user_id']];
        $res = db('article')->alias('a')->join($join)->field($field)->where($where)->page($data['page'], $data['num'])->select();
        /**********判断输出**********/
        if ($res === false) {
            $this->return_msg(400, '查询失败!');
        } elseif (empty($res)) {
            $this->return_msg(200, '暂无数据');
        } else {
            $return_data['data'] = $res;
            $return_data['page_num'] = $page_num;
            $this->return_msg(200, '查询成功', $return_data);
        }
    }

    public function article_detail()
    {
        /**********接收参数**********/
        $data = $this->params;
        /**********查询数据库**********/
        $where['article_id'] = $data['article_id'];
        $field = 'article_id,article_title,article_content,article_ctime,user_nickname';
        $join = [['tp5api_user u', 'u.user_id=a.article_uid']];
        $res = db('article')->alias('a')->join($join)->field($field)->where($where)->find();
        if (!$res) {
            $this->return_msg(400, '查询失败');
        } else {
            $res['article_content'] = htmlspecialchars_decode($res['article_content']);
            $this->return_msg(200, '查询成功', $res);
        }
    }

   /**
    * 更新文章
    *
    * @return json
    */
    public function update_article()
    {
        /**********接收参数**********/
        $data = $this->params;
        /**********存入数据库**********/
        $res = db('article')->where('article_id', $data['article_id'])->update($data);
        if ($res !== false) {
            $this->return_msg(200, '文章修改成功！');
        } else {
            $this->return_msg(400, '文章修改失败！');
        }
    }

    /**
     * 删除文章
     *
     * @return json
     */
    public function del_article()
    {
        /**********接收参数**********/
        $data = $this->params;
        /**********删除数据(逻辑删除)**********/
        $res = db('article')->where('article_id', $data['article_id'])->setField('article_isdel', 1);
        /**********删除数据(物理删除)**********/
        //$res = db('article')->delete($data['article_id']);
        if ($res !== false) {
            $this->return_msg(200, '文章删除成功');
        } else {
            $this->return_msg(400, '文章删除失败');
        }
    }
}
