/**
 * @file
 * Drupal URL embed plugin.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  CKEDITOR.plugins.add('omcode', {
    // This plugin requires the Widgets System defined in the 'widget' plugin.
    requires: 'widget',

    // The plugin initialization logic goes inside this method.
    beforeInit: function (editor) {
      // Configure CKEditor DTD for custom drupal-url element.
      // @see https://www.drupal.org/node/2448449#comment-9717735
      var dtd = CKEDITOR.dtd, tagName;
      dtd['om-code'] = {'#': 1};
      // Register om-code element as allowed child, in each tag that can
      // contain a div element.
      for (tagName in dtd) {
        if (dtd[tagName].div) {
          dtd[tagName]['om-code'] = 1;
        }
      }

      // Generic command for adding/editing entities of all types.
      editor.addCommand('editomcode', {
        allowedContent: 'om-code[data-embed-code]',
        requiredContent: 'om-code[data-embed-type]',
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, data) {
          data = data || {};

          var existingElement = getSelectedEmbeddedOmCode(editor);

          var existingValues = {};
          if (existingElement && existingElement.$ && existingElement.$.firstChild) {
            var embedDOMElement = existingElement.$.firstChild;
            // Populate array with the embed item's current attributes.
            var attribute = null, attributeName;
            for (var key = 0; key < embedDOMElement.attributes.length; key++) {
              attribute = embedDOMElement.attributes.item(key);
              attributeName = attribute.nodeName.toLowerCase();
              if (attributeName.substring(0, 15) === 'data-cke-saved-') {
                continue;
              }
              if(attribute.nodeName == 'data-embed-code'){
                existingValues[attributeName] = b64DecodeUnicode(attribute.nodeValue);
              } else {
                existingValues[attributeName] = existingElement.data('cke-saved-' + attributeName) || attribute.nodeValue;
              }
            }
          }

          var embed_button_id = data.id ? data.id : existingValues['data-embed-button'];

          var dialogSettings = {
            title: existingElement ? 'Edit Embed Code' : 'Insert Embed Code',
            dialogClass: 'om-code-select-dialog',
            resizable: false,
            minWidth: 800
          };

          var saveCallback = function (values) {
            var urlElement = editor.document.createElement('om-code');
            var attributes = values.attributes
            for (var key in attributes) {
              if(key == 'data-embed-code'){
                urlElement.setAttribute(key, b64EncodeUnicode(attributes[key]));
              } else {
                urlElement.setAttribute(key, attributes[key]);
              }
            }
            editor.insertHtml(urlElement.getOuterHtml());
            if (existingElement) {
              // Detach the behaviors that were attached when the URL content
              // was inserted.
              Drupal.runEmbedBehaviors('detach', existingElement.$);
              existingElement.remove();
            }
          };

          // Open the URL embed dialog for corresponding EmbedButton.
          Drupal.ckeditor.openDialog(editor, Drupal.url('om-code-embed/dialog/' + editor.config.drupal.format + '/' + embed_button_id), existingValues, saveCallback, dialogSettings);
        }
      });

      // Register the URL embed widget.
      editor.widgets.add('omcode', {
        // Minimum HTML which is required by this widget to work.
        allowedContent: 'om-code[data-embed-code]',
        requiredContent: 'om-code[data-embed-code]',

        // Simply recognize the element as our own. The inner markup if fetched
        // and inserted the init() callback, since it requires the actual DOM
        // element.
        upcast: function (element) {
          var attributes = element.attributes;
          if (attributes['data-embed-code'] === undefined) {
            return;
          }
          // Generate an ID for the element, so that we can use the Ajax
          // framework.
          element.attributes.id = generateEmbedId();
          return element;
        },

        // Fetch the rendered item.
        init: function () {
          /** @type {CKEDITOR.dom.element} */
          var element = this.element;
          // Use the Ajax framework to fetch the HTML, so that we can retrieve
          // out-of-band assets (JS, CSS...).
          var omCodeEmbedPreview = Drupal.ajax({
            base: element.getId(),
            element: element.$,
            url: Drupal.url('embed/preview/' + editor.config.drupal.format + '?' + $.param({
              value: element.getOuterHtml()
            })),
            progress: {type: 'none'},
            // Use a custom event to trigger the call.
            event: 'om_code_embed_dummy_event'
          });
          omCodeEmbedPreview.execute();
        },

        // Downcast the element.
        downcast: function (element) {
          // Only keep the wrapping element.
          element.setHtml('');
          // Remove the auto-generated ID.
          delete element.attributes.id;
          return element;
        }
      });

      // Register the toolbar buttons.
      if (editor.ui.addButton) {
        for (var key in editor.config.OmCode_buttons) {
          var button = editor.config.OmCode_buttons[key];
          editor.ui.addButton(button.id, {
            label: button.label,
            data: button,
            click: function(editor) {
              editor.execCommand('editomcode', this.data);
            },
            icon: button.image
          });
        }
      }

      // Register context menu option for editing widget.
      /*
      if (editor.contextMenu) {
        editor.addMenuGroup('omcode');
        editor.addMenuItem('omcode', {
          label: Drupal.t('Edit embedded code'),
          command: 'editomcode',
          group: 'omcode'
        });

        editor.contextMenu.addListener(function(element) {
          if (isEmbeddedOmCodeWidget(editor, element)) {
            return { drupalurl: CKEDITOR.TRISTATE_OFF };
          }
        });
      }
      */

      // Execute widget editing action on double click.
      editor.on('doubleclick', function (evt) {
        var element = getSelectedEmbeddedOmCode(editor) || evt.data.element;

        if (isEmbeddedOmCodeWidget(editor, element)) {
          editor.execCommand('editomcode');
        }
      });
    }
  });

  /**
   * Get the surrounding drupalurl widget element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEmbeddedOmCode(editor) {
    var selection = editor.getSelection();
    var selectedElement = selection.getSelectedElement();
    if (isEmbeddedOmCodeWidget(editor, selectedElement)) {
      return selectedElement;
    }

    return null;
  }

  /**
   * Returns whether or not the given element is a drupalurl widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEmbeddedOmCodeWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    return widget && widget.name === 'omcode';
  }

  /**
   * Generates unique HTML IDs for the widgets.
   *
   * @returns {string}
   */
  function generateEmbedId() {
    if (typeof generateEmbedId.counter == 'undefined') {
      generateEmbedId.counter = 0;
    }
    return 'om-code-embed-' + generateEmbedId.counter++;
  }
  
  function b64EncodeUnicode(str) {
    // first we use encodeURIComponent to get percent-encoded UTF-8,
    // then we convert the percent encodings into raw bytes which
    // can be fed into btoa.
    return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
        function toSolidBytes(match, p1) {
            return String.fromCharCode('0x' + p1);
    }));
   }
  function b64DecodeUnicode(str) {
    // Going backwards: from bytestream, to percent-encoding, to original string.
    return decodeURIComponent(atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
  }

})(jQuery, Drupal, CKEDITOR);
