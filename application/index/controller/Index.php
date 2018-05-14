<?php
namespace app\index\controller;

use QL\Ext\PhantomJs;
use QL\QueryList;

class Index
{
    public function index()
    {
        $ql = QueryList::getInstance();
        $ql->use(PhantomJs::class, 'D:/dev/phpext/phantomjs.exe');
        // $rules = [
        //     'title' => ['.ctx-box>.title>a', 'text'],
        //     'link' => ['.ctx-box>.title>a', 'href'],
        // ];
        // $data = $ql->browser('https://s.taobao.com/search?q=MAC&imgfile=&commend=all&ssid=s5-e&search_type=item&sourceId=tb.index&spm=a21bo.2017.201856-taobao-item.1&ie=utf8&initiative_id=tbindexz_20170306&sort=sale-desc')->rules($rules)->query()->getData();
        // print_r($data->all());

        $item_sell_count_url = 'https://mdskip.taobao.com/core/initItemDetail.htm?isUseInventoryCenter=false&cartEnable=true&service3C=false&isApparel=false&isSecKill=false&tmallBuySupport=true&isAreaSell=false&tryBeforeBuy=false&offlineShop=false&itemId=558803677297&showShopProm=false&cachedTimestamp=1525329984098&isPurchaseMallPage=false&isRegionLevel=false&household=false&sellerPreview=false&queryMemberRight=true&addressLevel=2&isForbidBuyItem=false&callback=setMdskip&timestamp=1525332556900&isg=null&isg2=BK6u-BNKeHMOr49SoNpJHNy8_wS61jO_V8bfGNh2FbFvu0oVQz3hu3XadydXY2rB&ref=https%3A%2F%2Fs.taobao.com%2Fsearch%3Fq%3DMAC%26imgfile%3D%26commend%3Dall%26ssid%3Ds5-e%26search_type%3Ditem%26sourceId%3Dtb.index%26spm%3Da21bo.2017.201856-taobao-item.1%26ie%3Dutf8%26initiative_id%3Dtbindexz_20170306%26sort%3Dsale-desc';
        $jsonStr = $ql->get($item_sell_count_url)->encoding('UTF-8')->find('sellCount');
        $json_arr = (array) $jsonStr;
        var_dump($json_arr);
        // $json = json_decode($jsonStr);
        // print_r($json);

        // foreach (array_slice($data->all(), 2) as $key => $value) {
        //     // var_dump($value['link']);
        //     $item_data = $ql->get('https:' . $value['link'])->encoding('UTF-8')->find('#detail')->html();
        //     print_r($item_data);
        //     //print_r($value['title'] . '----' . $item_data);
        //     $ql->destruct();
        // }
    }

    public function object_to_array($obj)
    {
        $obj = (array) $obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array) object_to_array($v);
            }
        }

        return $obj;
    }

    function test(){
       return View();
    }
}
