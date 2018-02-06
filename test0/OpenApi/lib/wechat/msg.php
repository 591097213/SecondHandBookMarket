<?php

/**
 *
 * 收发消息相关函数
 */

require_once "helper.php";
require_once "msgcrypt.php";
require_once "app_api.php";
require_once "globalCode.php";

/**
 * 接收消息
 * @param int 要接收消息的应用的agentId
 * @return array 返回一个数组，array(int,DOMDocument),当int=0时DOMDocument有效
 */

function receiveMsgFromQyWx($agentId)
{
    //读取config文件里面的配置
    $appConfigs = loadConfig();
    $config = getConfigByAgentId($agentId);

    $token = $config->Token;
    $encodingAesKey = $config->EncodingAESKey;
    $corpId = $appConfigs->CorpId;

    $sReqMsgSig = $_GET["msg_signature"];
    $sReqTimeStamp = $_GET["timestamp"];
    $sReqNonce = $_GET["nonce"];
    $sReqData = file_get_contents("php://input");

    $sMsg = ""; // 解析之后的明文,此处作为指针供DecryptMsg写入
    $wxcpt = new MsgCrypt($token, $encodingAesKey, $corpId);
    $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);

    if ($errCode == 0) {
        // 解密成功，sMsg即为xml格式的明文

        $arr = xmlToArray($sMsg);

        return array(0, $arr);
        //return $sMsg，然后输出查看msg的内容的话标签中的内容不会输出，
        //只会输出没有标签包裹的CreatTime中的内容

    } else {
        return array($errCode, null);
    }
}

//text msg example:
// <xml>
// <ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[fromUser]]></FromUserName>
// <CreateTime>1348831860</CreateTime>
// <MsgType><![CDATA[text]]></MsgType>
// <Content><![CDATA[this is a test]]></Content>
// <MsgId>1234567890123456</MsgId>可用于消息重排，判断是否为重复收到的消息
// <AgentID>1</AgentID>
// </xml>

// scancode_waitmsg example：
// <xml><ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[FromUser]]></FromUserName>
// <CreateTime>1408090606</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[scancode_waitmsg]]></Event>
// <EventKey><![CDATA[6]]></EventKey>菜单的键值
// <ScanCodeInfo>
//    <ScanType><![CDATA[qrcode]]></ScanType>一般为qrcode
//    <ScanResult><![CDATA[2]]></ScanResult>isbn
// </ScanCodeInfo>
// <AgentID>1</AgentID>
// </xml>
//可用FromUserName+CreateTime重排判重

/**
 *
 *  判断消息是扫描时间还是文本消息
 * @param DOMDocument 为接收到的消息
 * @return int 返回判断后的结果
 */
function judgeMsg($msg)
{
    $jMsg = $msg["MsgType"];
    if ($jMsg == "text") {
        return GlobalCode::$Text;
    } elseif ($jMsg == "event") {
        if ($msg["Event"] == "scancode_waitmsg") {
            return GlobalCode::$ScancodeWaitMsg;
        } else {
            return GlobalCode::$UnknowType;
        }
    } else {
        return GlobalCode::$UnknowType;
    }

}
