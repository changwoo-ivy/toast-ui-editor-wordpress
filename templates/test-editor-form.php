<form name="test-editor-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php tuew_editor( 'test-editor', array(), array() ); ?>
	<input type="hidden" name="action" value="test_editor" />
	<input type="hidden" name="markdown_text" value="" />
	<?php wp_nonce_field( 'test-form-action' ); ?>
	<button type="submit" class="button" id="test-button">테스트</button>
</form>
