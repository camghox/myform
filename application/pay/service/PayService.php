<?php
namespace app\pay\service;

use think\Log;
use think\Db;

use app\pay\model\PayBase;
use app\pay\model\PayNotify;

class PayService
{
    const PRIMARY_KEY = "72fanke@peizheng";

    public function updatePayStat($trade_no, $pay_stat, $pay_no){
        $PayBase = model('PayBase');
    	$result = $PayBase->where(["trade_no"=>$trade_no])->find();
        if($result){
            if($pay_stat){
                $result->paystat = $pay_stat;
                $result->payno = $pay_no;
                $result->paytime = date('Y-m-d H:i:s');
            }
            return $result->save();
        }
        return false;
    }

    public function add($open_id, $content, $price, $indate, $outdate, $phone, $contact){
    	$trade_no = $this->gen_trade_no();
    	$PayBase = model('PayBase');
    	$result = $PayBase->save([
            'wx_openid'=>$open_id,
    		'trade_no'=>$trade_no,
            'indate'=>$indate,
            'outdate'=>$outdate,
    		'content'=>$content,
    		'price'=>$price,
            'phone'=>$phone,
            'contact'=>$contact,
    		'paystat'=>0,
    		'addtime'=>date('Y-m-d H:i:s')
    	]);
        if($result) return $trade_no;
        else return false;
    }

    public function get($trade_no){
        $PayBase = model('PayBase');
        $pay = $PayBase->where(["trade_no"=>$trade_no])->find();
        return $pay;
    }

    public function addNotify($content){
        $PayNotify = model('PayNotify');
        $result = $PayNotify->save([
            'trade_no'=>'',
            'content'=>$content,
            'addtime'=>date('Y-m-d H:i:s')
        ]);
        return $result;
    }
    public function gen_trade_no(){
        $t = (time().'');
    	return date('YmdHis').substr($t, strlen($t) - 4, 4);
    }
}
