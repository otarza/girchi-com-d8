(function ($, Drupal, CKEDITOR) {

    CKEDITOR.plugins.add('galleryplaceholder', {

        // Register the icons.
        icons: 'galleryplaceholder',

        // The plugin initialization logic goes inside this method.
        init: function (editor) {

            editor.addCommand('insert_gallery_placeholder_button', {
                exec : function () {
                    insert_content('<div class="om_social_embed_buttons_placeholder_widget om_gallery">[gallery]</div>', editor);
                }
            });

            editor.ui.addButton('GalleryPlaceholder', {
                label: 'Insert [gallery] placeholder',
                command: 'insert_gallery_placeholder_button'
            });

            editor.widgets.add( 'om_social_embed_buttons_placeholder_widget', {
                upcast: function( element ) {
                    return element.name == 'div' && element.hasClass( 'om_social_embed_buttons_placeholder_widget' );
                }
            });

            // insert content helper
            var insert_content = function(content, editor) {

                editor.fire('saveSnapshot');

                editor.insertHtml(content);

                editor.fire('saveSnapshot');
            }
        }
    });


})(jQuery, Drupal, CKEDITOR);
