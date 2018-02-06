<!--///////////////////////////////////////////////////////////////-->
<!--一. sqlsrv连接数据库-->

<?php

$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn) { //$con!=0,connect successfully
    echo "Connection established.<br/><br/>";
    $server_info = sqlsrv_server_info($conn);
    if ($server_info) { //获取server_info成功，server_info从key到value
        foreach ($server_info as $key => $value) {
            echo $key . ":" . $value . "<br/>";
        }
    } else {
        die(print_r(sqlsrv_errors(), true)); //获取server_info失败，退出
    }
    echo "<br/>";
    //
    $client_info = sqlsrv_client_info($conn);
    if ($client_info) {
        foreach ($client_info as $key => $value) {
            echo $key . ":" . $value . "<br/>";
        }
    } else {
        echo "Error in retrieving client info.<br/>";
    }
} else {
    echo "Connection could not be established.\n";
    die(print_r(sqlsrv_errors(), true));
}
/* Close the connection. */
sqlsrv_close($conn); //关闭连接
?>





<!--///////////////////////////////////////////////////////////////-->
<!--二. 获取查询结果-->





<?php
$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$sql = "select top 100 * from cti_agent;";
$stmt = sqlsrv_query($conn, $sql, null);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    print_r($row);
    print_r($row["agtid"] . ", " . $row["account"] . ", " . $row["agtname"] . ", " . $row["telnum"] . ", " . $row["pwd"] . ", " . $row["isMaster"] . "<br/>");
}
?>



<!--///////////////////////////////////////////////////////////////-->
<!--三.调用存储过程(无参)-->




<?php
$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$sp = "{call web_agent_list}";
$stmt = sqlsrv_query($conn, $sp, null);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    print_r($row);
    print_r($row["agtid"] . ", " . $row["account"] . ", " . $row["agtname"] . ", " . $row["telnum"] . ", " . $row["pwd"] . ", " . $row["isMaster"] . "<br/>");
}
?>



<!--///////////////////////////////////////////////////////////////-->

<!--四.调用存储过程(入参)-->





<?php
$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$tid = 1;
$params = array(
    array($tid, SQLSRV_PARAM_IN),
);
$sp = "{call web_huifang_export1(?)}";
$stmt = sqlsrv_query($conn, $sp, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    print_r($row);
    print_r($row["agtid"] . ", " . $row["account"] . ", " . $row["agtname"] . ", " . $row["telnum"] . ", " . $row["pwd"] . ", " . $row["isMaster"] . "<br/>");
}
?>



<!--///////////////////////////////////////////////////////////////-->
<!--五.调用存储过程(出参)-->




<?php
$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
//prepare params
$tid = 1;
$ttid = 3;
$kind = 0; //输出参数
$params = array(
    array($tid, SQLSRV_PARAM_IN),
    array($ttid, SQLSRV_PARAM_IN),
    array($kind, SQLSRV_PARAM_OUT),
);
//stored procedure
$sp = "{call web_tasktel_info(?,?,?)}";
$stmt = sqlsrv_query($conn, $sp, $params);
if (!stmt) {
    die(print_r(sqlsrv_errors(), true));
}
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $result = $row;
}
echo "结果集：";
print_r($result);
echo "<br/><br/>";
sqlsrv_next_result($stmt);
echo "输出参数：";
print_r($kind);

/* Release resources */
sqlsrv_free_stmt($stmt);
/* Close the connection. */
sqlsrv_close($conn);
?>
六.调用存储过程(多结果集)


[php] view plain copy


<?php
$serverName = "ip, port";
$connectionInfo = array("UID" => "username", "PWD" => "password", "Database" => "db", "CharacterSet" => "utf-8");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
//stored procedure
$sp = "{call xp_results_test()}";
$stmt = sqlsrv_query($conn, $sp, null);
if (!stmt) {
    die(print_r(sqlsrv_errors(), true));
}
//遍历结果集
echo "第一个结果集：<br/>";
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo $row["account"] . "<br/>";
}
echo "<br/>第二个结果集：<br/>";
sqlsrv_next_result($stmt);
while ($row1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo $row1["account"] . "<br/>";
}
echo "<br/>第三个结果集：<br/>";
sqlsrv_next_result($stmt);
while ($row1 = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo $row1["account"] . "<br/>";
}
/* Release resources */
sqlsrv_free_stmt($stmt);
/* Close the connection. */
sqlsrv_close($conn);
?>