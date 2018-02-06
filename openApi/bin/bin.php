<?php
require_once dirname(__FILE__) . "/../lib/wechat/other/helper.php";
require_once dirname(__FILE__) . "/../lib/wechat/crypt/WXBizMsgCrypt.php";
require_once dirname(__FILE__) . "/../lib/wechat/other/app_api.php";
require_once dirname(__FILE__) . "/../lib/mylib/msg.php";
require_once dirname(__FILE__) . "/../lib/mylib/msgTypeCode.php";
require_once dirname(__FILE__) . "/../lib/mylib/doScancodeWaitMsg.php";
require_once dirname(__FILE__) . "/../lib/mylib/doText.php";
require_once dirname(__FILE__) . "/../lib/mylib/doDefault.php";
require_once dirname(__FILE__) . "/../lib/mylib/doSubscribe.php";
require_once dirname(__FILE__) . "/../lib/mylib/doClickBuy.php";
require_once dirname(__FILE__) . "/../lib/mylib/doClickReg.php";
require_once dirname(__FILE__) . "/../lib/mylib/doClickManag.php";

const AgentId = 1000004; //要接收消息的AgentId

$xml = receiveMsgFromQyWx(AgentId);

$code = judgeMsg($xml);//判断消息类型
switch ($code) {

  case msgTypeCode::$ScancodeWaitMsg://扫码事件
    doScancodeWaitMsg($xml);
    break;

  case msgTypeCode::$ClickReg://点击“编辑联系方式”按钮
    doClickReg($xml);
    break;

  case msgTypeCode::$Text://消息事件
    doText($xml);
    break;

  case msgTypeCode::$Subscribe://订阅事件
    doSubscribe($xml);
    break;

  case msgTypeCode::$ClickBuy://点击“买书”按钮事件
    doClickBuy($xml);
    break;


  case msgTypeCode::$ClickManag://点击“管理我的售书信息”按钮
    doClickManag($xml);
    break;

  default:
    doDefault($xml);//未知事件
    break;

}



// $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
// $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
// $eventkey = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
// replyMsgToUser($eventkey, $FromUserName, $ToUserName);
