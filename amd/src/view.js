

define(['jquery', 'core/str', 'core/log'], function($, str, log) {

    var magtestview = {

        strs: [],

        init: function() {
            // Confirm delete action.
            $('.questioncommands #delete').bind('click', this.questiondelete);

            // Confirm delete action.
            $('.categorycommands #delete').bind('click', this.categorydelete);

            // Call strings.
            var stringdefs = [
                {key: 'questiondeleteconfirm', component: 'magtest'}, // 0
                {key: 'categorydeleteconfirm', component: 'magtest'}, // 0
            ];

            str.get_strings(stringdefs).done(function(s) {
                magtestview.strs = s;
            });

            log.debug('AMD Magtest view initialized');
        },

        questiondelete: function(e) {
            e.preventDefault();
            var result = confirm(magtestview.strs[0]);
            if (result) {
                window.location = $(this).attr('href');
            }
        },

        categorydelete: function(e) {
            e.preventDefault();
            var result = confirm(magtestview.strs[1]);
            if (result) {
                window.location = $(this).attr('href');
            }
        }
    };

    return magtestview;
});