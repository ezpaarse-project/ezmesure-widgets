<?php
/*
Plugin Name: ezMESURE Widget
Description: Charts for ezMESURE data
Version: 1.0.0
Author: ezPAARSE Team
Author URI: https://ezpaarse.org
License: CeCILL
Text Domain: ezmesure-widget
Domain Path: /lang
*/

require_once dirname(__FILE__) . '/options.php';

add_action('plugins_loaded', 'ezmesure_load_textdomain');
function ezmesure_load_textdomain() {
	load_plugin_textdomain( 'ezmesure-widget', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

function search($index, $query) {
  $token   = get_option('ezmesure_token');
  $baseUrl = get_option('ezmesure_url', 'https://ezmesure.couperin.org/api');
  $baseUrl = rtrim($baseUrl, '/');

  if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
    return new WP_Error('invalid_url', 'invalid ezMESURE URL : ' . $baseUrl, array('status' => 501));
  }

  if (!isset($token) || empty($token)) {
    return new WP_Error('no_token', 'ezMESURE token not configured', array('status' => 501));
  }

  $req = wp_safe_remote_post($baseUrl . '/logs/' . $index . '/search', array(
    'headers' => array('Authorization' => 'Bearer ' . $token),
    'body' => $query,
  ));

  $body = wp_remote_retrieve_body($req);

  return json_decode($body);
}

class Ezmesure_Widget extends WP_Widget {
	// Main constructor
	public function __construct() {
		parent::__construct(
			'ezmesure_widget',
			__('ezMESURE Widget', 'ezmesure-widget'),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}
	// The widget form (for the backend)
	public function form($instance) {
		// Set widget defaults
		$defaults = array(
			'title' => '',
			'index' => '',
			'top_length' => 10,
			'field' => '',
			'chart_type' => 'vert_bar',
			'roles' => array()
		);

		// Parse current settings with defaults
		extract( wp_parse_args((array) $instance, $defaults)); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'ezmesure-widget'); ?></label>
			<input
				type="text"
				class="widefat"
				id="<?php echo esc_attr($this->get_field_id('title')); ?>"
				name="<?php echo esc_attr($this->get_field_name('title')); ?>"
				value="<?php echo esc_attr($title ); ?>"
			/>
		</p>

		<?php // Index to search ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('index')); ?>"><?php _e('Index', 'ezmesure-widget'); ?></label>
			<input
				type="text"
				class="widefat"
				id="<?php echo esc_attr($this->get_field_id('index')); ?>"
				name="<?php echo esc_attr($this->get_field_name('index')); ?>"
				value="<?php echo esc_attr($index ); ?>"
			/>
		</p>

		<?php // Top length ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('top_length')); ?>"><?php _e('Top length', 'ezmesure-widget'); ?></label>
			<input
				type="number"
				class="widefat"
				id="<?php echo esc_attr($this->get_field_id('top_length')); ?>"
				name="<?php echo esc_attr($this->get_field_name('top_length')); ?>"
				value="<?php echo esc_attr($top_length ); ?>"
			/>
		</p>

		<?php // Aggregated field ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('field')); ?>"><?php _e('Field', 'ezmesure-widget'); ?></label>
			<input
				type="text"
				class="widefat"
				id="<?php echo esc_attr($this->get_field_id('field')); ?>"
				name="<?php echo esc_attr($this->get_field_name('field')); ?>"
				value="<?php echo esc_attr($field); ?>"
			/>
		</p>

		<?php // Chart type ?>
		<p>
			<label for="<?php echo $this->get_field_id('chart_type'); ?>"><?php _e('Chart type', 'ezmesure-widget'); ?></label>
			<select name="<?php echo $this->get_field_name('chart_type'); ?>" id="<?php echo $this->get_field_id('chart_type'); ?>" class="widefat">
			<?php
			// Your options array
			$options = array(
				'vert_bar' => __('Vertical Bars', 'ezmesure-widget'),
				'bar'      => __('Horizontal Bars', 'ezmesure-widget'),
				'line'     => __('Line', 'ezmesure-widget'),
				'table'    => __('Table', 'ezmesure-widget'),
			);
			// Loop through options and add each one to the select dropdown
			foreach ($options as $key => $name) {
				echo '<option value="' . esc_attr($key) . '" id="' . esc_attr($key) . '" '. selected($chart_type, $key, false) . '>'. $name . '</option>';
			} ?>
			</select>
		</p>

		<?php // Chart restrictions ?>
		<p>
			<label><?php _e('Visible by', 'ezmesure-widget'); ?></label>
			<?php
			// Loop through options and add each one to the select dropdown
			foreach ( get_editable_roles() as $role_name => $role_info ) {
				$selected = (in_array($role_name, $roles) ? 'checked="checked"' : '');
				?>
					<div>
						<input
							type="checkbox"
							id="role-<?php echo esc_attr($role_name); ?>"
							name="<?php echo $this->get_field_name('roles'); ?>[]"
							value="<?php echo esc_attr($role_name); ?>"
							<?php echo $selected; ?>
						/>
						<label for="role-<?php echo esc_attr($role_name); ?>">
							<?php echo $role_info['name'] ?>
						</label>
					</div>
				<?php
			} ?>
		</p>

	<?php }
	// Update widget settings
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title']      = isset($new_instance['title']) ? wp_strip_all_tags($new_instance['title']) : '';
		$instance['index']      = isset($new_instance['index']) ? wp_kses_post($new_instance['index']) : '';
		$instance['top_length'] = isset($new_instance['top_length']) ? wp_kses_post($new_instance['top_length']) : '';
		$instance['field']      = isset($new_instance['field']) ? wp_kses_post($new_instance['field']) : '';
		$instance['chart_type'] = isset($new_instance['chart_type']) ? wp_kses_post($new_instance['chart_type']) : '';
		$instance['roles']      = isset($new_instance['roles']) ? $new_instance['roles'] : array();
		return $instance;
	}
	// Display the widget
	public function widget($args, $instance) {
		extract($args);
		// Check the widget options
		$title      = isset($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
		$index      = isset($instance['index']) ? $instance['index'] : '';
		$top_length = isset($instance['top_length']) ? $instance['top_length'] : '10';
		$field      = isset($instance['field']) ? $instance['field'] : '';
		$chart_type = isset($instance['chart_type']) ? $instance['chart_type'] : '';
		$roles      = isset($instance['roles']) ? $instance['roles'] : array();

		$user = wp_get_current_user();

		if (count($roles) > 0) {
			if (!is_user_logged_in() || count(array_intersect($roles, $user->roles)) == 0) {
				return;
			}
		}

		if (!isset($chart_type) || empty($chart_type)) {
			return;
		}

		$query = array(
			"size" => 0,
			"aggs" => array(
				"items" => array(
					"terms" => array(
						"field" => $field,
						"size" => intval($top_length, 10)
					)
				)
			)
		);

		$data = search($index, $query);
		$buckets = null;

		if ($data && $data->aggregations && $data->aggregations->items && $data->aggregations->items->buckets) {
			$buckets = $data->aggregations->items->buckets;
		}

		$vega_opt = array(
			"actions" => false
		);

		$vega_spec = array(
			"\$schema" => "https://vega.github.io/schema/vega-lite/v3.json",
			"autosize" => array(
				"type" => "pad"
			),
			"data" => array(
				"values" => $buckets
			),
			"encoding" => array()
		);

		switch ($chart_type) {
			case 'line':
				$vega_spec['mark'] = 'line';
				break;
			case 'bar':
			case 'vert_bar':
				$vega_spec['mark'] = 'bar';
				break;
		}

		switch ($chart_type) {
			case 'line':
			case 'vert_bar':
				$vega_spec['encoding']['x'] = array("field" => "key", "type" => "ordinal");
				$vega_spec['encoding']['y'] = array("field" => "doc_count", "type" => "quantitative");
				break;
			case 'bar':
			  $vega_spec['encoding']['x'] = array("field" => "doc_count", "type" => "quantitative");
				$vega_spec['encoding']['y'] = array("field" => "key", "type" => "ordinal");
				break;
		}

		// WordPress core before_widget hook (always include)
		echo $before_widget;
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';
    // Display widget title if defined
    if ($title ) {
      echo $before_title . $title . $after_title;
		}

		if ($buckets == null) {
			echo '<p>' . __('No data', 'ezmesure-widget') . '</p>';
		} else if ($chart_type == 'table') {
			echo '<table>';
			foreach ($buckets as $bucket) {
				echo '<tr><td>' . htmlspecialchars($bucket->key) . '</td><td>' . htmlspecialchars($bucket->doc_count) . '</td></tr>';
			}
			echo '</table>';
		} else {
			// Insert Vega scripts
			wp_enqueue_script('vega', 'https://cdn.jsdelivr.net/npm/vega@4.3.0', array(), false, true);
			wp_enqueue_script('vega-lite', 'https://cdn.jsdelivr.net/npm/vega-lite@3.0.0-rc10', array('vega'), false, true);
			wp_enqueue_script('vega-embed', 'https://cdn.jsdelivr.net/npm/vega-embed@3.24.1', array('vega', 'vega-lite'), false, true);

			// Add an inline script to embed the Vega chart
			wp_add_inline_script('vega-embed', 'vegaEmbed("#vega-chart", ' . json_encode($vega_spec) . ', ' . json_encode($vega_opt) . ');');
			echo '<div id="vega-chart" style="width: 100%; overflow: auto"></div>';
		}

		echo '</div>';
		// WordPress core after_widget hook (always include)
		echo $after_widget;
	}
}
// Register the widget
function my_register_custom_widget() {
	register_widget('Ezmesure_Widget');
}
add_action('widgets_init', 'my_register_custom_widget');

?>
