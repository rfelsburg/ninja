<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Nagvis widget
 *
 * @author op5 Valerij Stukanov
 */
class Nagvis_Widget extends widget_Base {
	protected $duplicatable = true;

	/**
	 * Return the default friendly name for the widget type
	 *
	 * default to the model name, but should be overridden by widgets.
	 */
	public function get_metadata() {
		return array_merge(parent::get_metadata(), array(
			'friendly_name' => 'NagVis',
			'instanceable' => true
		));
	}

	public function options() {

		$options = parent::options();

		# don't add refresh, nagvis reloads itself
		unset($options['refresh']);

		try {
			$maps = nagvisconfig::get_map_list();
		} catch (ORMDriverException $e) {
			$maps = array();
		} catch (op5LivestatusException $ex) {
			$maps = array();
		}
		$default = false;
		if (count($maps)) {
			$default = $maps[0];
			foreach (array_keys($maps) as $key) {
				$name = $maps[$key];
				unset($maps[$key]);
				$maps[$name] = $name;
			}
			$tmp = array_values($maps);
		}

		$map = new option('nagvis', 'map', 'Map', 'dropdown', array('options' => $maps), $default);
		$height = new option('nagvis', 'height', 'Height (px)', 'input', array('size'=>3), 400);
		$height->should_render_js(false);

		$options[] = $map;
		$options[] = $height;
		return $options;
	}

	public function get_suggested_title () {
		$args = $this->get_arguments();
		if (isset($args['map']) && strlen($args['map'])) {
			return $args['map'];
		} else {
			return 'NagVis';;
		}
	}

	public function index() {
		# fetch widget view path
		$view_path = $this->view_path('view');

		# set required extra resources
		$this->js = array('/js/nagvis');

		$name = Router::$method;
        $external_widget = Router::$controller === "external_widget";

        $arguments = $this->get_arguments();

		# fetch widget content
		require($view_path);
	}
}
