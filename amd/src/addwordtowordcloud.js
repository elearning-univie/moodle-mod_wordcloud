define(['jquery', 'core/ajax', 'core/notification', 'core/modal_factory', 'core/str'],
    function ($, ajax, notification, ModalFactory, str) {
        return {
            init: function () {
                $('#mod-wordcloud-new-word').keypress(function (e) {
                    if (e.keyCode === 13) {
                        $('#mod-wordcloud-btn').click();
                    }
                });
                $.mod_wordcloud_add_word = function (aid) {
                    var word = $('#mod-wordcloud-new-word').val();
                    ajax.call([{
                        methodname: 'mod_wordcloud_add_word',
                        args: {aid: aid, word: word},
                        done: function ($return) {
                            if (!$return.cloudhtml) {
                                ModalFactory.create({
                                    type: ModalFactory.types.CANCEL,
                                    title: str.get_string('warning', 'mod_wordcloud'),
                                    body: $return.warnings[0].message,
                                }).then(function (modal) {
                                    modal.show();
                                });
                            } else {
                                $('#mod-wordcloud-words-box').html($return.cloudhtml);
                                $('#mod-wordcloud-new-word').val('');
                            }
                        },
                        fail: notification.exception
                    }]);
                };
            }
        };
    });