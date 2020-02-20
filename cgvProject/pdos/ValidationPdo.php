<?php

/*DB에 유저 유뮤 확인 SELECT*/
function isUser($userId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM users WHERE userId= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

