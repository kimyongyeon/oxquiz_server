<?
    require "dbcon.php"; // db����.

    // ��ŷ ���̺��� 1~10�� �����͸� �о �Ѹ�.
    $rank_table = "TB_OX_RANK"; // id, name, phone, use_yn, reg_date

    // id�� ������ �ƴҰ��
    if ($c_secury == "y2k")
    {
        $result = $dbc->getRecordList($rank_table, "*", "", "jumsu desc LIMIT 0 , 10","","");

		 // ���̵� ������
		 foreach($result as $key => $val){
				print $val['id'].",";
		 }
		 // ����������
		 foreach($result as $key => $val){
				print $val['jumsu'].",";
		 }
    }
    
?>