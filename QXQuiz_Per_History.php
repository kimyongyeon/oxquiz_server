<?
    require "dbcon.php"; // db연결.

    // 랭킹 테이블에서 1~10위 데이터를 읽어서 뿌림.
    $rank_table = "TB_OX_MEM_HISTORY"; // id, name, phone, use_yn, reg_date

    // id가 공백이 아닐경우
    if ($c_id != "")
    {
		$colum = "distinct jumsu, reg_date";
		$where = "id = '".$c_id."'";
		$orderby = "jumsu desc LIMIT 0 , 10";
        $result = $dbc->getRecordList($rank_table, $colum, $where, $orderby,"","");

		 // 점수, 날짜를 전송한다. 최종 구분자 |
		 foreach($result as $key => $val){
				print $val['jumsu'].",".$val['reg_date'].",";
		 }
    }
    
?>