<?php
/**
 * Callbacks for adding content to an AMP template.
 *
 * @package AMP
 */

/**
 * Register hooks.
 */
function amp_post_template_init_hooks() {
	add_action( 'amp_post_template_head', 'amp_post_template_add_title' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_canonical' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_scripts' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_fonts' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_boilerplate_css' );
	add_action( 'amp_post_template_head', 'amp_print_schemaorg_metadata' );
	add_action( 'amp_post_template_head', 'amp_add_generator_metadata' );
	add_action( 'amp_post_template_css', 'amp_post_template_add_styles', 99 );
	add_action( 'amp_post_template_data', 'amp_post_template_add_analytics_script' );
	add_action( 'amp_post_template_footer', 'amp_post_template_add_analytics_data' );
}

/**
 * Add title.
 *
 * @param AMP_Post_Template $amp_template template.
 */
function amp_post_template_add_title( $amp_template ) {
	?>
	<title><?php echo esc_html( $amp_template->get( 'document_title' ) ); ?></title>
	<?php
}

/**
 * Add canonical link.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_canonical( $amp_template ) {
	?>
	<link rel="canonical" href="<?php echo esc_url( $amp_template->get( 'canonical_url' ) ); ?>" />
	<?php
}

/**
 * Print scripts.
 *
 * @see amp_register_default_scripts()
 * @see amp_filter_script_loader_tag()
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_scripts( $amp_template ) {

	// Just in case the runtime has been overridden by amp_post_template_data filter.
	wp_scripts()->registered['amp-runtime']->src = $amp_template->get( 'amp_runtime_script' );

	// Make sure any filtered extension script URLs get updated in registered scripts before printing.
	$scripts = $amp_template->get( 'amp_component_scripts', array() );
	foreach ( $scripts as $handle => $value ) {
		if ( is_string( $value ) && wp_script_is( $handle, 'registered' ) ) {
			wp_scripts()->registered[ $handle ]->src = $value;
		}
	}

	wp_print_scripts( array_merge(
		array( 'amp-runtime' ),
		array_keys( $scripts )
	) );
}

/**
 * Print fonts.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_fonts( $amp_template ) {
	$font_urls = $amp_template->get( 'font_urls', array() );
	foreach ( $font_urls as $slug => $url ) {
		printf( '<link rel="stylesheet" href="%s">', esc_url( esc_url( $url ) ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}
}

/**
 * Print boilerplate CSS.
 *
 * @since 0.3
 * @see amp_get_boilerplate_code()
 */
function amp_post_template_add_boilerplate_css() {
	echo amp_get_boilerplate_code(); // WPCS: xss ok.
}

/**
 * Print Schema.org metadata.
 *
 * @deprecated Since 0.7
 */
function amp_post_template_add_schemaorg_metadata() {
	_deprecated_function( __FUNCTION__, '0.7', 'amp_print_schemaorg_metadata' );
	amp_print_schemaorg_metadata();
}

/**
 * Print styles.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_styles( $amp_template ) {
	$stylesheets = $amp_template->get( 'post_amp_stylesheets' );
	if ( ! empty( $stylesheets ) ) {
		echo '/* Inline stylesheets */' . PHP_EOL; // WPCS: XSS OK.
		echo implode( '', $stylesheets ); // WPCS: XSS OK.
	}

	$styles = $amp_template->get( 'post_amp_styles' );
	if ( ! empty( $styles ) ) {
		echo '/* Inline styles */' . PHP_EOL; // WPCS: XSS OK.
		foreach ( $styles as $selector => $declarations ) {
			$declarations = implode( ';', $declarations ) . ';';
			printf( '%1$s{%2$s}', $selector, $declarations ); // WPCS: XSS OK.
		}
	}
}

/**
 * Add analytics scripts.
 *
 * @param array $data Data.
 * @return array Data.
 */
function amp_post_template_add_analytics_script( $data ) {
	if ( ! empty( $data['amp_analytics'] ) ) {
		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
	}
	return $data;
}

/**
 * Print analytics data.
 *
 * @since 0.3.2
 */
function amp_post_template_add_analytics_data() {
	$analytics = amp_add_custom_analytics();
	amp_print_analytics( $analytics );
}

/**
 * Add generator metadata.
 *
 * @since 6.0
 */
function amp_add_generator_metadata() {
	printf( '<meta name="generator" content="%s" />', esc_attr( 'AMP Plugin v' . AMP__VERSION ) );
}
