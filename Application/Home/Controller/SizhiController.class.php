<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 龙岗便民对接微信接口
 */
class SizhiController extends Controller{
    /**
     * 验证消息的确来自微信服务器，服务器配置需要用到
     */
    public function index(){
        date_default_timezone_set("Asia/Shanghai");
        file_put_contents('log.text',"\r\n".date('Y-m-d H:i:s',time())."-".json_encode($_REQUEST),FILE_APPEND);
        file_put_contents('log1.text',"\r\n".date('Y-m-d H:i:s',time()).file_get_contents("php://input"),FILE_APPEND);
        //1.将token、timestamp、nonce三个参数进行字典序排序
        $timestamp=$_GET['timestamp'];
        $nonce=$_GET['nonce'];
        $token='weixin';
        $signature=$_GET['signature'];
        $echostr=$_GET['echostr'];
        $array=array($timestamp,$nonce,$token);
        sort($array);
        //2.将三个参数字符串拼接成一个字符串进行sha1加密
        $tmpstr=implode('',$array);
        $tmpstr=sha1($tmpstr);
        //3.开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
        if($tmpstr==$signature && $echostr){
            //第一次接入微信api接口的时候，会去验证合法性
            echo $echostr;
            exit;
        }else{
            $this->reponseMsg();
        }
    }
    /*
     * 接收事件推送并回复
     */
    public function reponseMsg(){
        //1.获取微信推送过来的post数据（xml格式）
        $postArr=$GLOBALS['HTTP_RAW_POST_DATA'];
        //2.处理消息类型，并设置回复类型和内容
       /* <xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
</xml>*/
       $postObj=simplexml_load_string($postArr);//把xml标签转化为对象
       //$postObj->ToUserName='';获取对象的值
        //判断该数据包是否是订阅的事件推送(strtolower:字符串转化成小写)
        if(strtolower($postObj->MsgType)=='event'){
            //如果是关注subscribe事件
            if(strtolower($postObj->Event)=='subscribe'){
                //回复用户消息
                $toUser=$postObj->FromUserName;
                $fromUser=$postObj->ToUserName;
                $time=time();
                $msgType='text';
                $content="欢迎关注我们的微信公众号";
                $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                          </xml>";
                $info=sprintf($template,$toUser,$fromUser,$time,$msgType,$content);//将模板中的%s依次用变量替代
                echo $info;
            }
            //如果是merchant_order事件(支付通知)
            if(strtolower($postObj->Event)=='merchant_order'){
                $access_token=$this->getWxAccessToken();
                //根据订单ID获取订单详情
                $orderId=strtolower($postObj->OrderId);//将字符转化为小写
                $url='https://api.weixin.qq.com/merchant/order/getbyid?access_token='.$access_token;
                $postJson=json_encode(array('order_id'=>$orderId));
                $res=$this->http_curl($url,'post','json',$postJson);
                //发送模板消息
                //$openid=$res['order']['buyer_openid'];//买家微信OPENID
                $template_id="7u_svdYjV2C7k4tL7S39gsBbhFzIWbrlYCPGRZay6TU";//模板ID
                $product_name=$res['order']['product_name'];//商品名称
                $order_total_price=$res['order']['order_total_price']/100;
                $order_total_price=$order_total_price."元";//消费总金额
                $receiver_name=$res['order']['receiver_name'];//用户姓名
                $receiver_mobile=$res['order']['receiver_mobile'];//用户电话
                $receiver_address=$res['order']['receiver_address'];//配送地址
                $url_template='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
                $arr_openid=array(
                    0=>'oxQVwxLr2BIXW9t5jMdWdghAHvBI',//黄志涛
                    1=>'oxQVwxCpEhKPgJgiWFar46I-UE1E',//老板
                    2=>'oxQVwxALyL92fQW0VuimFm3DP3U8'//用户3
                );//商户接收订单信息的用户openid,可能有多个
                foreach($arr_openid as $openid){
                    $postTemplateJson=json_encode(array(
                        'touser'=>$openid,//接收者openid
                        "template_id"=>$template_id,
                        'data'=>array(
                            'first'=>array(
                                'value'=>'有位顾客下单成功了，请您在后台注意查收！',
                                'color'=>'#173177'
                            ),
                            'keyword1'=>array(
                                'value'=>$product_name,
                                'color'=>'#173177'
                            ),
                            'keyword2'=>array(
                                'value'=>$order_total_price,
                                'color'=>'#173177'
                            ),
                            'keyword3'=>array(
                                'value'=>$receiver_name,
                                'color'=>'#173177'
                            ),
                            'keyword4'=>array(
                                'value'=>$receiver_mobile,
                                'color'=>'#173177'
                            ),
                            'keyword5'=>array(
                                'value'=>$receiver_address,
                                'color'=>'#173177'
                            ),
                            'remark'=>array(
                                'value'=>'我们把最好、最快的产品交给消费者。客服热线：13534298677',
                                'color'=>'#173177'
                            )
                        )
                    ));
                    $res_template=$this->http_curl($url_template,'post','json',$postTemplateJson);
                    file_put_contents('template.text',json_encode($res_template),FILE_APPEND);
                }
            }
        }
        //输入关键字，回复文本
        if(strtolower($postObj->MsgType)=='text'){
            if(strtolower($postObj->Content)=='huchao'){
                $toUser=$postObj->FromUserName;
                $fromUser=$postObj->ToUserName;
                $time=time();
                $msgType='text';
                $content="huchao is very good";
                $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                          </xml>";
                $info=sprintf($template,$toUser,$fromUser,$time,$msgType,$content);//将模板中的%s依次用变量替代
                echo $info;
            }

        }
        if(strtolower($postObj->Event)=='click'){
            //如果是自定义菜单中的 event->click
            if(strtolower($postObj->EventKey)=='item1'){
                $content="敬请期待!!!";
            }
            if(strtolower($postObj->EventKey)=='item2'){
                $content="敬请期待!!!";
            }
            if(strtolower($postObj->EventKey)=='item3'){
                $content="13534298677";
            }
            $toUser=$postObj->FromUserName;
            $fromUser=$postObj->ToUserName;
            $time=time();
            $msgType='text';
            $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                          </xml>";
            $info=sprintf($template,$toUser,$fromUser,$time,$msgType,$content);//将模板中的%s依次用变量替代
            echo $info;
        }
        /*if(strtolower($postObj->Event)=='view'){
            //如果是自定义菜单中的 event->view
            $content="跳转链接是".$postObj->EventKey;
            $toUser=$postObj->FromUserName;
            $fromUser=$postObj->ToUserName;
            $time=time();
            $msgType='text';
            $template="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                          </xml>";
            $info=sprintf($template,$toUser,$fromUser,$time,$msgType,$content);//将模板中的%s依次用变量替代
            echo $info;
        }*/
    }
    /*
     * curl
     */
    /*public function http_curl(){
        //1.初始化curl
        $ch=curl_init();
        $url="http://www.baidu.com";
        //2.设置curl的参数
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//返回
        //3.采集
        $output=curl_exec($ch);
        //4.关闭
        curl_close($ch);
        var_dump($output);
    }*/
    /*
     * $url 接口url string
     * $type 请求类型 string
     * $res 返回数据类型 string
     * $arr 请求参数 string,array,json(根据需要传输的类型)
     */
    public function http_curl($url,$type='get',$res='json',$arr=''){
        //1.初始化curl
        $ch=curl_init();
        //2.设置curl的参数
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//返回
        if($type=='post'){
            curl_setopt($ch,CURLOPT_POST,1);//定义是post请求
            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);//传输的数组
        }
        //3.采集
        $output=curl_exec($ch);
        //4.关闭
        curl_close($ch);
        if(curl_errno($ch)){//错误码是什么
            //请求失败返回错误信息
            return curl_error($ch);//错误信息
        }else{
            //请求成功
            if($res=='json'){
                //如果返回的数据类型为json那么将他转化为数组
                //取值：值=返回的数组[键名]
                return json_decode($output,true);
            }
            if($res=='xml'){
                //如果返回的数据类型为xml那么将他转化为对象
                //取值：值=返回的对象->键名
                return simplexml_load_string($output);
            }
        }
    }
    /*
     * 获取微信的access_token
     * 调用access_token是有次数限制的，可以暂存session/cookie中
     */
    public function getWxAccessToken(){
        session_start();//初始化session
        //将access_token 存在session/cookie中
        if($_SESSION['access_token'] && $_SESSION['expire_time']>time()){//如果session中存在access_token，并且access_token的过期时间大于当前时间
            //如果access_token在session没有过期
            return  $_SESSION['access_token'];
        }else{
            //如果access_token不存在或者已经过期，重新取access_token
            //1.请求的url地址
            $appid='wxf913702ec3786c11';//开发者ID
            $appsecret='8ca679e38e3f002e78f1c5eb08e01808';//开发者密码
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $res=$this->http_curl($url);
            $access_token=$res['access_token'];
            //将重新获取到的 access_token存到session
            $_SESSION['access_token']=$access_token;
            $_SESSION['expire_time']=time()+7000;
            return $access_token;
        }
    }
    /*
     * 获取微信服务器IP地址
     */
    public function getWxServerIp(){
        $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=".$access_token;
        $arr=$this->http_curl($url);
        var_dump($arr);
    }
    /**
     * 创建微信菜单
     */
    public function definedItem(){
        //目前微信接口的调用方式都是通过curl post/get
        $access_token=$this->getWxAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $postArr=array(
            'button'=>array(
                array(
                    'name'=>urlencode('瓶装煤气'),
                   'sub_button'=>array(
                       array(
                            'name'=>urlencode('微信订气'),
                           'type'=>'view',
                           'url'=>'http://mp.weixin.qq.com/bizmall/mallshelf?id=&t=mall/list&biz=MzI5NTY3NTg0NQ==&shelf_id=4&showwxpaytitle=1#wechat_redirect',
                       )
                   ),
                ),
                array(
                    'name'=>urlencode('更多服务'),
                    'sub_button'=>array(
                        array(
                            'name'=>urlencode('领优惠券'),
                            'type'=>'click',
                            'key'=>'item1',
                        ),
                        array(
                            'name'=>urlencode('推荐有奖'),
                            'type'=>'click',
                            'key'=>'item2',
                        ),
                         array(
                             'name'=>urlencode('联系客服'),
                             'type'=>'click',
                             'key'=>'item3',
                         ),
                    ),
                ),
            ),
        );
        $postJson=urldecode(json_encode($postArr));
        $res=$this->http_curl($url,'post','json',$postJson);
        var_dump($res);
    }

}