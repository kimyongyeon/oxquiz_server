<?	
	require "dbcon.php"; // db연결.

    $q_table = "TB_OX_NOTICE"; 
	
	$where = " useyn = 'Y'";
	$result = $dbc->getRecordList($q_table, "*",  $where, null, "", "");
	$R1 = 1;
	
	header("Cache-Control: no-cache, must-revalidate");
	header("Content-type: text/xml; charset=utf-8");

	// 문제 xml로 변환
	$xml = "<?xml version='1.0' encoding='utf-8'?>";
	$xml .="<notice>";		
	foreach($result as $key => $val){
		$xml .= "<R".$R1.">";
		$xml .= "<C>".$val['content']."</C>";
		$xml .= "</R".$R1.">";
		$R1++;
	}
	$xml .="</notice>";
	echo $xml;
?>