<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";
require_once dirname(__FILE__) . "/../sql/MysqliDb.php";
require_once dirname(__FILE__) . "/aboutSql.php";

/**
 * 得到array格式的书籍数据
 * @param string
 * @return array
 */
function getBookInfo($isbn)
{
    $doubanApi = json_decode(get_php_file(dirname(__FILE__) . "/../../apiconf/doubanApi.php"), true); //加载配置文件
    $url = appendString($doubanApi["doubanApi"], $isbn); //附加isbn
    $url = appendParamter($url, "fields", $doubanApi["paramList"]); //附加参数
    $info = http_get($url)["content"]; //获取的数据为一个数组，“content”为数组中json的内容
    return json_decode($info, true);
}

/**
 * 当接收到scancodeWaitMsg事件时
 * 1.调用豆瓣接口获取数据，判断书籍是否合法
 * 2.插入数据库
 * 3.判断用户是否登记联系方式
 * 4.回复用户书籍登记成功（如未登记联系方式则提示用户登记联系方式）
 * @param DOM
 */
function doScancodeWaitMsg(&$xml)
{
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
    $db = initMySql();

    //将用户状态设置为正常查询
    $set = 'call stateTo0("%s")';
    $set = sprintf($set, $FromUserName);
    $db->rawQuery($set);
    
    // 1.调用豆瓣接口获取数据
    $result = $xml->getElementsByTagName('ScanResult')->item(0)->nodeValue;
    $isbn = findISBN($result);
    $bookInfo = getBookInfo($isbn);
    //$bookInfo为书籍数据数组

    if (isset($bookInfo["id"])) {//如果书籍合法
        // 2.插入数据库
        insertDB($db, $bookInfo, $xml);
        //加载提示信息
        $prompt = getPrompt("bookReg");
        $prompt .= setBookInfo($bookInfo);//设置输出格式
        //3.判断用户是否登记联系方式
        if ($db->where("openId", $FromUserName)->getOne("user")["isReg"] == 0) {
            $prompt .= getPrompt("regPrompt");
        }

    } else {//如果书籍不合法
        $prompt = getPrompt("bookNotFound");
    }

    replyMsgToUser($prompt, $FromUserName, $ToUserName);
}
