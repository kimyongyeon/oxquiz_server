<?php

require "dbclass.php";

// ���� ���� �ʱ�ȭ
unset($conf);


// DB ����
$conf["dbi"]["host"] = "localhost";	// ȣ��Ʈ
$conf["dbi"]["user"] = "kyy82";			    // �����
$conf["dbi"]["pass"] = "msp430f5435";		// �н�����
$conf["dbi"]["name"] = "kyy82";			    // ����
$dbc = new MySql($conf["dbi"]);
?>