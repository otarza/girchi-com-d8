(function ($, Drupal, CKEDITOR) {

    CKEDITOR.plugins.add('imagefullwidth', {

        // Register the icons.
        icons: 'imagefullwidth',

        // The plugin initialization logic goes inside this method.
        init: function (editor) {

            // Define an editor command that opens our dialog window.
            editor.addCommand('imagefullwidth', {
                contextSensitive: 0,
                startDisabled: 1,
                exec: function (editor) {
                    var sel = editor.getSelection();
                    var self = editor.getCommand('imagefullwidth');
                    var $img = $(sel.getStartElement().$).find("img");

                    if($img.length && self.state == CKEDITOR.TRISTATE_ON) {
                        $img.attr("width", "250px");
                        self.setState(CKEDITOR.TRISTATE_OFF);
                    } else if($img.length && self.state == CKEDITOR.TRISTATE_OFF) {
                        $img.removeAttr("width");
                        $img.removeAttr("height");
                        self.setState(CKEDITOR.TRISTATE_ON);
                    }
                }
            });

            var refreshState = function () {
                var selection = editor.getSelection();
                var command = editor.getCommand('imagefullwidth');
                command.setState(CKEDITOR.TRISTATE_DISABLED);

                if (editor.getSelection().getSelectedText() == null) {

                    var selectedElement = selection.getSelectedElement(),
                        eligible = false;

                    if(
                        (
                            selectedElement.hasClass("cke_widget_image") &&
                            !selectedElement.hasClass('align-right') &&
                            !selectedElement.hasClass('align-left')
                        )
                    ) {
                        eligible = true;
                    }

                    if(eligible) {
                        if(typeof $(selectedElement.$).find("img").attr("width") == 'undefined') {
                            command.setState(CKEDITOR.TRISTATE_ON);
                        } else {
                            command.setState(CKEDITOR.TRISTATE_OFF);
                        }
                    }
                }
            }

            // We'll use throttled function calls, because this event can be fired very, very frequently.
            var throttledFunction = CKEDITOR.tools.eventsBuffer(250, refreshState);
            editor.on('selectionCheck', throttledFunction.input);

            editor.ui.addButton('ImageFullWidth', {
                label: Drupal.t('Set image width to 100% of container'),
                command: 'imagefullwidth'
            });
        }
    });


})(jQuery, Drupal, CKEDITOR);
