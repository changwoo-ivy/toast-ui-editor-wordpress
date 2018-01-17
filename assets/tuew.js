(function ($) {
    $('form').submit(function() {
        var instance = window.tui.Editor.getInstances()[0],
            markdownText = instance.getMarkdown();
        $('input[name="markdown_text"]').val(markdownText);
    });
})(jQuery);