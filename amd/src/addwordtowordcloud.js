define(['jquery', 'core/ajax', 'core/notification'], function ($, ajax, notification) {
    return {
        init: function () {
            $.mod_wordcloud_add_word = function (aid) {
                var word = $('#answer').val();
                ajax.call([{
                    methodname: 'mod_wordcloud_add_word',
                    args: {aid: aid, word: word},
                    done: function ($return) {
                        //window.location = url.relativeUrl('/mod/flashcards/studentview.php?id=' + cmid);
                        window.console.log($return);
                    },
                    fail: notification.exception
                }]);
            };
        }
    };
});