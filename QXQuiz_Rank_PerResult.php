<?
    require "dbcon.php"; // db연결.

    // 랭킹 테이블에서 1~10위 데이터를 읽어서 뿌림.
    $rank_table = "TB_OX_RANK"; // id, name, phone, use_yn, reg_date

    // id가 공백이 아닐경우
    if ($c_perdata != "")
    {
		//print $c_perdata;
		$where = " id = '".$c_perdata."'";
        $result = $dbc->getRecordList($rank_table, "id, jumsu",  $where, "", "", "");
		 // 아이디 보내기
		foreach($result as $key => $val){
			print $val['id'].",";
		}
		// 점수보내기
		foreach($result as $key => $val){
			print $val['jumsu'].",";
		}
 		$orderby = "jumsu desc ";
        $result = $dbc->getRecordList($rank_table, "id, jumsu",  "", $orderby, "", "");
		$ranking=1;
		 // 등수
		 foreach($result as $key => $val){
				if($val['id'] != $c_perdata)
					$ranking++;
				else
					break;
		 }
		 print $ranking;

    }
    
?>