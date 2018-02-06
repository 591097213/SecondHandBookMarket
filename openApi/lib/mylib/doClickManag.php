<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";

/**
 * 当用户点击“管理我的在售书籍”按钮时查询该用户在售书籍
 * @param DOMDocument
 * 
 */
function doClickManag(&$xml)
{
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;


    $db = initMySql();
    $set = 'call allocation("%s")';
    $set = sprintf($set, $FromUserName);
    $db->rawQuery($set);//为用户在售书籍分配序号

    $db->where("openId", $FromUserName);
    $db->where("isSell", 0);
    $db->orderBy("number", "asc");
    $datas = $db->get("bookUser");

    if (empty($datas) == true) {
        //先查询用户是否有在售书籍然后再决定是否更改用户状态、
        $prompt = getPrompt("managFalse");
        replyMsgToUser($prompt, $FromUserName, $ToUserName);
    } else {

        $set = 'call stateTo2("%s")';
        $set = sprintf($set, $FromUserName);
        $db->rawQuery($set);//更改用户状态


        $da = $datas[0];
        $format = getBook($da, $db);
        for ($i = 1; $i < count($datas); $i++) {
            $format .= "\n\n" . getBook($datas[$i], $db);
        }

        $prompt = getPrompt("manag");
        $prompt = sprintf($prompt, $format);

        replyMsgToUser($prompt, $FromUserName, $ToUserName);
    }

}


/**
 * 根据书籍ID查找书籍信息
 * @param array
 * @param db
 * @return string
 * 
 */
function getBook($da, &$db)
{
    $str = $da["number"] . "." . "\n";//序号
    $book = $db->where("bookId", $da["bookId"])->getOne("book");

    $authors = $db->where("bookId", $da["bookId"])->getValue("bookAuthor", "author", null);


    $title = $book["title"];
    if ($book["subtitle"] != null) {
        $title .= "——" . $book["subtitle"];
    }
    $publisher = $book["publisher"];

    $author = $authors[0];
    for ($i = 1; $i < count($authors); $i++) {
        $author .= "，" . $authors[$i];
    }

    $prompt = $str . getPrompt("book");
    $prompt = sprintf($prompt, $title, $author, $publisher);

    return $prompt;


}
