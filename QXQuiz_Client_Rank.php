<?
    require "dbcon.php"; // db연결.

    // 개인기록 테이블
    $perHistory_table = "TB_OX_MEM_HISTORY";

    // id, point check
    if ($c_id != "" && $c_point != "")
    {
        $today = date("Y-m-d H:i:s",time()); // 오늘날짜

        $arrPerHistory = array(     "id"        => $c_id,
                                "jumsu"      => $c_point,
                                "reg_date"  => $today
        );
        $rtn = $dbc->insertQuery($perHistory_table, $arrPerHistory, array("senddate"),$error=false);

		if(!$rtn){
			echo "false";
		}else{
	        echo "true \n";  
		}
    }else{
        echo "false \n"; 
    }

    // 랭크 테이블
    // id:사용자아이디, jumsu:사용자가 얻은 포인트, up_date:사용자갱신날짜, reg_date:등록일자, use_yn:사용유무
    $rank_table = "TB_OX_RANK";  // id, jumsu, up_date, reg_date, use_yn

    if ($c_point != "" && $c_point)
    {
		
        $today = date("Y-m-d"); // 오늘날짜

        $arrRank = array(     "id"        => $c_id,
                              "jumsu"     => $c_point,
                              "up_date"   => $today,
                              "reg_date"  => $today,
                              "use_yn"    => 1
        );
        $rtn = $dbc->insertQuery($rank_table, $arrRank, array("senddate"),$error=false);
		
		// insert가 안되는 경우는 update로 기록을 갱신해야 함.
		if (!$rtn) {
			// 업데이트 처리 로직 추가 해야 함.
			// 기존의 있을때는 현재날짜만 업데이트 한다.
			$today = date("Y-m-d"); // 오늘날짜 
			$arrRank = array( "jumsu"     => $c_point, 
				              "up_date"   => "CURRENT_DATE()");
			$where = "id='".$c_id."'"; 
			$dbc->updateQuery ($rank_table, $arrRank, $where, $error=true);
		}
		echo "true \n";  // 점수 등록 완료
    }else{
        echo "false \n";  // 점수 등록 실패
    }
?>