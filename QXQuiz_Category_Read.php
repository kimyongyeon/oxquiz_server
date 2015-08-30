<?	
	require "dbcon.php"; // db연결.

    $q_table = "TB_OX_Q"; 
	
//	echo "6:".$c_category;

    if ($c_category != "")
    {
		switch($c_category){
			case 0:
				$c_category = "시사";
				break;
			case 1:
				$c_category = "연예인";
				break;
			case 2:
				$c_category = "과학";
				break;
			case 3:
				$c_category = "예술";
				break;
			case 4:
				$c_category = "경제";
				break;
			case 5:
				$c_category = "수학";
				break;
		}

//		$c_category = "시사";

		$where = " category = '".$c_category."' and quseyn='Y'";
		$orderby = "question desc LIMIT 0 , 31";
        $result = $dbc->getRecordList($q_table, "question, useyn",  $where, $orderby, "", "");
		$R1 = 1;
		// 문제 xml로 변환
		$xml = "<?xml version='1.0' encoding='utf-8'?>";
    	$xml .="<category>";		
		foreach($result as $key => $val){
			$xml .= "<R".$R1.">";
			$xml .= "<K>".$val['question']."</K>";
			$xml .= "<S>".$val['useyn']."</S>";
			$xml .= "</R".$R1.">";
			$R1++;
		}
    	$xml .="</category>";
		echo $xml;
    }
?>