<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";

/**
 * 当用户点击“买书”按钮时给用户的提示
 * @param DOMDocument
 * 
 */
function doClickBuy(&$xml)
{
    $prompt = getPrompt("clickBuy");
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;


    $db = initMySql();
    //用户状态置0，表示处于查询态
    $set = 'call stateTo0("%s")';
    $set = sprintf($set, $FromUserName);
    $db->rawQuery($set);

    replyMsgToUser($prompt, $FromUserName, $ToUserName);
}