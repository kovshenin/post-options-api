# Post Options API

Howdy and welcome to the Post Options API, an alternative approach at Custom Fields in WordPress. The API makes it extremely easy to show different kinds of extra fields to posts, pages and custom post types in WordPress, with nice looking meta boxes and a whole set of predefined callback functions to generate controls like checkboxes, text fields and more, although you can always use your custom callback.

If you're familiar with WordPress' Settings API you'll find this extremely easy to use, except of course for the object/classes madness that we created. Let me explain.

To ensure compatibility with other themes and plugins that might be using the Post Options API (perhaps a different version) we've built in two initialization functions -- `get_post_options_api` to retrieve the API itself and `get_post_options_api_fields` to retrieve the fields helper. Here's how you use them:

	function your_function() {
		
		// Initialize Post Options API
		require( dirname( __FILE__ ) . '/inc/post-options-api.1.0.php' );
		$post_options = get_post_options_api( '1.0' );
		$post_fields = get_post_options_api_fields( '1.0' );
	}
	
	add_action( 'init', 'your_function' );

And then you can use the `$post_options` and `$post_fields` objects like you normally would, i.e.:

	$post_options->register_post_options_section( ... );
	$post_options->add_section_to_post_type( ... );
	$post_options->register_post_option( 
		...
		'callback' => $post_fields->checkbox( ... )
	);

Hope this explains it and doesn't make it too crazy to use, but we really had to go this way to ensure that the same version is always one, i.e. the class is a singleton, never redefined and never initialized twice (unless you explicitly ask it to of course).

Good luck using this and if you have any questions, feel free to contact me at kovshenin@gmail.com or kovshenin on Skype. Cheers!