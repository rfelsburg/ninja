<?php

class ListView_Controller extends Authenticated_Controller {
	public function index($q = "[hosts] all") {
		$this->xtra_js = array();
		$query = $this->input->get('q', $q);
		$query_order = $this->input->get('s', '');
		
		
		$basepath = 'modules/lsfilter/';
		$ormpath = 'modules/orm/';

		$this->xtra_js[] = $ormpath.'js/LivestatusStructure.js';

		$this->xtra_js[] = $basepath.'js/LSFilter.js';
		$this->xtra_js[] = $basepath.'js/LSFilterLexer.js';
		$this->xtra_js[] = $basepath.'js/LSFilterParser.js';
		$this->xtra_js[] = $basepath.'js/LSFilterPreprocessor.js';
		$this->xtra_js[] = $basepath.'js/LSFilterVisitor.js';
		
		$this->xtra_js[] = $basepath.'media/js/lib.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisitors.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterRenderer.js';
		
		$this->xtra_js[] = $basepath.'media/js/LSFilterMain.js';

		$this->xtra_js[] = $basepath.'media/js/LSFilterHistory.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterList.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterSaved.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterTextarea.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterVisual.js';
		
		$this->xtra_js[] = $basepath.'media/js/LSFilterMultiselect.js';
		$this->xtra_js[] = $basepath.'media/js/LSFilterInputWindow.js';

		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;

		$this->template->css_header = $this->add_view('css_header');
		$this->xtra_css = array();
		$this->xtra_css[] = $basepath.'views/themes/default/css/LSFilterStyle.css';
		$this->template->css_header->css = $this->xtra_css;

		$this->template->title = _('List view');
		$this->template->content = $lview = $this->add_view('listview/listview');
		$this->template->disable_refresh = true;

		$lview->query = $query;
		$lview->query_order = $query_order;
	}
	
	public function fetch_ajax() {
		$query = $this->input->get('query','');
		$columns = $this->input->get('columns',false);
		$sort = $this->input->get('sort',array());
		
		$limit = $this->input->get('limit',false);
		$offset = $this->input->get('offset',false);

		if( $limit === false ) {
			return json::ok( array( 'status' => 'error', 'data' => "No limit specified") );
		}
		
		/* TODO: Fix sorting better sometime
		 * Do it though ORM more orm-ly
		 * Check if columns exists and so on...
		 */
		$sort = array_map(function($el){return str_replace('.','_',$el);},$sort);
		
		try {
			$result_set = ObjectPool_Model::get_by_query( $query );
			
			$data = array();
			foreach( $result_set->it($columns,$sort,$limit,$offset) as $elem ) {
				$data[] = $elem->export();
			}

			return json::ok( array(
				'status' => 'success',
				'totals' => $result_set->get_totals(),
				'data' => $data,
				'table' => $result_set->get_table(),
				'count' => count($result_set)
			) );
		} catch( LSFilterException $e ) {
			return json::ok( array(
				'status' => 'error',
				'data' => $e->getMessage().' at "'.substr($e->get_query(), $e->get_position()).'"',
				'query' => $e->get_query(),
				'position' => $e->get_position()
				));
		} catch( Exception $e ) {
			$this->log->log('error', $e->getMessage() . ' at ' . $e->getFile() . '@' . $e->getLine());
			
			return json::ok( array(
				'status' => 'error',
				'data' => $e->getMessage().' at '.$e->getFile().'@'.$e->getLine()
				));
		}
	}

	public function fetch_saved_queries() {
		$queries = LSFilter_Saved_Queries_Model::get_queries();
		return json::ok( array( 'status' => 'success', 'data' => $queries ) );
	}

	public function save_query() {
		$name = $this->input->get('name',false);
		$query = $this->input->get('query','');
		$scope = $this->input->get('scope','user');

		try {
			
			$result = LSFilter_Saved_Queries_Model::save_query($name, $query, $scope);
			
			if( $result !== false )
				return json::ok( array('status'=>'error', 'data' => $result) );
			
			
			return json::ok( array( 'status' => 'success', 'data' => 'success' ) );
		}
		catch( Exception $e ) {
			return json::ok( array( 'status' => 'error', 'data' => $e->getMessage() ) );
		}
	}
}