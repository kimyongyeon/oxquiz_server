<?
    require "dbcon.php"; // db연결.

    // 개인기록 테이블
    $q_table = "TB_OX_Q";

    // id, point check
    if ($c_q != "" && $c_category != "" && $c_use)
    {
        $today = date("Y-m-d"); // 오늘날짜

        $arrq = array(     "question"        => $c_q,
                                "useyn"       => $c_use,
								"category"  => $c_category,
                                "reg_date"  => $today);
        $rtn = $dbc->insertQuery($q_table, $arrq, array("senddate"),$error=true);

		if(!$rtn){
			echo "false";
		}else{
	        echo "true";  
		}
    }else{
        echo "false"; 
    }
?>