(function ($, Drupal, CKEDITOR) {

    CKEDITOR.plugins.add('zoomableimage', {

        // Register the icons.
        icons: 'zoomableimage',

        // The plugin initialization logic goes inside this method.
        init: function (editor) {

            // Define an editor command that opens our dialog window.
            editor.addCommand('zoomableimage', {
                allowedContent: {
                    img: {
                        attributes: {
                            '!data-is-zoomable': true
                        }
                    }
                },
                requiredContent: new CKEDITOR.style({
                    element: 'img',
                    attributes: {
                        'data-is-zoomable': ''
                    }
                }),
                contextSensitive: 0,
                startDisabled: 1,
                exec: function (editor) {
                    var sel = editor.getSelection(),
                        self = editor.getCommand('zoomableimage'),
                        selectedElement = sel.getSelectedElement(),
                        $innerEntity = $(selectedElement.$).find("drupal-entity"),
                        $img = $(selectedElement.$).find("img"),
                        $target = null;

                    if($innerEntity.length) {
                        $target = $innerEntity;
                    } else if($img.length) {
                        $target = $img;
                    }

                    if($target && self.state == CKEDITOR.TRISTATE_ON) {
                        $target.attr("data-is-zoomable", "false");
                        self.setState(CKEDITOR.TRISTATE_OFF);
                    } else if($target && self.state == CKEDITOR.TRISTATE_OFF) {
                        $target.attr("data-is-zoomable", "true");
                        self.setState(CKEDITOR.TRISTATE_ON);
                    }
                }
            });

            var refreshState = function () {
                var selection = editor.getSelection();
                var command = editor.getCommand('zoomableimage');
                command.setState(CKEDITOR.TRISTATE_DISABLED);

                if (editor.getSelection().getSelectedText() == null) {

                    var selectedElement = selection.getSelectedElement(),
                        $innerEntity = $(selectedElement.$).find("drupal-entity");

                    if(selectedElement.hasClass("cke_widget_image")) {
                        if($(selectedElement.$).find("img").attr("data-is-zoomable") == 'true') {
                            command.setState(CKEDITOR.TRISTATE_ON);
                        } else {
                            command.setState(CKEDITOR.TRISTATE_OFF);
                        }
                    } else if(
                        selectedElement.hasClass("cke_widget_drupalentity")
                        && $innerEntity.attr("data-embed-button") == 'insert_media'
                    ) {
                        if($innerEntity.attr("data-is-zoomable") == 'true') {
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

            editor.ui.addButton('ZoomableImage', {
                label: Drupal.t('Allow image to be zoomed in'),
                command: 'zoomableimage'
            });
        }
    });


})(jQuery, Drupal, CKEDITOR);
