(function ($, Drupal) {
    var sortableInit = function () {

        var sortableList = $('#managed-topics-tree');
        sortableList.nestable({
            maxDepth: drupalSettings.om_sortable.maxDepth
        });

        var serializerList = JSON.stringify(sortableList.nestable('serialize'));
        $("input[name='managed_topics']").val(serializerList);

        sortableList.on('change', function () {
            var sortableList = $('#managed-topics-tree');
            var serializerList = JSON.stringify(sortableList.nestable('serialize'));
            $("input[name='managed_topics']").val(serializerList);
        });

        $('.remove-item').on('click', function () {
            var closest_li = $(this).closest('li');
            closest_li.remove();
            var sortableList = $('#managed-topics-tree');
            var serializerList = JSON.stringify(sortableList.nestable('serialize'));
            $("input[name='managed_topics']").val(serializerList);
        });
    };

    sortableInit();

    $(document).ajaxComplete(function (event, xhr, settings) {
        if (typeof settings.extraData != 'undefined' && typeof settings.extraData._triggering_element_name != 'undefined') {
            switch (settings.extraData._triggering_element_name) {
                case "search_topic":
                    sortableInit();
                    break;
                default:
                    break;
            }
        }
    });


    $(document).on("keypress", "form", function (event) {
        if (event.keyCode === 13) {
            if (event.target !== undefined) {
                var target = $(event.target);
                if (target.attr('name') === 'search_topic') {
                    target.trigger('om_topic_select');
                }
            }
        }

        return event.keyCode !== 13;
    });

})(jQuery, Drupal);
