<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once( dirname(__FILE__)."/merlin_get_kohana_db_field_metadata.php" );

/*
 * Class: Database_Pdogeneric_Driver
 *  This base class implements most of the Kohana database API for PDO.
 *  It's abstract, since there are some pieces that are required to end up with
 *  a working Kohana backend that are completely backend specific.
 *
 * Version 1.0 alpha
 *  author    - Doutu, updated by gregmac. Ported from sqlite-only
 *  to mysql/sqlite by Stephan Beal (20101228). Converted to a base class by
 *  Robin Sonefors.
 *  copyright - (c) BSD
 *  license   - <no>
 */

abstract class Database_Pdogeneric_Driver extends Database_Driver
{
	// Database connection link
	protected $link;
	protected $db_config;

	/*
	 * Constructor: __construct
	 *  Sets up the config for the class.
	 *
	 * Parameters:
	 *  config - database configuration
	 *
	 */
	public function __construct($config)
	{
		$this->db_config = $config;
	}

	public function query($sql)
	{
		// FIXME: add caching
		try
		{
			$sth = $this->link->prepare($sql);
			return new Pdogeneric_Result($sth, $this->link, $this->db_config['object'], $sql);
		}
		catch (PDOException $e)
		{
			throw new Kohana_Database_Exception('database.error', $e->getMessage());
		}
	}

	public function set_charset($charset)
	{
		# does oracle/pgsql/whatever have ways of doing this?
		throw new Kohana_Database_Exception('database.not_implemented',
		                                    __FUNCTION__);
	}

	public function escape_table($table)
	{
		if (!$this->db_config['escape'])
			return $table;
		return '`'.str_replace('.', '`.`', $table).'`';
	}

	public function escape_column($column)
	{
		if (!$this->db_config['escape'])
			return $column;

		if (strtolower($column) == 'count(*)' OR $column == '*')
			return $column;

		// This matches any modifiers we support to SELECT.
		if ( ! preg_match('/\b(?:rand|all|distinct(?:row)?|high_priority|sql_(?:small_result|b(?:ig_result|uffer_result)|no_cache|ca(?:che|lc_found_rows)))\s/i', $column)) {
			if (stripos($column, ' AS ') !== FALSE) {
				// Force 'AS' to uppercase
				$column = str_ireplace(' AS ', ' AS ', $column);

				// Runs escape_column on both sides of an AS statement
				$column = array_map(array($this, __FUNCTION__), explode(' AS ', $column));

				// Re-create the AS statement
				return implode(' AS ', $column);
			}
			return preg_replace('/[^.*]+/', '`$0`', $column);
		}

		$parts = explode(' ', $column);
		$column = '';

		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			// The column is always last
			if ($i == ($c - 1)) {
				$column .= preg_replace('/[^.*]+/', '`$0`', $parts[$i]);
			} else {    // otherwise, it's a modifier
				$column .= $parts[$i].' ';
			}
		}
		return $column;
	}

	// This *can't* be implemented properly for oracle. Stupid oracle.
	public function limit($limit, $offset = 0) {}

	public function compile_select($database)
	{
		$sql = ($database['distinct'] == TRUE) ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= (count($database['select']) > 0) ? implode(', ', $database['select']) : '*';

		if (count($database['from']) > 0) {
			$sql .= "\nFROM ";
			$sql .= implode(', ', $database['from']);
		}

		if (count($database['join']) > 0) {
			foreach($database['join'] AS $join) {
				$sql .= "\n".$join['type'].'JOIN '.implode(', ', $join['tables']).' ON '.$join['conditions'];
			}
		}

		if (count($database['where']) > 0) {
			$sql .= "\nWHERE ";
		}

		$sql .= implode("\n", $database['where']);

		if (count($database['groupby']) > 0) {
			$sql .= "\nGROUP BY ";
			$sql .= implode(', ', $database['groupby']);
		}

		if (count($database['having']) > 0) {
			$sql .= "\nHAVING ";
			$sql .= implode("\n", $database['having']);
		}

		if (count($database['orderby']) > 0) {
			$sql .= "\nORDER BY ";
			$sql .= implode(', ', $database['orderby']);
		}

		if (is_numeric($database['limit'])) {
			$sql .= "\n";
			$sql = $this->limit($sql, $database['limit'], $database['offset']);
		}

		return $sql;
	}

	public function list_tables(Database $db)
	{
		throw new Kohana_Database_Exception ('database.not_implemented', __FUNCTION__);
	}

	public function show_error()
	{
		$err = $this->link->errorInfo();
		return isset($err[2]) ? $err[2] : 'Unknown error!';
	}

	public function list_fields($table)
	{
		static $tables = array();

		if (empty($tables[$table]))
		{
			foreach ($this->field_data($table) as $row)
			{
				// Make an associative array
				$tables[$table][$row->Field] = $this->sql_type($row->Type);

				if ($row->Key === 'PRI' AND $row->Extra === 'auto_increment') {
					// For sequenced (AUTO_INCREMENT) tables
					$tables[$table][$row->Field]['sequenced'] = TRUE;
				}

				if ($row->Null === 'YES') {
					// Set NULL status
					$tables[$table][$row->Field]['null'] = TRUE;
				}
			}
		}

		if (!isset($tables[$table]))
			throw new Kohana_Database_Exception('database.table_not_found', $table);

		return $tables[$table];
	}

	public function field_data($table)
	{
		/* The API docs don't specify what exactly i should be
		 returning here, so i just implemented a clone of the
		 MySQL driver's functionality. */

		static $columns = array();
		if (!empty($columns[$table])) {
			return $columns[$table];
		}

		# UGLY FUGLY HACK: return pre-generated field data. We
		# only need this for Oracle, but since we generate this
		# data from MySQL, we can use it for the MySQL back-end
		# as well (and save ourselves a few queries to find the
		# information server-side).
		$columns = merlin_get_kohana_db_field_metadata();
		return $columns[$table];

		if ($this->isMysql()) {
			$sql = 'SHOW COLUMNS FROM '.$this->escape_table($table);
			$res = $this->query($sql);
			$cols = array();

			foreach($res->result(TRUE) as $row) {
				$cols[] = $row;
			}
			unset($res);
			return $columns[$table] = $cols;
		}
		else if ($this->isSqlite()) {
			$sql = 'PRAGMA table_info('.$this->escape_table($table).')';
			$res = $this->link->query($sql);
			$cols = array();
			foreach ($res as $row)
			{
				$obj = new stdClass();
				/* Reminder:
				 sqlite> .headers on
				 sqlite> PRAGMA table_info(t1);
				 cid|name|type|notnull|dflt_value|pk
				 0|i|int|0||0
				 1|s|varchar(32)|0||0
				 */
				$obj->Field = $row[1];
				$obj->Type = $row[2];
				$obj->Null = $row[3] ? 'NO' : 'YES';
				$obj->Default = $row[4];
				$obj->Key = $row[5] ? 'PRI' : NULL;
				$obj->Extra = NULL;
				$cols[] = $obj;
			}
			unset($res);
			return $columns[$table] = $cols;
		}
		else {
			throw new Kohana_Database_Exception
				('database.not_implemented', __FUNCTION__);
		}
	}

	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function version()
	{
		return $this->link->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
	}

	function stmt_prepare($sql = '')
	{
		is_object($this->link) or $this->connect();
		return new Kohana_Pdogeneric_Statement($sql, $this->link);
	}
} // End Database_Pdogeneric_Driver Class

/**
 * Kohana's support for prepared statements seems to be less-than-well defined.
 * This tries to stick to what Mysqli does (which is also what Pgsql does).
 *
 * Because I'm lazy, there's no support for binding output parameters, only
 * input.
 */
class Kohana_Pdogeneric_Statement {
	protected $link = null;
	protected $stmt;
	protected $params = array();

	public function __construct($sql, $link)
	{
		$this->link = $link;
		$this->stmt = $this->link->prepare($sql);
	}

	/**
	 * The first param is for a "type hint string" used in other drivers
	 * ("si" means "a string and an int"). We don't give a crap about that.
	 */
	public function bind_params($unused, $params)
	{
		$this->params = $params;
	}

	public function execute()
	{
		$this->stmt->execute($this->params);
		return $this->stmt;
	}
}

/*
 * PDO Result
 */
class Pdogeneric_Result extends Database_Result {

	// Data fetching types
	protected $fetch_type  = PDO::FETCH_OBJ;
	protected $return_type = PDO::FETCH_ASSOC;

	/**
	 * Sets up the result variables.
	 *
	 * @param  resource  query result
	 * @param  resource  database link
	 * @param  boolean   return objects or arrays
	 * @param  string    SQL query that was run
	 */
	public function __construct($result, $link, $object = TRUE, $sql)
	{
		if (is_object($result) OR $result = $link->prepare($sql)) {
			// run the query
			try {
				$result->execute();
			} catch (PDOException $e) {
				throw new Kohana_Database_Exception('database.error', $e->getMessage());
			}

			if (preg_match('/^\s*(SHOW|DESCRIBE|SELECT|PRAGMA|EXPLAIN)/i', $sql)) {
				$this->result = $result;
				$this->current_row = 0;

				$this->total_rows = $this->pdo_row_count();

				$this->fetch_type = ($object === TRUE) ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
			} elseif (preg_match('/^\s*(DELETE|INSERT|UPDATE)/i', $sql)) {
				# completely broken, but I don't care
				$this->insert_id  = 0;
			}
		} else {
			// SQL error
			$err = $link->errorInfo();
			throw new Kohana_Database_Exception
				('database.error', $err[2].' - SQL=['.$sql.']');
		}

		// Set result type
		$this->result($object);

		// Store the SQL
		$this->sql = $sql;
	}

	protected function pdo_row_count()
	{
		$count = 0;
		while ($this->result->fetch()) {
			$count++;
		}

		// The query must be re-fetched now.
		$this->result->execute();
		return $count;
	}

	/*
	 * Destructor: __destruct
	 *  Magic __destruct function, frees the result.
	 */
	public function __destruct()
	{
		if (is_object($this->result)) {
			$this->result->closeCursor();
			$this->result = NULL;
		}
	}

	public function result($object = TRUE, $type = PDO::FETCH_BOTH)
	{
		$this->fetch_type = ((bool) $object) ? PDO::FETCH_OBJ : PDO::FETCH_BOTH;

		if ($this->fetch_type == PDO::FETCH_OBJ) {
			$this->return_type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
		} else {
			$this->return_type = $type;
		}
		return $this;
	}

	public function as_array($object = NULL, $type = PDO::FETCH_ASSOC)
	{
		return $this->result_array($object, $type);
	}

	public function result_array($object = NULL, $type = PDO::FETCH_ASSOC)
	{
		$rows = array();

		if (is_string($object)) {
			$fetch = $object;
		} elseif (is_bool($object)) {
			if ($object === TRUE) {
				$fetch = PDO::FETCH_OBJ;

				// NOTE - The class set by $type must be defined before fetching the result,
				// autoloading is disabled to save a lot of stupid overhead.
				$type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
			} else {
				$fetch = PDO::FETCH_OBJ;
			}
		} else {
			// Use the default config values
			$fetch = $this->fetch_type;

			if ($fetch == PDO::FETCH_OBJ) {
				$type = (is_string($type) AND class_exists($type, true)) ? $type : 'stdClass';
			}
		}

		try {
			while ($row = $this->result->fetch($fetch)) {
				$rows[] = $row;
			}
		} catch(PDOException $e) {
			throw new Kohana_Database_Exception('database.error', $e->getMessage());
			return FALSE;
		}
		return $rows;
	}

	public function list_fields()
	{
		$field_names = array();
		for ($i = 0, $max = $this->result->columnCount(); $i < $max; $i++) {
			$info = $this->result->getColumnMeta($i);
			$field_names[] = $info['name'];
		}
		return $field_names;
	}

	public function seek($offset)
	{
		# To request a scrollable cursor for your PDOStatement object,
		# you must set the PDO::ATTR_CURSOR attribute to PDO::CURSOR_SCROLL
		# when you prepare the statement.
		Kohana::log('error', get_class($this).' does not support scrollable cursors, '.__FUNCTION__.' call ignored');

		return FALSE;
	}

	public function offsetGet($offset)
	{
		try {
			return $this->result->fetch($this->fetch_type, PDO::FETCH_ORI_ABS, $offset);
		} catch(PDOException $e) {
			throw new Kohana_Database_Exception('database.error', $e->getMessage());
		}
	}

	public function rewind()
	{
		# Same problem that seek() has, see above.
		return $this->seek(0);
	}

} // End PdoSqlite_Result Class
