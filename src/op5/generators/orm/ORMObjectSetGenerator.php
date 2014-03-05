<?php

class ORMObjectSetGenerator extends class_generator {
	private $name;
	private $structure;
	private $objectclass;
	private $associations; /** an association is a way to get a one-to-many */
	private $relations; /** a relation is a way to declare a many-to-one for sql */

	public function __construct( $name, $structure ) {
		$this->name = $name;
		$this->structure = $structure[$name];
		$this->objectclass = $this->structure['class'].self::$model_suffix;
		$this->classname = 'Base'.$this->structure['class'].'Set';

		$this->associations = array();

		foreach( $structure as $table => $tbl_struct ) {
			foreach( $tbl_struct['structure'] as $name => $type ) {
				if( is_array( $type ) ) {
					if( $type[0] == $this->structure['class'] ) {
						$this->associations[] = array(
							$table,
							$tbl_struct['class'],
							substr( $type[1], 0, -1 ) // Drop last _
						);
					}
				}
			}
		}

		if (isset($this->structure['relations'])) {
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$this->relations[$this->structure['structure'][$key][1]] = array(
					'tbl' => $structure[$table]['table'],
					'tblkey' => $structure[$table]['key'],
				);
			}
		}
		else {
			$this->relations = array();
		}

		$this->set_model();
	}

	public function generate($skip_generated_note = false) {
		parent::generate($skip_generated_note);
		$this->init_class( 'Object'.$this->structure['source'].'Set', array('abstract') );
		if( isset($this->structure['db_instance']) ) {
			$this->variable('db_instance',$this->structure['db_instance'],'protected');
		}
		$this->variable('table',$this->name,'protected');

		$dbtable_expr = $dbtable = $this->name;

		if (isset($this->structure['table'])) {
			$dbtable = $this->structure['table'];
			$dbtable_expr = $this->structure['table'] . ' AS ' . $this->name;
		}
		if (isset($this->structure['relations'])) {
			$joinexpr = array();
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$relations = $this->relations[$this->structure['structure'][$key][1]];
				$ons = array();
				for ($i = 0; $i < count($foreign_key); $i++) {
					$ons[] = "{$this->structure['structure'][$key][1]}.{$relations['tblkey'][$i]} = {$this->name}.{$foreign_key[$i]}";
				}
				$joinexpr[] = "LEFT JOIN {$relations["tbl"]} AS {$this->structure['structure'][$key][1]} ON " . implode(" AND ", $ons);
			}
			$dbtable_expr .= ' '.implode("", $joinexpr);
		}
		$this->variable('dbtable',$dbtable,'protected');
		$this->variable('dbtable_expr',$dbtable_expr,'protected');

		if( isset($this->structure['default_sort']) )
			$this->variable('default_sort',$this->structure['default_sort'],'protected');

		$this->variable('class',$this->structure['class'].self::$model_suffix,'protected');
		$this->variable('key_columns',$this->structure['key'],'protected');

		$this->generate_format_column_filter();
		$this->generate_format_column_selector();
		$this->generate_format_column_list();

		$this->generate_apply_columns_rewrite();
		$this->generate_filter_valid_columns();

		$this->generate_get_all_columns_list();

		$this->generate_process_field_name();

		foreach( $this->associations as $assoc ) {
			$this->generate_association_get_set( $assoc[0], $assoc[1], $assoc[2] );
		}
		$this->finish_class();
	}

	public function generate_apply_columns_rewrite() {
		$this->init_function('apply_columns_rewrite', array('columns', 'prefix'),array('static'),array('prefix'=>''));
		$this->write( 'foreach('.$this->structure['class'].self::$model_suffix.'::$rewrite_columns as $column => $rewrites) {');
		$this->write(   'if( in_array( $prefix.$column, $columns ) ) {' );
		$this->write(     'foreach($rewrites as $rewrite) {' );
		$this->write(       '$columns[] = $prefix.$rewrite;' );
		$this->write(     '}' );
		$this->write(   '}' );
		$this->write( '}' );
		foreach( $this->structure['structure'] as $name => $type ) {
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$name])) {
				$name = $this->structure['rename'][$name];
			}
			if(is_array($type)) {
				$this->write('$columns = '.$type[0].'Set'.self::$model_suffix.'::apply_columns_rewrite($columns,%s);',$name.".");
			}
		}
		$this->write('return $columns;');
		$this->finish_function();
	}

	public function generate_filter_valid_columns() {
		$translated_structure = array();
		foreach( $this->structure['structure'] as $name => $type ) {
			if(isset($this->structure['rename']) && isset($this->structure['rename'][$name])) {
				$name = $this->structure['rename'][$name];
			}
			$translated_structure[$name] = $type;
		}

		$this->init_function('filter_valid_columns', array('columns','prefix'), array('static'), array('prefix'=>''));
		$this->write('$in_columns = array_flip($columns);');
		$this->write('$out_columns = array();');

		foreach($translated_structure as $name => $type ) {
			if( !is_array($type) ) {
				$this->write('if(isset($in_columns[$prefix.%s])) {', $name);
				$this->write('$out_columns[] = $prefix.%s;',$name);
				$this->write('}');
			}
		}
		foreach($translated_structure as $name => $type ) {
			if( is_array($type) ) {
				$this->write('$tmpset = '.$type[0].'Pool'.self::$model_suffix.'::all();');
				$this->write('$sub_columns = $tmpset->filter_valid_columns($columns,%s);',$type[1]);
				$this->write('$out_columns = array_merge($out_columns, $sub_columns);');
			}
		}

		/*
		foreach($this->structure['key'] as $keypart ) {
			$this->write('if( !in_array(%s, $columns) ) $columns[] = %s;', $keypart, $keypart);
		}
		*/
		$this->write('return $out_columns;');
		$this->finish_function();
	}

	public function generate_format_column_filter() {
		if (isset($this->structure['relations'])) {
			$this->init_function('format_column_filter', array('column'));
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$prefix = $this->structure['structure'][$key][1];
				$this->write('if (!strncmp("'.$prefix.'", $column, '.strlen($prefix).')) {');
				$this->write(    'return "'.$prefix.'.".substr($column, '.(strlen($prefix)+1).');');
				$this->write('}');
			}
			$this->write('return "'.$this->name.'.".$column;');
			$this->finish_function();
		}
	}

	/**
	 * Generate a function that returns a corrected column name
	 * for use in a SELECT clause for making proper aliases available
	 * to the ORM backend.
	 */
	public function generate_format_column_selector() {
		if (isset($this->structure['relations'])) {
			$this->init_function('format_column_selector', array('column'), array('private'));
			foreach ($this->structure['relations'] as $relation) {
				list($foreign_key, $table, $key) = $relation;
				$prefix = $this->structure['structure'][$key][1];
				$this->write('if (!strncmp("'.$prefix.'", $column, '.strlen($prefix).')) {');
				$this->write(    'return "'.$prefix.'.$column AS '.$prefix.'".substr($column, '.(strlen($prefix)+1).');');
				$this->write('}');
			}
			$this->write('return "'.$this->name.'.".$column;');
			$this->finish_function();
		}
	}

	public function generate_format_column_list() {
		if (isset($this->structure['relations'])) {
			$this->init_function('format_column_list', array('columns'), array('protected'), array('false'));
			$this->write('if ($columns == false) {');
			# This won't work quite right, as we won't get the prefix in place for foreign data. Meh.
			$this->write(    'return "'.$this->name.'.*, '.implode(', ', array_map(function($rel) { return $rel[2] . '.*'; }, $this->structure['relations'])).'";');
			$this->write('}');
			$this->write('return implode(", ", array_map(array($this, "format_column_selector"), $columns));');
			$this->finish_function();
		}
	}

	private function generate_get_all_columns_list() {
		$columns = array();
		$subobjs = array();
		foreach ($this->structure['structure'] as $name => $type) {
			if (is_array($type)) {
				$subobjs[$name] = $type;
			} else {
				$columns[] = $name;
			}
		}
		$this->init_function('get_all_columns_list', array('include_nested'), array('static'), array('include_nested'=>true));
		$this->write('$raw_columns = %s;', $columns);
		$this->write('$sub_columns = array();');
		$this->write('if ($include_nested) {');
		foreach ($subobjs as $name => $type) {
			$this->write('$obj_cols = '.$type[0].'Set'.self::$model_suffix.'::get_all_columns_list(false);');
			$this->write('foreach ($obj_cols as $name) {');
			$this->write('$sub_columns[] = %s.$name;', str_replace('_','.',$type[1]));
			$this->write('}');
		}
		$this->write('}');
		$this->write('$virtual_columns = array_keys('.$this->objectclass.'::$rewrite_columns);');
		$this->write('return array_merge($sub_columns, $raw_columns, $virtual_columns);');
		$this->finish_function();
	}

	private function generate_association_get_set($table, $class, $field) {
		$this->init_function('get_'.$table);
		$this->write('$result = '.$class.'Pool'.self::$model_suffix.'::all();');
		$this->write('$result->filter = $this->filter->prefix(%s);', $field.'.');
		$this->write('return $result;');
		$this->finish_function();
	}

	private function generate_process_field_name() {
		$this->init_function('process_field_name', array('name'), array('static'));
		if(isset($this->structure['rename'])) {
			foreach($this->structure['rename'] as $source => $dest ) {
				$this->write('if($name == %s) {', $source);
				$this->write('$name = %s;', $dest);
				$this->write('}');
			}
		}
		foreach($this->structure['structure'] as $field => $type ) {
			if(is_array($type)) {
				$subobjset_class = $type[0].'Set'.self::$model_suffix;
				$this->write('if(substr($name,0,%s) == %s) {', strlen($field)+1, $field.'.');
				$this->write('$subobj_name = substr($name,%d);', strlen($field)+1);
				// Somewhat a livestatus hack, but probably sql to. Only keep the innermost object name if contianing objects
				$this->write('$prefix = "";');
				$this->write('if(false===strpos($subobj_name,".")) {');
				$this->write('$prefix = %s;', $field.'.');
				$this->write('}');
				$this->write('$name = $prefix.'.$subobjset_class.'::process_field_name($subobj_name);');
				$this->write('}');
			}
		}
		$this->write('$name = parent::process_field_name($name);');
		$this->write('return $name;');
		$this->write('}');
	}
}
