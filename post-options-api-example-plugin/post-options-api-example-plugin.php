<?php
/*
 * Plugin Name: Post Options API Example
 * Description: This plugin demonstrates the usage of the Post Options API
 * Author: Konstantin Kovshenin
 * Version 1.1
 * License: GPLv2
 */

// We'll do everything inside init.
add_action( 'init', 'post_options_api_example' );

// Let's test out the above
function post_options_api_example() {
	
	// Include the Post Options API Library bundled with this plugin
	require( dirname( __FILE__ ) . '/inc/post-options-api.1.0.php' );

	// Initialize the Post Options API and Fields
	$post_options = get_post_options_api( '1.0' );
	$post_fields = get_post_options_api_fields( '1.0' );	
	
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
		'callback' => $post_fields->checkbox( array(
			'label' => 'Check me to win $500',
			'description' => 'Any sort of description can go below this checkbox and <a href="#">HTML markup</a> too!'
		) )
	) );
	
	// A text input with a sanitize callback
	$post_options->register_post_option( array( 
		'id' => 'an-input',
		'title' => 'A text input',
		'section' => 'showing-off',
		'priority' => 5,
		'callback' => $post_fields->text( array(
			'description' => 'The text in this input is saved and sanitized using the <code>sanitize_title</code> sanitize callback, so try and input some caps, numbers, symbols and spaces.',
			'sanitize_callback' => 'sanitize_title'
		) )
	) );
	
	// A textarea
	$post_options->register_post_option( array(
		'id' => 'a-textarea',
		'title' => 'A textarea for larger text or perhaps code',
		'section' => 'showing-off',
		'callback' => $post_fields->textarea( array(
			'description' => 'A textarea might be useful for some custom code or perhaps an addition to the post, like a signature or something. Note how the field title flows nicely in to several lines.'
		) )
	) );
	
	// A radio group
	$post_options->register_post_option( array( 
		'id' => 'a-radio-group',
		'title' => 'A radio group',
		'section' => 'showing-off',
		'callback' => $post_fields->radio( array(
			'description' => 'Radio groups accept a <code>$radio_data</code> argument where we pass in an array with values and captions for each item in the group.',
			'radio_data' => array(
				'option-1' => 'The first option',
				'option-2' => 'Another option',
				'option-3' => 'One more option'
			)
		) )
	) );
	
	// A drop-down select box
	$post_options->register_post_option( array(
		'id' => 'a-select-input',
		'title' => 'A drop-down select box',
		'section' => 'showing-off',
		'callback' => $post_fields->select( array(
			'description' => 'Select boxes are similar to radio when it comes to data input, so just provide an array of values and captions.',
			'select_data' => array(
				'option-1' => 'This is the first option',
				'option-2' => 'Hurray for the second one',
				'option-3' => 'There is room for a third'
			)
		) )
	) );
	
	// The real-world section
	
	// Hide sidebar
	$post_options->register_post_option( array( 
		'id' => 'hide-sidebar',
		'title' => 'Hide sidebar',
		'section' => 'real-world',
		'callback' => $post_fields->checkbox( array(
			'label' => 'Hide sidebar on this post',
			'description' => 'Check this to hide the right sidebar on this post.'
		) )
	) );
	
	// Feature this post
	$post_options->register_post_option( array( 
		'id' => 'featured-post',
		'title' => 'Featured post',
		'section' => 'real-world',
		'callback' => $post_fields->checkbox( array(
			'label' => 'This is a featured post',
			'description' => 'Check this to feature the post in the highlights section on the homepage.'
		) )
	) );
	
	// Hide banners
	$post_options->register_post_option( array( 
		'id' => 'hide-banners',
		'title' => 'Hide banners',
		'section' => 'real-world',
		'callback' => $post_fields->checkbox( array(
			'label' => 'Hide all banner ads on this post',
			'description' => 'You might want to hide all your banner advertising if you would like the visitor to focus on the content of this post.'
		) )
	) );

	// Background image
	$post_options->register_post_option( array( 
		'id' => 'background-image',
		'title' => 'Background image URL',
		'section' => 'real-world',
		'callback' => $post_fields->text( array(
			'description' => 'Provide the background image URL to override on this post, useful to create outstanding landing pages without the use of templates.'
		) )
	) );
	
	// A textarea
	$post_options->register_post_option( array(
		'id' => 'greeting-text',
		'title' => 'Greeting text',
		'section' => 'real-world',
		'callback' => $post_fields->textarea( array(
			'description' => 'Enter some text here to show a popup greeting message box as soon as this post loads.', 
			'rows' => 3
		) )
	) );
	
	// A radio group
	$post_options->register_post_option( array( 
		'id' => 'navigation-style',
		'title' => 'Navigation style',
		'section' => 'real-world',
		'callback' => $post_fields->radio( array(
			'description' => 'Customize the navigation style for this page.',
			'radio_data' => array(
				'option-1' => 'Default',
				'option-2' => 'Full navigation menu and submenu',
				'option-3' => 'Menu only, submenu on hover',
				'option-4' => 'Submenu only'
			)
		) )
	) );
	
	// Did you know
	$post_options->register_post_option( array(
		'id' => 'did-you-know',
		'title' => 'Did you know?',
		'section' => 'real-world',
		'callback' => 'post_options_api_page_custom_callback'
	) );
	
	// Page Layout
	$post_options->register_post_option( array(
		'id' => 'page-layout',
		'title' => 'Custom Callback',
		'section' => 'real-world',
		'callback' => 'post_options_api_page_layout_callback'
	) );
		
	// Mood Example
	$post_options->register_post_option( array(
		'id' => 'mood',
		'title' => 'Helper Callback',
		'section' => 'real-world',
		'callback' => $post_fields->select( array(
			'description' => 'How did you feel when writing this post?',
			'select_data' => array(
				'Happy' => 'Happy',
				'Sad' => 'Sad',
				'Disappointed' => 'Disappointed',
				'Awful' => 'Awful'
			)
		) )
	) );
	
	add_filter( 'the_content', 'my_mood_filter' );
}

// A filter to the_content to show off the current mood
function my_mood_filter( $content ) {
	global $post_options, $post;
	$mood = $post_options->get_post_option( $post->ID, 'mood' );
	if ( ! empty( $mood ) )
		$content .= "<p><strong>Mood</strong>: {$mood}</p>";
	return $content;
}

// This function illustrates a custom callback
function post_options_api_page_custom_callback( $args ) {
	?>
	That you can provide your own callback to the post option registration function and that the above are just helpers to spare you time and money?
	<strong>Seriously</strong>, I can do whatever I want here, and even provide a sanitize callback for validation. Want proof? Check out this <code>print_r</code>
	call to the arguments provided to this callback function:<br />
	
	<pre style="margin: 10px;"><?php echo htmlspecialchars( print_r( $args, true ) ); ?></pre>
	
	Go ahead and set it to whatever you like and see how it affects the value:<br />
	<input class="large-text" type="text" name="<?php echo $args['name_attr']; ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />
	<?php
}

// Custom callback, page layout
function post_options_api_page_layout_callback( $args ) {
	$layouts = array(
		'layout-1' => 'Default',
		'layout-2' => 'Full-width',
		'layout-3' => 'Left sidebar'
	);
	?>
	
	<?php foreach( $layouts as $layout => $caption ): ?>
	<div class="mg-color-scheme-item" style="float: left; margin-right: 14px; margin-bottom: 18px;">
		<label style="float: left; clear: both;">
			<input <?php echo checked( $layout == $args['value'] ); ?> type="radio" name="<?php echo $args['name_attr']; ?>" value="<?php echo $layout; ?>" style="margin-bottom: 4px;" /><br />
			<img src="<?php echo plugins_url( 'images/' . $layout . '.png', __FILE__ ); ?>" style="border: solid 1px #ccc;" /><br />
			<span class="description" style="margin-top: 8px; float: left;"><?php echo $caption; ?></span>
		</label>
	</div>
	<?php endforeach; ?>
	<br class="clear" />
	<span class="description">Showing off how one would implement page templates.</span>
	<?php
}