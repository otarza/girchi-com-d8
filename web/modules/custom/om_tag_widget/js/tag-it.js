(function ($, Drupal) {
    Drupal.behaviors.om_tag_widget_init = {
        attach: function(context, settings){
            $(".field--widget-entity-reference-om-tag-widget .form-autocomplete", context)
                .once("om_tag_widget_init").each(function(){
                var $this = $(this);
                $this.tagit({
                    allowSpaces: true,
                    tagSource: function (request, response) {
                        $.ajax({
                            url: $this.data("autocomplete-path"),
                            data: {q: request.term},
                            dataType: "json",
                            success: function (data) {
                                response($.map(data, function (item) {
                                    return {
                                        label: item.value,
                                        value: item.value
                                    }
                                }));
                            }
                        })
                    }
                });

            });
        }
    }
})(jQuery, Drupal);
