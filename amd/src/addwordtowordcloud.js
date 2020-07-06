define(['jquery', 'core/ajax', 'core/notification'], function ($, ajax, notification) {
    return {
        init: function () {
            $.mod_wordcloud_add_word = function (aid) {
                var word = $('#mod-wordcloud-new-word').val();
                ajax.call([{
                    methodname: 'mod_wordcloud_add_word',
                    args: {aid: aid, word: word},
                    done: function ($return) {
                        $('#mod-wordcloud-words-box').html($return);
                    },
                    fail: notification.exception
                }]);
            };
        }
    };
});