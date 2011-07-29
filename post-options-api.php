<?php
/**
 * Plugin Name: Post Options API
 * Plugin URI: http://github.com/kovshenin/post-options-api
 * Description: Post Options API plugin, test driving.
 * Version: 1.0
 * Author: kovshenin
 * Author URI: http://theme.fm
 * License: GPL2
 **/

// Helper static functions for various fields
class Post_Options_Fields {
	public static function checkbox( $label = '', $description = '' ) {
		return array( 'function' => array( 'Post_Options_Fields', '_checkbox' ), 'args' => array( 'label' => $label, 'description' => $description ) );
	}
	
	public static function _checkbox( $args = array() ) {
		?>
			<label><input type="checkbox" name="<?php echo $args['name_attr']; ?>" value="1" <?php echo checked( (bool) $args['value_attr'] ); ?> /> <?php echo $args['label']; ?></label>
		<?php
			self::description( $args );		
	}
	
	public static function description( $args = array() ) {
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) )
			echo "<br /><span class='description'>{$args['description']}</span>";
	}
};

// The post options operations
class Post_Options {
	private $sections = array();
	private $options = array();
	private $post_types = array();
	
	function __construct() {
		add_action( 'admin_init', array( &$this, '_admin_init' ) );
	}
	
	function _admin_init() {
		foreach ( $this->post_types as $post_type => $sections )
			add_meta_box( 'post-options', 'Post Options', array( &$this, '_meta_box_post_options' ), 'post', 'normal', 'default', array( 'post_type' => $post_type ) );
			
		add_action( 'save_post', array( &$this, '_save_post' ), 10, 2 );
	}
	
	function _save_post( $post_id, $post ) {
		// Don't save revisions and auto-drafts
		if ( wp_is_post_revision( $post_id ) || $post->post_status == 'auto-draft' )
			return;
			
		$post_type = $post->post_type;
		if ( isset( $this->post_types[$post_type] ) ) {
			$post_type_sections = $this->post_types[$post_type];
			foreach ( $this->sections as $priority => $sections ) {
				foreach ( $sections as $section_id => $section ) {
					if ( ! in_array( $section_id, $post_type_sections ) ) continue;
					
					$section_options = $this->options[$section_id];

					foreach ( $section_options as $priority => $options ) {
						foreach ( $options as $option_id => $option ) {
							if ( isset( $_POST['post-options'][$option_id] ) ) {
								$value = $_POST['post-options'][$option_id];
								if ( isset( $option['callback']['sanitize_callback'] ) && is_callable( $option['callback']['sanitize_callback'] ) )
									$value = call_user_func( $option['callback']['sanitize_callback'], $value );
									
								// Update the post meta
								update_post_meta( $post_id, $option_id, $value );
							} else {
								
								// Delete the post meta otherwise (for checkboxes)
								delete_post_meta( $post_id, $option_id );
							}
						}
					}
				}
			}
		}
	}
	
	// The meta box, oh the meta box!
	function _meta_box_post_options( $post ) {
		$post_type = $post->post_type;
		$post_type_sections = $this->post_types[$post_type];
		?>
		<style>
			.post-options-section {
				display: block;
				margin-top: 8px;
			}
			.post-options-section .section-title {
				display: block;
				padding: 8px 0;
				font-weight: bold;
				border-bottom: solid 1px #ccc;
			}
			
			.post-option {
				display: block;
				padding: 8px 0;
				border-bottom: solid 1px #ccc;
			}
			
			.post-option label.post-option-label {
				width: 20%;
				display: block;
				float: left;
			}
			
			.post-option .post-option-value {
				display: block;
				margin-left: 25%;
			}
		</style>
		<?php
		foreach ( $this->sections as $priority => $sections ) {
			foreach ( $sections as $section_id => $section ) {
				if ( ! in_array( $section_id, $post_type_sections ) ) continue;
				$section_options = $this->options[$section_id];
				
				echo "<div id='post-options-{$section_id}' class='post-options-section'><div class='section-title'>{$section['title']}</div>";
				
				foreach ( $section_options as $priority => $options ) {
					foreach ( $options as $option_id => $option ) {
						
						echo "<div class='post-option option-{$option_id}'>";
							echo "<label class='post-option-label'>{$option['title']}</label>";
							echo "<div class='post-option-value'>";
								
								// These will be sent to the callback as arguments
								$args = array(
									'name_attr' => "post-options[{$option_id}]",
									'value_attr' => $this->get_post_option( $post->ID, $option_id )
								);
								
								// There may be more arguments for the callback, merge them
								if ( isset( $option['callback']['args'] ) && is_array( $option['callback']['args'] ) )
									$args = array_merge( $args, $option['callback']['args'] );
								
								// Fire the callback.
								if ( is_callable( $option['callback'] ) )
									call_user_func( $option['callback'], $args );
								elseif ( is_callable( $option['callback']['function'] ) )
									call_user_func( $option['callback']['function'], $args );
									
							echo "</div>";
						echo "</div>";
						
					}
				}
				
				echo "</div>";
			}
		}
	}
	
	// Register a post options section
	public function register_post_options_section( $id, $title, $priority = 10 ) {
		if ( ! isset( $this->sections[$priority][$id] ) ) {
			$this->sections[$priority][$id] = array(
				'title' => $title
			);
			return true;
		}
		
		return false;
	}
	
	// Register a post option
	public function register_post_option( $id, $title, $callback, $section, $description = '', $priority = 10 ) {
		if ( ! isset( $this->options[$section][$priority][$id] ) && ( is_callable( $callback ) || ( is_array( $callback ) && is_callable( $callback['function'] ) ) ) ) {
			$this->options[$section][$priority][$id] = array(
				'title' => $title,
				'callback' => $callback,
				'description' => $description
			);
			return true;
		}
		
		return false;
	}
	
	// Add section to post type
	public function add_section_to_post_type( $section_id, $post_type ) {
		if ( $this->section_exists( $section_id ) && ( ! isset( $this->post_types[$post_type] ) || ! in_array( $section_id, $this->post_types[$post_type] ) ) ) {
			$this->post_types[$post_type][] = $section_id;
			return true;
		}
		
		return false;
	}
	
	// Returns true if given section_id exists
	function section_exists( $section_id ) {
		foreach ( $this->sections as $priority => $sections ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( $section_id === $section_key )
					return true;
			}
		}
		
		return false;
	}
	
	// Get post option
	public function get_post_option( $post_id, $option_id ) {
		return get_post_meta( $post_id, $option_id, true );
	}
};

add_action( 'init', create_function( '', 'global $post_options; $post_options = new Post_Options();' ) );
add_action( 'init', 'post_options_test' );

function post_options_test() {
	global $post_options;
	
	$post_options->register_post_options_section( 'section-id', 'Section Title' );
	$post_options->register_post_option( 'option-id', 'Option Title', array( 'function' => 'my_callback', 'args' => array( 1, 2, 3 ), 'sanitize_callback' => 'my_option_sanitize' ), 'section-id' );
	$post_options->register_post_option( 'second-option', 'One More Option', 'my_callback', 'section-id' );
	$post_options->register_post_option( 'third-option', 'Third through helper', Post_Options_Fields::checkbox('Full width layout', 'Enable full width layout for this post.'), 'section-id' );

	$post_options->add_section_to_post_type( 'section-id', 'post' );
}

function my_option_sanitize( $option ) {
	return $option . '123';
}

function my_callback( $args ) {
	$value_attr = $args['value_attr'];
	?>
		<input type="checkbox" name="<?php echo $args['name_attr']; ?>" value="<?php echo $args['value_attr']; ?>" <?php echo checked( (bool) $value_attr ); ?> /><br />
	<?php
}