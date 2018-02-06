<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";
require_once dirname(__FILE__) . "/aboutSql.php";

/**
 * 用户订阅公众号时的欢迎语以及提示
 * 并且将用户插入用户表中
 * @param DOMDocument
 * 
 */
function doSubscribe(&$xml)
{
    $prompt = getPrompt("subscribe");
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;

    $db = initMySql();
    insertUser($db, $xml);

    //将用户状态置0
    $set = 'call stateTo0("%s")';
    $set = sprintf($set, $FromUserName);
    $db->rawQuery($set);

    replyMsgToUser($prompt, $FromUserName, $ToUserName);
}