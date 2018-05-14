<?php
namespace app\api\controller;

class Index
{
    public function index($id)
    {
        echo $id;
        return 'api/index';
    }
}
