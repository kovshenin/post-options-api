<?php
/**
 * Post Options API
 *
 * This is not a plugin, this is a file you should bundle together with
 * your theme or plugin where you'd like to use the Post Options API.
 * View the Readme file for more details and examples.
 * 
 * Version 1.0
 * Author: Konstantin Kovshenin (kovshenin@gmail.com)
 * http://theme.fm
 * 
 * License: GPL2
 **/

/**
 * Post Options Fields
 *
 * This is a helper class with static methods that can be used in
 * callbacks when registering post options. These are used to create
 * simple fields like text boxes, textareas, checkboxes and more. If
 * something more customizable is needed, you can always run your own
 * callback function.
 *
 * Methods come in pairs, the creator function (factory) and the actual callback.
 * The factory function returns the callback in an array compatible with
 * the post options API.
 *
 **/
if ( ! class_exists( 'Post_Options_Fields_1_0' ) ):
class Post_Options_Fields_1_0 {
	
	// Used to output description if present (for less redundancy)
	public static function description( $args = array() ) {
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) )
			echo "<br /><span class='description'>{$args['description']}</span>";
	}
	
	/**
	 * Checkbox
	 *
	 * Give this checkbox a label and a description through
	 * the arguments array for the best look and feel.
	 **/
	public static function checkbox( $args = array() ) {

		$defaults = array(
			'label' => '',
			'description' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );

		return array( 
			'function' => array( __CLASS__, '_checkbox' ), 
			'args' => $args
		);
	}
	public static function _checkbox( $args = array() ) {
		?>
			<label><input type="checkbox" name="<?php echo $args['name_attr']; ?>" value="1" <?php echo checked( (bool) $args['value'] ); ?> /> <?php echo $args['label']; ?></label>
		<?php
		self::description( $args );		
	}
		
	/**
	 * Text Input
	 *
	 * Factory function accepts a description and a
	 * sanitize_callback if you need some validation.
	 **/
	public static function text( $args = array() ) {
		
		$defaults = array(
			'description' => '',
			'sanitize_callback' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		return array( 
			'function' => array( __CLASS__, '_text' ), 
			'sanitize_callback' => $sanitize_callback, 
			'args' => array( 
				'description' => $description 
			) 
		);
	}
	public static function _text ( $args = array() ) {
		?>
			<input class="large-text" type="text" name="<?php echo $args['name_attr']; ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />
		<?php
		self::description( $args );
	}
	
	/**
	 * Textarea (multi-line text)
	 *
	 * Function accepts a description, rows and
	 * a sanitize_callback for validation.
	 **/
	public static function textarea( $args = array() ) {

		$defaults = array(
			'description' => '',
			'rows' => 4,
			'sanitize_callback' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		return array( 
			'function' => array( __CLASS__, '_textarea' ), 
			'sanitize_callback' => $sanitize_callback, 
			'args' => array( 
				'description' => $description, 
				'rows' => $rows 
			) 
		);
	}
	public static function _textarea( $args = array() ) {
		?>
			<textarea class="large-text" rows="<?php echo $args['rows']; ?>" name="<?php echo $args['name_attr']; ?>"><?php echo esc_textarea( $args['value'] ); ?></textarea>
		<?php
		self::description( $args );
	}
	
	/**
	 * Drop-down Select
	 *
	 * Give it a description and a select_data array where
	 * the array keys are the values of the options and the array
	 * values are the captions. The sanitize_callback argument
	 * is available too.
	 **/
	public static function select( $args = array() ) { 
		
		$defaults = array(
			'description' => '',
			'select_data' => array(),
			'sanitize_callback' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		return array( 
			'function' => array( __CLASS__, '_select' ), 
			'sanitize_callback' => $sanitize_callback, 
			'args' => array( 
				'description' => $description,
				'select_data' => $select_data 
			) 
		);
	}
	public static function _select( $args = array() ) {
		?>
			<select name="<?php echo $args['name_attr']; ?>">
			<?php foreach ( $args['select_data'] as $value => $caption ): ?>
				<option <?php echo selected( $value == $args['value'] ); ?> value="<?php echo $value; ?>"><?php echo $caption; ?></option>
			<?php endforeach; ?>
			</select>
		<?php
		self::description( $args );
	}
	
	/**
	 * Radio Group
	 *
	 * Works very much like the drop-down select box. The radio
	 * data is passed in the radio_data array. Rest is the same.
	 **/
	public static function radio( $args = array() ) {

		$defaults = array(
			'description' => '',
			'radio_data' => array(),
			'sanitize_callback' => ''
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		return array(
			'function' => array( __CLASS__, '_radio' ),
			'sanitize_callback' => $sanitize_callback,
			'args' => array(
				'description' => $description,
				'radio_data' => $radio_data
			)
		);
	}
	public static function _radio( $args = array() ) {
		?>

			<?php foreach ( $args['radio_data'] as $value => $caption ): ?>
				<label><input type="radio" name="<?php echo $args['name_attr']; ?>" value="<?php echo $value; ?>" <?php echo checked( $value == $args['value'] ); ?> > <?php echo $caption; ?></label><br />
			<?php endforeach; ?>

		<?php
		self::description( $args );
	}

	// This is a Singleton class
	private static $instance;
	public static function singleton() {
		if ( ! isset( self::$instance ) ) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
    }
};
endif; // class_exists

/**
 *
 * Post Options Class
 *
 * All the post options logic and functions are implemented here
 * and wrapper functions with simpler names could then be created
 * outside the class for convenience and simplicity.
 *
 * This class handles registration of sections and post options to
 * sections, sections to post types assignment and the actual
 * metabox UI and the post meta IO operations.
 *
 **/
if ( ! class_exists( 'Post_Options_API_1_0' ) ):
class Post_Options_API_1_0 {
	
	private $sections = array();
	private $options = array();
	private $post_types = array();
	
	// Runs during 'init'
	function __construct() {
		add_action( 'admin_init', array( &$this, '_admin_init' ) );
		add_action( 'submitpage_box', array( &$this, '_add_nonce_field' ) );
		add_action( 'submitpost_box', array( &$this, '_add_nonce_field' ) );
	}
	
	// Runs during 'admin_init' doh!
	function _admin_init() {
		
		// Debug voodoo
		//add_action('all', create_function('', 'var_dump(current_filter());'));
		
		// Adds the metabox for each post type
		foreach ( $this->post_types as $post_type => $sections )
			foreach ( $sections as $section_id )
				add_meta_box( 'post-options-' . $section_id, $this->sections[$section_id]['title'], array( &$this, '_meta_box_post_options' ), $post_type, 'normal', 'default', array( 'section_id' => $section_id ) );
			
		// Register the save_post action (for all post types)
		add_action( 'save_post', array( &$this, '_save_post' ), 10, 2 );
	}
	
	// Security check on edit pages
	function _add_nonce_field() {
		global $post;
		if ( $post )
			wp_nonce_field( 'edit_post_options_' . $post->ID , '_post_options_nonce', false );
	}
	
	// Runs during 'save_post'
	function _save_post( $post_id, $post ) {
		
		// Security check (nonce)
		if ( ! isset( $_POST['_post_options_nonce'] ) || ! wp_verify_nonce( $_POST['_post_options_nonce'], 'edit_post_options_' . $post->ID ) )
		      return;
		//check_admin_referer( '_post_options_nonce', 'aedit_post_options_' . $post->ID );
		
		// Don't save revisions and auto-drafts
		if ( wp_is_post_revision( $post_id ) || $post->post_status == 'auto-draft' )
			return;
		
		// If there are no sections in the current post type live no longer!
		$post_type = $post->post_type;
		if ( ! isset( $this->post_types[$post_type] ) )
			return;
		
		// Get the sections for the post type	
		$post_type_sections = $this->post_types[$post_type];
		
		foreach ( $post_type_sections as $section_id ) {
			
			// Skip inexisting sections
			if ( ! $this->section_exists( $section_id ) )
				continue;
			
			$section = $this->sections[$section_id];
			$section_options = $this->options[$section_id];
			
			// Loop through the options in the section (priority voodoo).
			foreach ( $section_options as $priority => $options ) {
				foreach ( $options as $option_id => $option ) {

					// Read the POST data, call the sanitize functions if they exist.
					if ( isset( $_POST['post-options'][$option_id] ) ) {
						$value = $_POST['post-options'][$option_id];
						if ( isset( $option['callback']['sanitize_callback'] ) && is_callable( $option['callback']['sanitize_callback'] ) )
							$value = call_user_func( $option['callback']['sanitize_callback'], $value );

						// Update the post meta for this option.
						update_post_meta( $post_id, $option_id, $value );

					} else {

						// Delete the post meta otherwise (for checkboxes)
						delete_post_meta( $post_id, $option_id );
					}
				} // foreach (option)
			} // foreach (priority, options)
		} // foreach post type sections	
	}
		
	// The meta box, oh the meta box! Runs for the meta box contents.
	function _meta_box_post_options( $post, $metabox ) {		
		$args = $metabox['args'];
		$section_id = $args['section_id'];
		
		if ( ! $this->section_exists( $section_id ) )
			return false;

		$section = $this->sections[$section_id];
		
		// Sort the array by keys (priority)
		ksort( $this->options[$section_id] );
		
		?>
		<!-- Put this in a more decent place when done with styling. -->
		<style>			
			.post-option {
				display: block;
				padding: 8px 0;
				border-bottom: solid 1px #eee;
				line-height: 1.5;
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
			
			.post-option-value br+br {
				display: none;
			}
			
			.post-option input,
			.post-option textarea,
			.post-option select {
				font-size: 12px;
			}
			
		</style>
		<?php
		
		$section_options = $this->options[$section_id];
						
		// Loop through the options.
		foreach ( $section_options as $priority => $options ) {
			foreach ( $options as $option_id => $option ) {

				// Print the option title
				echo "<div class='post-option option-{$option_id}'>";
					echo "<label class='post-option-label'>{$option['title']}</label>";
					echo "<div class='post-option-value'>";
						
						// These will be sent to the callback as arguments
						$args = array(
							'name_attr' => "post-options[{$option_id}]",
							'value' => $this->get_post_option( $post->ID, $option_id )
						);
						
						// There may be more arguments for the callback, merge them
						if ( isset( $option['callback']['args'] ) && is_array( $option['callback']['args'] ) )
							$args = array_merge( $args, $option['callback']['args'] );
						
						// Fire the callback (prints the option value part).
						if ( is_callable( $option['callback'] ) )
							call_user_func( $option['callback'], $args );
						elseif ( is_callable( $option['callback']['function'] ) )
							call_user_func( $option['callback']['function'], $args );
							
					echo "</div>";
				echo "<div class='clear'></div></div>"; // Second div closes .post-option
				
			}
		}		
	}
	
	// Register a post options section
	public function register_post_options_section( $id, $title ) {
		if ( ! isset( $this->sections[$id] ) ) {
			$this->sections[$id] = array(
				'title' => $title
			);
			return true;
		}
		
		return false;
	}
	
	/*
	 * Register Post Option
	 *
	 * Registers a new post option that can then be used in different
	 * sections for different post types. Each post option is also interpreted
	 * during post type save so you don't have to do the saving, we do it for you.
	 */
	public function register_post_option( $args ) {
		
		$defaults = array(
			'id' => null,
			'title' => 'Untitled',
			'callback' => '',
			'section' => '',
			'description' => '',
			'priority' => 10
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		// Madness eh? Well $this->options is an array of sections, each array of sections is an array
		// of priorities and each array of priorities is an array of options, ex:
		// $this->options[section][priority][option_id] = array of options, sorry! :)
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
	
	/*
	 * Add Section to Post Type
	 * 
	 * Registers a given section_id to a given post type slug (post, page, etc)
	 * so this function makes the section actually appear as a meta box on
	 * the edit screen.
	 */
	public function add_section_to_post_type( $section_id, $post_type ) {
		if ( $this->section_exists( $section_id ) && ( ! isset( $this->post_types[$post_type] ) || ! in_array( $section_id, $this->post_types[$post_type] ) ) ) {
			$this->post_types[$post_type][] = $section_id;
			return true;
		}
		
		return false;
	}
	
	// Returns true if given section_id exists
	private function section_exists( $section_id ) {
		return array_key_exists( $section_id, $this->sections );
	}
		
	// Get post option (just a wrapper around get_post_meta)
	public function get_post_option( $post_id, $option_id ) {
		return get_post_meta( $post_id, $option_id, true );
	}
	
	// This is a Singleton class
	private static $instance;
	public static function singleton() {
		if ( ! isset( self::$instance ) ) {
			$className = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
    }
};
endif; // class_exists


/**
 * Singleton Creators
 *
 * Below are the singleton creators for the Post Options API
 * and the Post Options Fields if needed. This is made to ensure
 * compatibility, like when two plugins are running a different
 * version of the Post Options API.
 */

// Returns the Post Options API object (singleton)
if ( ! function_exists( 'get_post_options_api') ) {
	function get_post_options_api( $version ) {
		$class_name = 'Post_Options_API_' . str_replace( '.', '_', $version );
		if ( class_exists( $class_name ) )
			return $class_name::singleton();
		else
			return new WP_Error( 'post-options-api-init', 'You have requested a non-existing version of the Post Options API.' );
	}
}

// Returns the Post Options Fields object (singleton)
if ( ! function_exists( 'get_post_options_api_fields' ) ) {
	function get_post_options_api_fields( $version ) {
		$class_name = 'Post_Options_Fields_' . str_replace( '.', '_', $version );
		if ( class_exists( $class_name ) )
			return $class_name::singleton();
		else
			return new WP_Error( 'post-options-api-fields-init', 'You have requested a non-existing version of the Post Options API Fields.' );
	}
}