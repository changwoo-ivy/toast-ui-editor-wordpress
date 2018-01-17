<?php
/**
 * Plugin Name: Toast UI Editor WordPress
 * Version:     0.0.0
 * Author:      Changwoo Nam
 * Author URI:  mailto://cs.chwnam@gmail.com
 * Plugin URI:  https://github.com/changwoo-ivy/toast-ui-editor-wordpress
 */


function tuew_get_js_sources() {
	return array(
		'tuew_jquery'            => 'assets/bower_components/jquery/dist/jquery.js',
		'tuew_markdown_it'       => 'assets/bower_components/markdown-it/dist/markdown-it.js',
		'tuew_to_mark'           => 'assets/bower_components/toMark/dist/toMark.js',
		'tuew_tui_code_snippet'  => 'assets/bower_components/tui-code-snippet/dist/tui-code-snippet.js',
		'tuew_codemirror'        => 'assets/bower_components/codemirror/lib/codemirror.js',
		'tuew_highlight_pack'    => 'assets/bower_components/highlightjs/highlight.pack.js',
		'tuew_squire_raw'        => 'assets/bower_components/squire-rte/build/squire-raw.js',
		'tuew_tui_editor_editor' => 'assets/bower_components/tui-editor/dist/tui-editor-Editor.js',
	);
}

function tuew_get_css_sources() {
	return array(
		'tuew_codemirror'          => 'assets/bower_components/codemirror/lib/codemirror.css',
		'tuew_github'              => 'assets/bower_components/highlightjs/styles/github.css',
		'tuew_tui_editor'          => 'assets/bower_components/tui-editor/dist/tui-editor.css',
		'tuew_tui_editor_contents' => 'assets/bower_components/tui-editor/dist/tui-editor-contents.css',
	);
}


add_action( 'wp_enqueue_scripts', 'tuew_enqueue_scripts', 9 );

function tuew_enqueue_scripts() {

	$js_sources  = tuew_get_js_sources();
	$css_sources = tuew_get_css_sources();
	$url         = plugin_dir_url( __FILE__ );

	foreach ( $js_sources as $handle => $src ) {
		if ( $handle === 'tuew_jquery' ) {
			wp_register_script( $handle, $url . $src, array( 'jquery' ), '2.2.4', FALSE );
		} else {
			wp_register_script( $handle, $url . $src, array(), FALSE, FALSE );
		}
	}

	foreach ( $css_sources as $handle => $src ) {
		wp_register_style( $handle, $url . $src );
	}

	wp_register_script( 'tuew', $url . 'assets/tuew.js', array_keys( $js_sources ), FALSE, FALSE );
	wp_register_style( 'tuew', $url . 'assets/tuew.css', array_keys( $css_sources ) );
}


function tuew_editor( $id, $attrs = array(), $props = array(), $echo = TRUE ) {

	if ( ! wp_script_is( 'tuew', 'enqueued' ) ) {
		wp_enqueue_script( 'tuew' );
	}

	$id            = esc_attr( $id );
	$props_encoded = $props ? json_encode( $props ) : '{}';

	wp_add_inline_script(
		'tuew',
		"\$(document).ready(function($){\$('div#{$id}').tuiEditor($props_encoded);});"
	);

	if ( ! wp_style_is( 'tuew', 'enqueued' ) ) {
		wp_enqueue_style( 'tuew' );
	}

	$attributes = array();
	foreach ( $attrs as $key => $val ) {
		$key = sanitize_key( $key );
		switch ( $key ) {
			case 'href':
				$val = esc_url( $val );
				break;
			case 'class':
				$val = sanitize_html_class( $val );
				break;
			default:
				$val = esc_attr( $val );
				break;
		}
		$attributes[] = "{$key}=\"{$val}\"";
	}
	$attributes_implode = implode( ' ', $attributes );

	$html = "<div id=\"{$id}\" {$attributes_implode}></div>";

	if ( $echo ) {
		echo $html;
		return NULL;
	}

	return $html;
}


add_shortcode( 'tui_editor', 'tuew_output_editor' );

function tuew_output_editor() {
	ob_start();
	include( __DIR__ . '/templates/test-editor-form.php' );
	return ob_get_clean();
}


add_action( 'admin_post_test_editor', 'tuew_dump_post_data' );

function tuew_dump_post_data() {
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'test-form-action' ) ) {
		var_dump( $_POST );
	}
	exit;
}