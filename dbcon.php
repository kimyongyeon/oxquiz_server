<?php

require "dbclass.php";

// 설정 변수 초기화
unset($conf);


// DB 설정
$conf["dbi"]["host"] = "localhost";	// 호스트
$conf["dbi"]["user"] = "kyy82";			    // 사용자
$conf["dbi"]["pass"] = "msp430f5435";		// 패스워드
$conf["dbi"]["name"] = "kyy82";			    // 디비명
$dbc = new MySql($conf["dbi"]);
?>