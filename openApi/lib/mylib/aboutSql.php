<?php
require_once dirname(__FILE__) . "/../wechat/other/helper.php";
require_once dirname(__FILE__) . "/../sql/MysqliDb.php";


/*******************
 * 插入数据相关函数 *
 *******************/

/**
 * 初始化mysql连接
 * @return MysqliDb
 */
function initMySql()
{
    $conf = loadMySql();//load config
    $db = new MysqliDb($conf["host"], $conf['username'], $conf['password'], $conf['databaseName']);
    return $db;
}

/**
 * 插入book表,采用ON DUPLICATE KEY UPDATE的方式
 * 以确保用户重新添加已登记过的书籍时能够以豆瓣数据为准跟新数据库中的书籍数据
 * @param MysqliDb 初始化后的数据库
 * @param array 书籍信息
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertBook(&$db, &$bookInfo)
{
    $authors = implode(" ", $bookInfo["author"]);
    $translators = implode(" ", $bookInfo["translator"]);

    $temp = array(
        $authors,
        $translators
    );

    $keyWords = implode(" ", $temp);

    $tags = $bookInfo["tags"];
    foreach ($tags as $tag) {
        $keyWords .= " " . $tag["title"];
    }

    $inser = array(//设置插入字段
        "bookId" => $bookInfo["id"],
        "ratingMax" => $bookInfo["rating"]["max"],
        "ratingNumRaters" => $bookInfo["rating"]["numRaters"],
        "ratingAverage" => $bookInfo["rating"]["average"],
        "ratingMin" => $bookInfo["rating"]["min"],
        "subtitle" => $bookInfo["subtitle"],
        "pubdate" => $bookInfo["pubdate"],
        "originTitle" => $bookInfo["origin_title"],
        "pages" => $bookInfo["pages"],
        "Isbn10" => $bookInfo["isbn10"],
        "Isbn13" => $bookInfo["isbn13"],
        "title" => $bookInfo["title"],
        "altTitle" => $bookInfo["alt_title"],
        "price" => $bookInfo["price"],
        "publisher" => $bookInfo["publisher"],
        "keyWords" => $keyWords
    );

    $updateColumns = array(
        "ratingMax",
        "ratingNumRaters",
        "ratingAverage",
        "ratingMin",
        "subtitle",
        "pubdate",
        "originTitle",
        "pages",
        "Isbn10",
        "Isbn13",
        "title",
        "altTitle",
        "price",
        "publisher",
        "keyWords"
    );
    $db->onDuplicate($updateColumns);//设置重复插入时更新的字段
    $result = $db->insert("book", $inser);//插入
    return $result;
}

/**
 * 插入bookAuthor表
 * @param MysqliDb 初始化后的数据库
 * @param array 书籍信息
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertBookAuthor(&$db, &$bookInfo)
{
    $author = $bookInfo["author"];
    $inser = array();
    foreach ($author as $au) {
        $temp = array(
            "bookId" => $bookInfo["id"],
            "author" => $au,
        );
        array_push($inser, $temp);
    }
    $result = $db->insertMulti("bookAuthor", $inser);
    return $result;
}

/**
 * 插入bookTranslator表
 * @param MysqliDb 初始化后的数据库
 * @param array 书籍信息
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertbookTranslator(&$db, &$bookInfo)
{
    $translator = $bookInfo["translator"];
    $inser = array();
    foreach ($translator as $tr) {
        $temp = array(
            "bookId" => $bookInfo["id"],
            "translator" => $tr,
        );
        array_push($inser, $temp);
    }
    $result = $db->insertMulti("bookTranslator", $inser);
    return $result;
}

/**
 * 插入user表,不包含联系方式
 * @param DOMDocument
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertUser(&$db, &$xml)
{
    $inser = array(
        "openId" => $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue,
    );
    $result = $db->insert("user", $inser);
    return $result;
}

/**
 * 插入bookUser表
 * 注意插入后还需用userBookCountAddone()将user表对应用户的bookCount+1
 * @param MysqliDb
 * @param array
 * @param DOMDocument
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertBookUser(&$db, &$bookInfo, &$xml)
{
    $openId = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
    $db->where("bookId", $bookInfo["id"]);
    $db->where("openId", $openId);
    $temp = $db->getOne("bookUser");//查询是否已有数据

    if (empty($temp) == true) {
        //插入数据
        $inser = array(
            "bookId" => $bookInfo["id"],
            "openId" => $openId,
            "lastRegTime" => $db->now()
        );
        $result = $db->insert("bookUser", $inser);
        return $result;
    } else {
        //更新lastRegTime和isSell
        $data = array(
            "lastRegTime" => $db->now(),
            "isSell" => 0
        );
        $db->where("bookId", $bookInfo["id"]);
        $db->where("openId", $openId);
        $result = $db->update("bookUser", $data);
        return $result;
    }
}


/**
 * 用户所登记的书籍数目+1
 * @param MysqliDb
 * @param DOMDocument
 * @return int 0表示插入错误，>0表示正常插入
 */
function userBookCountAddone(&$db, &$xml)
{
    $data = array(
        "bookCount" => $db->inc(1)//表示自加一
    );
    $db->where("openId", $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue);
    $result = $db->update("user", $data);
    return $result;
}

/**
 * 插入Tag表
 * @param MysqliDb
 * @param array
 * @return int
 * 不能采用insertMulti的方式，因为用该方式一旦一个tag有重复，插入不成功
 * 则一整次插入都无效
 */
function insertTag(&$db, &$bookInfo)
{
    foreach ($bookInfo["tags"] as $tags) {
        $temp = array(
            "name" => $tags["name"],
            "title" => $tags["title"]
        );
        $result = $db->insert("tag", $temp);
    }
    return $result;
}

/**
 * 插入bookTag表
 * @param MysqliDb
 * @param array
 * @return int
 */
function insertBookTag(&$db, &$bookInfo)
{
    $inser = array();
    foreach ($bookInfo["tags"] as $tags) {
        $ta = $db->where("name", $tags["name"])->getOne("tag");
        $temp = array(
            "bookId" => $bookInfo["id"],
            "count" => $tags["count"],
            "tagId" => $ta["tagId"]
        );
        array_push($inser, $temp);
    }
    $result = $db->insertMulti("bookTag", $inser);
    return $result;
}


/**
 * 将书籍bookInfo与用户信息添加到数据库
 * @param MysqliDb
 * @param array
 * @param DOMDocument
 */

function insertDB(&$db, &$bookInfo, &$xml)
{
    //用户信息应该已经在订阅公众号的时候插入表中了
    //保险起见再查询一次，若无该用户则插入
    $temp = $db->where("openId", $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue)->get("bookUser");
    if (empty($temp)) {
        insertUser($db, $xml);
    }

    insertBook($db, $bookInfo);
    insertBookAuthor($db, $bookInfo);
    insertbookTranslator($db, $bookInfo);

    //查询是否已经登记过。
    $temp = $db->where('bookId', $bookInfo['id'])->where("openId", $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue)->get("bookUser");
    //否则书籍加1并插入表中
    if (empty($temp) === true) {
        userBookCountAddone($db, $xml);//user表的书籍count+1
    }
    //登记过则刷新生存时间
    insertBookUser($db, $bookInfo, $xml);
    insertTag($db, $bookInfo);
    insertBookTag($db, $bookInfo);
}

/*******************
 *
 *******************/