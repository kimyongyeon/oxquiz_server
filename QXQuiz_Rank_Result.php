<?
    require "dbcon.php"; // db연결.

    // 랭킹 테이블에서 1~10위 데이터를 읽어서 뿌림.
    $rank_table = "TB_OX_RANK"; // id, name, phone, use_yn, reg_date

    // id가 공백이 아닐경우
    if ($c_secury == "y2k")
    {
        $result = $dbc->getRecordList($rank_table, "*", "", "jumsu desc LIMIT 0 , 10","","");

		 // 아이디 보내기
		 foreach($result as $key => $val){
				print $val['id'].",";
		 }
		 // 점수보내기
		 foreach($result as $key => $val){
				print $val['jumsu'].",";
		 }
    }
    
?>