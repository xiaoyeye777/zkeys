<?php
/**
 * Created by PhpStorm.
 * User: xiaoye
 * Email: 415907483@qq.com
 * Date: 2020/10/19
 * Time: 11:59
 */

namespace Niaoyun\Payment;

use libs\Curl;
use Niaoyun\Payment\PayInterface\Ipay;
use Detection\MobileDetect;

class YeWeChat implements Ipay
{

//    private $payment_method='Wechat';

    /**
     * 支付参数配置
     */
    private $config= [
        'appid'=>'',
        'appkey'=>'',
        'url'=>'https://pay.xiaooye.com',
    ];

    private $configAdmin= [
        'appid' =>[
            'name' =>'AppId',
            'show' =>true,
        ],
        'appkey'=>[
            'name'=>'AppKey',
            'show' =>true,
        ],
    ];

    public function __construct()
    {
        foreach ($this->configAdmin as $k => $v){
            $this->config[$k]= C('recharge')[substr(__CLASS__,strrpos(__CLASS__,'\\')+1).$k];
        }
    }

    public function getConfigAdmin()
    {
        return $this->configAdmin;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function pay($parameters)
    {
        $parameter = [
            'appid' => $this->config['appid'],//appid
            'out_trade_no'=>$parameters['out_trade_no'],//订单号
            'total_fee'=>$parameters['total_fee'],//支付金额
            'body'=>$parameters['body'],//商品名称
            'remark'=>C('basic.site_name'),//商品备注
        ];
        $parameter['sign']=  $this->sign($parameter, $this->config['appkey']);

//        $mobile = new MobileDetect();
////        检测是否是手机网站
//        if ($mobile->isMobile()) {
//            $getdata = http_build_query($parameter);
//            return [
//                'code'=>200,  //200表示成功,201 表示失败
//                'html_text'=>$this->config['url'] .'/api/WeiPayJS?' . $getdata, //返回的是跳转链接则用html_text字段，用于新窗口打开的页面
//                'msg'=>'success'  //成功success，失败fail
//            ];
//        }



        $data =  Curl::post($this->config['url'] . '/api/WeiCode',$parameter);
        $data = json_decode($data,true);
        if(empty($data['code']) ) return ['code'=>201, 'msg'=>'下单失败'];
        if($data['code']==1){
            $ret =[
                'code'=>200,  //200表示成功,201 表示失败
                'code_url'=>$data['code_url'], //支付方式返回的是二维码数据，并需要使用js生成二维码的则用code_url字段，用于 iframe 页面（生成二维码的js平台已集成，不用另外开发）
                'msg'=>'success'  //成功success，失败fail
            ];
            return $ret;
        }
        return ['code'=>201, 'msg'=>$data['msg']];
    }


    public function sign($params, $secret) {
        $sign = $signstr = "";
        if (!empty($params)) {
            ksort($params);
            reset($params);
            foreach ($params AS $key => $val) {
                if ($key == 'sign') continue;
                if ($signstr != '') {
                    $signstr.= "&";
                }
                $signstr.= "$key=$val";
            }
            $sign = md5($signstr . $secret);
        }
        return $sign;
    }
}