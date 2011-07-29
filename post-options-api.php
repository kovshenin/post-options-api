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
	
	// Used to output description if present (for less redundancy)
	public static function description( $args = array() ) {
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) )
			echo "<br /><span class='description'>{$args['description']}</span>";
	}
	
	// Checkbox
	public static function checkbox( $description = '', $label = '' ) {
		return array( 
			'function' => array( 'Post_Options_Fields', '_checkbox' ), 
			'args' => array( 
				'label' => $label, 
				'description' => $description 
			) 
		);
	}
	
	public static function _checkbox( $args = array() ) {
		?>
			<label><input type="checkbox" name="<?php echo $args['name_attr']; ?>" value="1" <?php echo checked( (bool) $args['value'] ); ?> /> <?php echo $args['label']; ?></label>
		<?php
		self::description( $args );		
	}
		
	// Regular text input
	public static function text( $description = '', $sanitize_callback = '' ) {
		return array( 
			'function' => array( 'Post_Options_Fields', '_text' ), 
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
	
	// Textarea input
	public static function textarea( $description = '', $rows = 4, $sanitize_callback = '' ) {
		return array( 
			'function' => array( 'Post_Options_Fields', '_textarea' ), 
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
	
	// Select input
	public static function select( $description = '', $select_data = array(), $sanitize_callback = '' ) {
		return array( 
			'function' => array( 'Post_Options_Fields', '_select' ), 
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
	
	// A radio group input
	public static function radio( $description = '', $radio_data = array(), $sanitize_callback = '' ) {
		return array(
			'function' => array( 'Post_Options_Fields', '_radio' ),
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
				margin-top: 12px;
			}
			.post-options-section .section-title {
				display: block;
				padding: 12px 0;
				font-weight: bold;
				border-bottom: solid 1px #ccc;
			}
			
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
			
			.post-options-section input,
			.post-options-section textarea,
			.post-options-section select {
				font-size: 12px;
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
									'value' => $this->get_post_option( $post->ID, $option_id )
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
						echo "<div class='clear'></div></div>";
						
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

// Let's test out the above
function post_options_test() {
	global $post_options;
	
	// Register two sections and add them both to the 'post' post type
	$post_options->register_post_options_section( 'showing-off', 'Showing off various post options' );
	$post_options->register_post_options_section( 'real-world', 'Some real world examples' );
	$post_options->add_section_to_post_type( 'showing-off', 'post' );
	$post_options->add_section_to_post_type( 'real-world', 'post' );
	
	// The showing off section
	
	// A simple checkbox
	$post_options->register_post_option( array( 
		'id' => 'a-checkbox',
		'title' => 'A checkbox',
		'section' => 'showing-off',
		'callback' => Post_Options_Fields::checkbox(
			'Any sort of description can go below this checkbox and <a href="#">HTML markup</a> too!',
			'Check me to win $500'
		)
	) );
	
	// A text input with a sanitize callback
	$post_options->register_post_option( array( 
		'id' => 'an-input',
		'title' => 'A text input',
		'section' => 'showing-off',
		'callback' => Post_Options_Fields::text(
			'The text in this input is saved and sanitized using the <code>sanitize_title</code> sanitize callback, so try and input some caps, numbers, symbols and spaces.',
			'sanitize_title'
		)
	) );
	
	// A textarea
	$post_options->register_post_option( array(
		'id' => 'a-textarea',
		'title' => 'A textarea for larger text or perhaps code',
		'section' => 'showing-off',
		'callback' => Post_Options_Fields::textarea(
			'A textarea might be useful for some custom code or perhaps an addition to the post, like a signature or something. Note how the field title flows nicely in to several lines.'
		)
	) );
	
	// A radio group
	$post_options->register_post_option( array( 
		'id' => 'a-radio-group',
		'title' => 'A radio group',
		'section' => 'showing-off',
		'callback' => Post_Options_Fields::radio(
			'Radio groups accept a <code>$radio_data</code> argument where we pass in an array with values and captions for each item in the group.',
			array(
				'option-1' => 'The first option',
				'option-2' => 'Another option',
				'option-3' => 'One more option'
			)
		)
	) );
	
	// A drop-down select box
	$post_options->register_post_option( array(
		'id' => 'a-select-input',
		'title' => 'A drop-down select box',
		'section' => 'showing-off',
		'callback' => Post_Options_Fields::select(
			'Select boxes are similar to radio when it comes to data input, so just provide an array of values and captions.',
			array(
				'option-1' => 'This is the first option',
				'option-2' => 'Hurray for the second one',
				'option-3' => 'There is room for a third'
			)
		)
	) );
	
	// The real-world section
	
	// Hide sidebar
	$post_options->register_post_option( array( 
		'id' => 'hide-sidebar',
		'title' => 'Hide sidebar',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::checkbox(
			'Check this to hide the right sidebar on this post.',
			'Hide sidebar on this post'
		)
	) );
	
	// Feature this post
	$post_options->register_post_option( array( 
		'id' => 'featured-post',
		'title' => 'Featured post',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::checkbox(
			'Check this to feature the post in the highlights section on the homepage.',
			'This is a featured post'
		)
	) );
	
	// Hide sidebar
	$post_options->register_post_option( array( 
		'id' => 'hide-banners',
		'title' => 'Hide banners',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::checkbox(
			'You might want to hide all your banner advertising if you would like the visitor to focus on the content of this post.',
			'Hide all banner ads on this post'
		)
	) );

	// Background image
	// A text input with a sanitize callback
	$post_options->register_post_option( array( 
		'id' => 'background-image',
		'title' => 'Background image URL',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::text(
			'Provide the background image URL to override on this post, useful to create outstanding landing pages without the use of templates.'
		)
	) );
	
	// A textarea
	$post_options->register_post_option( array(
		'id' => 'greeting-text',
		'title' => 'Greeting text',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::textarea(
			'Enter some text here to show a popup greeting message box as soon as this post loads.', 3
		)
	) );
	
	// A radio group
	$post_options->register_post_option( array( 
		'id' => 'navigation-style',
		'title' => 'Navigation style',
		'section' => 'real-world',
		'callback' => Post_Options_Fields::radio(
			'Customize the navigation style for this page.',
			array(
				'option-1' => 'Default',
				'option-2' => 'Full navigation menu and submenu',
				'option-3' => 'Menu only, submenu on hover',
				'option-4' => 'Submenu only'
			)
		)
	) );
	
	// Did you know
	$post_options->register_post_option( array(
		'id' => 'did-you-know',
		'title' => 'Did you know?',
		'section' => 'real-world',
		'callback' => 'my_callback'
	) );
}

// This function illustrates a custom callback
function my_callback( $args ) {
	?>
	That you can provide your own callback to the post option registration function and that the above are just helpers to spare you time and money?
	<strong>Seriously</strong>, I can do whatever I want here, and even provide a sanitize callback for validation. Want proof? Check out this <code>print_r</code>
	call to the arguments provided to this callback function:<br />
	
	<pre style="margin: 10px;"><?php echo htmlspecialchars( print_r( $args, true ) ); ?></pre>
	
	Go ahead and set it to whatever you like and see how it affects the value:<br />
	<input class="large-text" type="text" name="<?php echo $args['name_attr']; ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />
	<?php
}