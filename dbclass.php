<?php

// #############################################################################

class MySQL
{
	/**
	 * 에러시 메세지 출력여부
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $error = false;

	/**
	 * SQL 쿼리 디버깅 여부
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $debug = false;

	/**
	 * SQL 쿼리 로그 기록 여부
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $query_log = false;

	/**
	 * 기록된 SQL 쿼리 로그
	 *
	 * @access	public
	 * @var		string
	 */
	var $log = NULL;

	/**
	 * SQL 쿼리 및 처리 로그 기록 파일
	 *
	 * @access	public
	 * @var		string
	 */
	var $logfile = NULL;

	/**
	 * 최종 SQL 쿼리
	 *
	 * @access	private
	 * @var		string
	 */
	var $lastQuery = NULL;

	/**
	* DB 지속 연결(persistent) 여부
	*
	* @access	private
	* @var		boolean
	*/
	var $persistent = false;

	/**
	 * DB 연결 정보 연관 배열
	 *
	 * @access	private
	 * @var		array
	 */
	var $dbi = array();

	/**
 	 * DB 객체 생성자
	 *
	 * @access	public
	 * @param	array		$dbi	Database 연결정보 연관배열(host, port, user, pass, name)
 	 */
	function MySQL ($dbi)
	{
		if (!is_array($dbi)) return;
		$dbi['handle'] = NULL;
		$dbi['host'] = $dbi['port'] ? $dbi['host'] . ":" . $dbi['port'] : $dbi['host'];
		$this->dbi = $dbi;
	}

	/**
 	 * 조건에 일치하는 레코드 수
	 *
	 * @access	public
	 * @param	string		$table	테이블명
	 * @param	string		$where	WHERE 조건
	 * @return	integer		레코드 수
	 * @see					query(), numRows(), result(), freeResult()
 	 */
	function getCount ($table, $where=NULL)
	{
		$where = $where != NULL ? " WHERE $where" : NULL;
		$qid = $this->query("SELECT COUNT(*) FROM $table$where");
		$row = $this->numRows($qid);
		if ($row) $count = $this->result($qid, 0, 0);
		else $count = 0;
		intval($count);
		$this->freeResult($qid);
		return $count;
	}


	/**
 	 * 조건에 일치하는 레코드
	 *
	 * @access	public
	 * @param	string		$table	테이블명
	 * @param	string		$field	추출 필드
	 * @param	string		$where	WHERE 조건
	 * @return	array		레코드 정보
	 * @see					getRecordList()
	 * @see					query(), numRows(), fetchArray(), freeResult()
 	 */
	function getRecord ($table, $field="*", $where=NULL)
	{
		$where = $where != NULL ? " WHERE $where" : NULL;
		$qid = $this->query("SELECT $field FROM $table$where");
		if (!($rec = $this->fetchArray($qid))) { $rec = array(); }
		$this->freeResult($qid);
		return $rec;
	}

	/**
 	 * 조건에 일치하는 레코드 목록
	 *
	 * @access	public
	 * @param	string		$table	테이블명
	 * @param	string		$field	추출 필드
	 * @param	string		$where	WHERE 조건
	 * @param	string		$sort	정렬 조건
	 * @param	integer		$offset	추출 시작 위치(LIMIT)
	 * @param	integer		$count	추출 수량
	 * @return	array		레코드 정보 목록
	 * @see					getRecord()
	 * @see					query(), numRows(), fetchArray(), freeResult()
 	 */
	function getRecordList ($table, $field="*", $where=NULL,
									$sort=NULL, $offset=0, $count=0)
	{
		$where = $where != NULL ? " WHERE $where" : NULL;
		if ($count != 0) $limit = " LIMIT $offset, $count";
		$sort = $sort != NULL ? " ORDER BY $sort" : NULL;
		$qid = $this->query("SELECT $field FROM $table$where$sort$limit");
		$list = array();
		while ($rec = $this->fetchArray($qid)) {$list[] = $rec;}
		$this->freeResult($qid);
		return $list;
	}

	/**
 	 * 레코드 기록
	 *
	 * @access	public
	 * @param	string		$table			테이블명
	 * @param	array		$hash			기록용 필드 및 값의 연관배열
	 * @param	array		$withoutQuote	자동 인용부호(Quote, ') 삽입 및
	 *										addslashes 배제 컬럼 배열(string 가능)
	 * @param	boolean		$error			에러 검출 여부
	 * @return	string		SQL 쿼리 Resource
	 * @see					updateQuery(), deleteQuery()
	 * @see					query()
 	 */
	function insertQuery ($table, $hash,
									$withoutQuote=array(), $error=true)
	{
		if ($table == NULL || !is_array($hash)) return false;
		if (!is_array($withoutQuote) && $withoutQuote != NULL)
			$withoutQuote = array($withoutQuote);
		$fields = $values = array();
		foreach ($hash as $field => $value)
		{
			if (is_array($withoutQuote) && in_array($field, $withoutQuote))
				$value_q = $value;
			else	
				$value_q = "'" . $this->addslashes($value) . "'";
			$fields[] = $field;
			$values[] = $value_q;
		}
		$fields = implode(", ", $fields);
		$values = implode(", ", $values);
		return $this->query("INSERT INTO $table ($fields) VALUES ($values)", $error);
	}

	/**
 	 * 레코드 수정
	 *
	 * @access	public
	 * @param	string		$table			테이블명
	 * @param	array		$hash			수정용 필드 및 값의 연관배열
	 * @param	string		$where			WHERE 조건
	 * @param	array		$withoutQuote	자동 인용부호(Quote, ') 삽입 및
	 *										addslashes 배제 컬럼 배열(string 가능)
	 * @param	boolean		$error			에러 검출 여부
	 * @return	string		SQL 쿼리 Resource
	 * @see					insertQuery(), deleteQuery()
	 * @see					query()
 	 */
	function updateQuery ($table, $hash, $where=NULL,
									$withoutQuote=array(), $error=true)
	{
		if ($table == NULL || !is_array($hash)) return false;
		$where = $where != NULL ? " WHERE $where" : NULL;
		if (!is_array($withoutQuote) && $withoutQuote != NULL)
			$withoutQuote = array($withoutQuote);
		$fields_values = array();
		foreach ($hash as $field => $value)
		{
			if (is_array($withoutQuote) && in_array($field, $withoutQuote))
				$value_q = $value;
			else	
				$value_q = "'" . $this->addslashes($value) . "'";
			$fields_values[] = "$field = $value_q";
		}
		$fields_values = implode(", ", $fields_values);
		return $this->query("UPDATE $table SET $fields_values$where", $error);
	}

	/**
 	 * 레코드 삭제
	 *
	 * @access	public
	 * @param	string		$table	테이블명
	 * @param	string		$where	WHERE 조건
	 * @param	boolean		$error	에러 검출 여부
	 * @return	string		SQL 쿼리 Resource
	 * @see					insertQuery(), updateQuery()
	 * @see					query()
 	 */
	function deleteQuery ($table, $where=NULL, $error=true)
	{
		$where = $where != NULL ? " WHERE $where" : NULL;
		return $this->query("DELETE FROM $table$where", $error);
	}

	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/

	/**
 	 * mysql_insert_id()
	 *
	 * @access	public
	 */
	function insertId ()
	{
		return mysql_insert_id($this->dbi['handle']);
	}

	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/

	/**
 	 * DB 연결
	 *
	 * @access	private
 	 */
	
	function connect ()
	{
		$this->fileLog("connect()");
		if ($this->dbi['handle'] == NULL)
		{
			$connect_function = $this->persistent ? "mysql_pconnect" : "mysql_connect";
			$this->dbi['handle'] = $connect_function($this->dbi['host'],
														$this->dbi['user'],
														$this->dbi['pass']);
			$this->errorCheck($this->dbi['handle']);

         @mysql_query("set names euckr"); 
		}
	}

	/**
 	 * DB 연결 상태 확인 및 접속 처리
	 *
	 * @access	private
	 * @see					connect(), selectDb()
 	 */
	function connectCheck ()
	{
		$this->fileLog("connectCheck()");
		if ($this->dbi['handle'] == NULL)
		{
			$this->connect();
			$this->selectDb();
		}
	}

	/**
 	 * DB 연결 종료
	 *
	 * @access	private
	 * @return	boolean		종료 성공 여부
 	 */
	function close ()
	{
		$this->fileLog("close()");
		if ($this->dbi['handle'] != NULL)
		{
			return mysql_close($this->dbi['handle']);
		}
	}

	/**
 	 * Database 선택
	 *
	 * @access	private
	 * @return	boolean		선택 성공 여부
 	 */
	function selectDb ()
	{
		$this->fileLog("selectDb()");
		return mysql_select_db($this->dbi['name'], $this->dbi['handle']);
	}

	/**
 	 * 질의문(Query) 실행
	 *
	 * @access	private
	 * @param	string		$query	SQL 쿼리문
	 * @param	boolean		$error	에러 검출 여부
	 * @return	string		SQL 쿼리 Resource
	 * @see					connectCheck()
 	 */
	
	function query ($query, $error=true)
	{
		$this->connectCheck();
		$this->fileLog($this->dbi['host'] . " : " . $query);
		$this->lastQuery = $query;
		if ($this->debug) $this->debugExec($query . ";");
		if ($this->query_log) $this->log .= "$query;\n\n";
		$qid = mysql_query($query, $this->dbi['handle']);
		if ($error) $this->errorCheck($qid);

		return $qid;
	}

	/**
	 * mysql_num_rows()
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 */
	function numRows ($qid)
	{
		return mysql_num_rows($qid);
	}

	/**
 	 * mysql_result()
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 * @param	integer		$row	레코드 번호
	 * @param	integer		$col	필드 번호 또는 필드 명
	 */
	function result ($qid, $row, $col)
	{
		return mysql_result($qid, $row, $col);
	}

	/**
 	 * mysql_fetch_assoc()
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 * @since				PHP 4.0.3
	 */
	function fetchArray ($qid)
	{
		// return mysql_fetch_array($qid, MYSQL_ASSOC);
		return mysql_fetch_assoc($qid);
	}

	/**
 	 * mysql_fetch_row()
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 */
	function fetchRow ($qid)
	{
		return mysql_fetch_row($qid);
	}

	/**
 	 * mysql_affected_rows()
	 *
	 * @access	private
	 */
	function affectedRows ()
	{
		return mysql_affected_rows($this->dbi['handle']);
	}

	/**
 	 * 에러 메세지 출력
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 */
	function errorCheck ($qid)
	{
		if ( !$qid )
		{
			$title = "SQL ERROR";
			$msg = $this->errorNo() . " : " . $this->errorMsg();
			$query = $this->lastQuery;
			$string = "\n<BR /><BR /><H2>$title</H2>$msg<BR />\n" .
					"Query: $query;<BR />\n";
			$this->dieExec($string);
		}
	}

	/**
 	 * mysql_error()
	 *
	 * @access	private
	 */
	function errorMsg ()
	{
		return mysql_error($this->dbi['handle']);
	}

	/**
 	 * mysql_errno()
	 *
	 * @access	private
	 */
	function errorNo ()
	{
		return mysql_errno($this->dbi['handle']);
	}

	/**
 	 * 메세지 출력 및 비정상 종료(die) 처리
	 *
	 * @access	private
	 * @param	string		$string	출력할 메세지
	 * @see					connect(), errorCheck()
	 */
	function dieExec ($string)
	{
		die($string);
	}

	/**
 	 * mysql_free_result()
	 *
	 * @access	private
	 * @param	string		$qid	SQL 쿼리 Resource
	 */
	function freeResult ($qid)
	{
		return mysql_free_result($qid);
	}

	/**
 	 * 디버그 메세지 출력
	 *
	 * 디버그 모듈이 로드된 경우 팝업 윈도우에 메세지를 출력하고,
	 * 그렇지 않으면 현재창에 메세지 출력
	 *
	 * @access	private
	 * @param	string		$string	디버그 메세지
	 * @see					query()
	 */
	function debugExec ($string)
	{
		$dbg = &$GLOBALS['dbg'];
		if (is_object($dbg) && method_exists($dbg, "doDebug"))
			$dbg->doDebug($string);		// 디버그 모듈 실행
		else print "Query: $string<BR />\n";
	}

	/**
 	 * 처리 상태 로그 파일 기록
	 *
	 * @access	private
	 * @param	string		$string	Query 및 처리 상태 정보
	 */
	function fileLog ($string)
	{
		if ($this->logfile != NULL)
			error_log(date("[Y-m-d H:i:s] ") . $string . "\n", 3, $this->logfile);
	}

	/**
	 * 특수 문자 escape 처리
	 *
	 * @access	public
	 * @param	string		$string	escape 처리할 문자열
	 * @return	string		escape 처리된 문자열
	 */
	function addslashes ($string)
	{
		return addslashes($string);
	}
}

// #############################################################################

?>