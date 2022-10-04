define(['jquery', 'core/ajax', 'core/notification', 'core/modal_factory', 'core/str'],
    function ($, ajax, notification, ModalFactory, str) {
        return {
            init: function (refreshtime, aid, timestamphtml) {
                $('#mod-wordcloud-new-word').keypress(function (e) {
                    // filter enter key to auto commit the word
                    if (e.keyCode === 13) {
                        $('#mod-wordcloud-btn').click();
                    }
                });
                $.mod_wordcloud_add_word = function () {
                    var word = $('#mod-wordcloud-new-word').val();

                    if (!word.trim()) {
                        return;
                    }

                    ajax.call([{
                        methodname: 'mod_wordcloud_add_word',
                        args: {aid: aid, word: word},
                        done: function (returnval) {
                            if (!returnval.cloudhtml) {
                                ModalFactory.create({
                                    type: ModalFactory.types.CANCEL,
                                    title: str.get_string('warning', 'mod_wordcloud'),
                                    body: returnval.warnings[0].message,
                                }).then(function (modal) {
                                    modal.show();
                                });
                            } else {
                                $('#mod-wordcloud-words-box').html(returnval.cloudhtml);
                                $('#mod-wordcloud-new-word').val('');

                                var wb = document.getElementById('mod-wordcloud-div');
                                var divheight = wb.offsetHeight;
                                var newwidth = 0;
                                if (divheight < 300) {
                                    newwidth = 60;
                                } else if (divheight < 500) {
                                    newwidth = 70;
                                } else if (divheight < 700) {
                                    newwidth = 80;
                                } else {
                                    newwidth = 100;
                                }
                                if (newwidth > parseInt(wb.style.width) && window.innerWidth > 900) {
                                    wb.style.width = newwidth + "%";
                                }
                            }
                        },
                        fail: notification.exception
                    }]);
                };
                setInterval(function(){
                    ajax.call([{
                        methodname: 'mod_wordcloud_get_words',
                        args: {aid: aid, timestamphtml: timestamphtml},
                        done: function (returnval) {
                            if (returnval.cloudhtml) {
                                $('#mod-wordcloud-words-box').html(returnval.cloudhtml);
                                timestamphtml = returnval.timestamphtml;
                                var wb = document.getElementById('mod-wordcloud-div');
                                var divheight = wb.offsetHeight;
                                var newwidth = 0;
                                if (divheight < 400) {
                                    newwidth = 60;
                                } else if (divheight < 600) {
                                    newwidth = 70;
                                } else if (divheight < 800) {
                                    newwidth = 80;
                                } else {
                                    newwidth = 100;
                                }
                                if (newwidth > parseInt(wb.style.width) && window.innerWidth > 900) {
                                    wb.style.width = newwidth + "%";
                                }
                            }
                        },
                        fail: notification.exception
                    }]);
                },refreshtime*1000);
            }
        };
    });