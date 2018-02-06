<?php

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
//可用FromUserName+CreateTime重排判断是否重复接收消息

/**
 * ScancodeWaitMsg事件消息
 * 根据isbn从豆瓣图书API调取数据并存入数据库，
 * 查询用户是否登记联系方式
 * 返回给用户图书录入信息（如未登记联系方式则提示登记）
 * @param DOMDocument 收到的消息的xml形式
 */

function doScancodeWaitMsg($msg)
{
    $temp = $msg["ScanCodeInfo"]["ScanResult"];
    echo $msg;
}
