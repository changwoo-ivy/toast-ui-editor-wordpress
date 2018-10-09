<?php
/**
 * Plugin Name: Toast UI Editor WordPress
 * Version:     1.0.0
 * Author:      Changwoo Nam
 * Author URI:  mailto://changwoo@ivynet.co.kr
 * Plugin URI:  https://github.com/changwoo-ivy/toast-ui-editor-wordpress
 */


function tuew_get_js_sources() {
	return array(
		'tuew_jquery'            => 'assets/bower_components/jquery/dist/jquery.js',
		'tuew_markdown_it'       => 'assets/bower_components/markdown-it/dist/markdown-it.js',
		'tuew_to_mark'           => 'assets/bower_components/to-mark/dist/to-mark.js',
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


add_action( 'admin_enqueue_scripts', 'tuew_enqueue_scripts', 9 );

function tuew_enqueue_scripts( $hook ) {

	if ( $hook == 'post.php' ) {
		$js_sources  = tuew_get_js_sources();
		$css_sources = tuew_get_css_sources();
		$url         = plugin_dir_url( __FILE__ );

		foreach ( $js_sources as $handle => $src ) {
			if ( $handle === 'tuew_jquery' ) {
				wp_register_script( $handle, $url . $src, array( 'jquery' ), '3.3.1', FALSE );
			} else {
				wp_register_script( $handle, $url . $src, array(), NULL, FALSE );
			}
		}

		foreach ( $css_sources as $handle => $src ) {
			wp_register_style( $handle, $url . $src, array(), NULL );
		}

		wp_register_script( 'tuew', $url . 'assets/tuew.js', array_keys( $js_sources ), '1.0.0', TRUE );
		wp_register_style( 'tuew', $url . 'assets/tuew.css', array_keys( $css_sources ), '1.0.0' );

		add_action( 'admin_footer', 'tuew_admin_editor' );
	}
}


function tuew_admin_editor( $attrs = array() ) {
	global $post_ID;

	$attrs = wp_parse_args(
		$attrs,
		array(
			'editor_id'              => 'tuew',
			'input_name'             => 'tuew',
			'wrapper_tag'            => 'div',
			'wrapper_attrs'          => array(),
			'editor_options'         => array(),
			'extra_inline_js_after'  => '',
			'extra_inline_js_before' => '',
			'extra_inline_css'       => '',
			'append_after'           => 'textarea#content',
		)
	);

	if ( ! is_array( $attrs['editor_options'] ) ) {
		$attrs['editor_options'] = array();
	}

	$attrs['editor_options'] = wp_parse_args(
		$attrs['editor_options'],
		array(
			'initialEditType' => 'markdown',
			'initialValue'    => get_post_meta( $post_ID, 'tuew_markdown', TRUE ),
			'previewStyle'    => 'vertical',
			'height'          => '500px',
			'exts'            => [],
		)
	);

	if ( ! $attrs['wrapper_tag'] ) {
		return;
	}

	$wrapper_attrs       = $attrs['wrapper_attrs'];
	$wrapper_attrs['id'] = $attrs['editor_id'];

	if ( ! isset( $attrs['editor_options']['el'] ) ) {
		$attrs['editor_options']['el'] = sanitize_key( $attrs['wrapper_tag'] ) . '#' . sanitize_key( $attrs['editor_id'] );
	}

	// template <div id="tuew" .... ><input name="tuew" type="hidden" value=""></div>
	$template = '<' . sanitize_key( $attrs['wrapper_tag'] );
	foreach ( $wrapper_attrs as $key => $value ) {
		$template .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
	}
	$template .= '><input type="hidden" name="' . $attrs['input_name'] . '" value="">';
	$template .= '</' . sanitize_key( $attrs['wrapper_tag'] ) . '>';

	wp_localize_script(
		'tuew',
		'tuew',
		array(
			'template'      => $template,
			'editorOptions' => $attrs['editor_options'],
			'appendAfter'   => $attrs['append_after'],
			'inputName'     => $attrs['input_name'],
			'content'       => '#content',
		)
	);

	if ( ! wp_script_is( 'tuew' ) ) {
		wp_enqueue_script( 'tuew' );
	}

	if ( $attrs['extra_inline_js_before'] ) {
		wp_add_inline_script( 'tuew', $attrs['extra_inline_js_before'], 'before' );
	}

	wp_add_inline_script(
		'tuew',
		"
            (function (\$) {
                \$(document).ready(function() {
                    initToastUiEditor(tuew);
                });
            })(jQuery);",
		'after'
	);

	if ( $attrs['extra_inline_js_after'] ) {
		wp_add_inline_script( 'tuew', $attrs['extra_inline_js_after'], 'after' );
	}

	if ( ! wp_style_is( 'tuew' ) ) {
		wp_enqueue_style( 'tuew' );
	}

	if ( $attrs['extra_inline_css'] ) {
		wp_add_inline_style( 'tuew', $attrs['extra_inline_css'] );
	}
}

add_action( 'save_post', 'tuew_save_post', 10, 1 );

function tuew_save_post( $post_id ) {
	if ( isset( $_POST['tuew'] ) ) {
		$tuew = $_POST['tuew'];
		update_post_meta( $post_id, 'tuew_markdown', $tuew );
	}
}
