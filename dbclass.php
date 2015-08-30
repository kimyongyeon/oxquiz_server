<?php

// #############################################################################

class MySQL
{
	/**
	 * ������ �޼��� ��¿���
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $error = false;

	/**
	 * SQL ���� ����� ����
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $debug = false;

	/**
	 * SQL ���� �α� ��� ����
	 *
	 * @access	public
	 * @var		boolean
	 */
	var $query_log = false;

	/**
	 * ��ϵ� SQL ���� �α�
	 *
	 * @access	public
	 * @var		string
	 */
	var $log = NULL;

	/**
	 * SQL ���� �� ó�� �α� ��� ����
	 *
	 * @access	public
	 * @var		string
	 */
	var $logfile = NULL;

	/**
	 * ���� SQL ����
	 *
	 * @access	private
	 * @var		string
	 */
	var $lastQuery = NULL;

	/**
	* DB ���� ����(persistent) ����
	*
	* @access	private
	* @var		boolean
	*/
	var $persistent = false;

	/**
	 * DB ���� ���� ���� �迭
	 *
	 * @access	private
	 * @var		array
	 */
	var $dbi = array();

	/**
 	 * DB ��ü ������
	 *
	 * @access	public
	 * @param	array		$dbi	Database �������� �����迭(host, port, user, pass, name)
 	 */
	function MySQL ($dbi)
	{
		if (!is_array($dbi)) return;
		$dbi['handle'] = NULL;
		$dbi['host'] = $dbi['port'] ? $dbi['host'] . ":" . $dbi['port'] : $dbi['host'];
		$this->dbi = $dbi;
	}

	/**
 	 * ���ǿ� ��ġ�ϴ� ���ڵ� ��
	 *
	 * @access	public
	 * @param	string		$table	���̺��
	 * @param	string		$where	WHERE ����
	 * @return	integer		���ڵ� ��
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
 	 * ���ǿ� ��ġ�ϴ� ���ڵ�
	 *
	 * @access	public
	 * @param	string		$table	���̺��
	 * @param	string		$field	���� �ʵ�
	 * @param	string		$where	WHERE ����
	 * @return	array		���ڵ� ����
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
 	 * ���ǿ� ��ġ�ϴ� ���ڵ� ���
	 *
	 * @access	public
	 * @param	string		$table	���̺��
	 * @param	string		$field	���� �ʵ�
	 * @param	string		$where	WHERE ����
	 * @param	string		$sort	���� ����
	 * @param	integer		$offset	���� ���� ��ġ(LIMIT)
	 * @param	integer		$count	���� ����
	 * @return	array		���ڵ� ���� ���
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
 	 * ���ڵ� ���
	 *
	 * @access	public
	 * @param	string		$table			���̺��
	 * @param	array		$hash			��Ͽ� �ʵ� �� ���� �����迭
	 * @param	array		$withoutQuote	�ڵ� �ο��ȣ(Quote, ') ���� ��
	 *										addslashes ���� �÷� �迭(string ����)
	 * @param	boolean		$error			���� ���� ����
	 * @return	string		SQL ���� Resource
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
 	 * ���ڵ� ����
	 *
	 * @access	public
	 * @param	string		$table			���̺��
	 * @param	array		$hash			������ �ʵ� �� ���� �����迭
	 * @param	string		$where			WHERE ����
	 * @param	array		$withoutQuote	�ڵ� �ο��ȣ(Quote, ') ���� ��
	 *										addslashes ���� �÷� �迭(string ����)
	 * @param	boolean		$error			���� ���� ����
	 * @return	string		SQL ���� Resource
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
 	 * ���ڵ� ����
	 *
	 * @access	public
	 * @param	string		$table	���̺��
	 * @param	string		$where	WHERE ����
	 * @param	boolean		$error	���� ���� ����
	 * @return	string		SQL ���� Resource
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
 	 * DB ����
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
 	 * DB ���� ���� Ȯ�� �� ���� ó��
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
 	 * DB ���� ����
	 *
	 * @access	private
	 * @return	boolean		���� ���� ����
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
 	 * Database ����
	 *
	 * @access	private
	 * @return	boolean		���� ���� ����
 	 */
	function selectDb ()
	{
		$this->fileLog("selectDb()");
		return mysql_select_db($this->dbi['name'], $this->dbi['handle']);
	}

	/**
 	 * ���ǹ�(Query) ����
	 *
	 * @access	private
	 * @param	string		$query	SQL ������
	 * @param	boolean		$error	���� ���� ����
	 * @return	string		SQL ���� Resource
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
	 * @param	string		$qid	SQL ���� Resource
	 */
	function numRows ($qid)
	{
		return mysql_num_rows($qid);
	}

	/**
 	 * mysql_result()
	 *
	 * @access	private
	 * @param	string		$qid	SQL ���� Resource
	 * @param	integer		$row	���ڵ� ��ȣ
	 * @param	integer		$col	�ʵ� ��ȣ �Ǵ� �ʵ� ��
	 */
	function result ($qid, $row, $col)
	{
		return mysql_result($qid, $row, $col);
	}

	/**
 	 * mysql_fetch_assoc()
	 *
	 * @access	private
	 * @param	string		$qid	SQL ���� Resource
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
	 * @param	string		$qid	SQL ���� Resource
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
 	 * ���� �޼��� ���
	 *
	 * @access	private
	 * @param	string		$qid	SQL ���� Resource
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
 	 * �޼��� ��� �� ������ ����(die) ó��
	 *
	 * @access	private
	 * @param	string		$string	����� �޼���
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
	 * @param	string		$qid	SQL ���� Resource
	 */
	function freeResult ($qid)
	{
		return mysql_free_result($qid);
	}

	/**
 	 * ����� �޼��� ���
	 *
	 * ����� ����� �ε�� ��� �˾� �����쿡 �޼����� ����ϰ�,
	 * �׷��� ������ ����â�� �޼��� ���
	 *
	 * @access	private
	 * @param	string		$string	����� �޼���
	 * @see					query()
	 */
	function debugExec ($string)
	{
		$dbg = &$GLOBALS['dbg'];
		if (is_object($dbg) && method_exists($dbg, "doDebug"))
			$dbg->doDebug($string);		// ����� ��� ����
		else print "Query: $string<BR />\n";
	}

	/**
 	 * ó�� ���� �α� ���� ���
	 *
	 * @access	private
	 * @param	string		$string	Query �� ó�� ���� ����
	 */
	function fileLog ($string)
	{
		if ($this->logfile != NULL)
			error_log(date("[Y-m-d H:i:s] ") . $string . "\n", 3, $this->logfile);
	}

	/**
	 * Ư�� ���� escape ó��
	 *
	 * @access	public
	 * @param	string		$string	escape ó���� ���ڿ�
	 * @return	string		escape ó���� ���ڿ�
	 */
	function addslashes ($string)
	{
		return addslashes($string);
	}
}

// #############################################################################

?>