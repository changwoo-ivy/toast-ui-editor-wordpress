(function ($) {
    function initToastUiEditor(toastUiEditor) {
        var obj = $.extend({
                template: '',
                editorOptions: {},
                appendAfter: '',
                inputName: '',
                content: ''
            }, toastUiEditor),
            editor = null,
            content = $(obj.content);

        $(obj.appendAfter).after(obj.template);

        if (obj.editorOptions.hasOwnProperty('el')) {
            obj.editorOptions.el = document.querySelector(obj.editorOptions.el);
            editor = new tui.Editor(obj.editorOptions);
            editor.eventManager.listen('previewBeforeHook', function (html) {
                content.text(html);
            });
        }

        $('form#post').submit(function () {
            $('input[name="' + obj.inputName + '"]').val(editor.getMarkdown());
        });
    }

    window.initToastUiEditor = initToastUiEditor;
})(jQuery);
