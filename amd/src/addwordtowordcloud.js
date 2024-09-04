import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import ModalFactory from "core/modal_factory";
import {get_string as getString} from 'core/str';

export const init = (refreshtime, aid, timestamphtml, listview) => {
    $('#mod-wordcloud-new-word').keypress(function (e) {
        // filter enter key to auto commit the word
        if (e.keyCode === 13) {
            $('#mod-wordcloud-btn').click();
        }
    });
    document.mod_wordcloud_add_word = function () {
        var word = $('#mod-wordcloud-new-word').val();

        if (!word.trim()) {
            return;
        }

        ajax.call([{
            methodname: 'mod_wordcloud_add_word',
            args: {aid: aid, word: word, listview: listview},
            done: function (returnval) {
                if (!returnval.cloudhtml) {
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: getString('warning', 'mod_wordcloud'),
                        body: returnval.warnings[0].message,
                    }).then(function (modal) {
                        modal.show();
                    });
                } else {
                    $('#mod-wordcloud-words-box').html(returnval.cloudhtml);
                    $('#mod-wordcloud-wcount').text(returnval.sumcount);
                    $('#mod-wordcloud-new-word').val('');
                    $('#mod-wordcloud-view-menu').prop('disabled', false);
                }
            },
            fail: notification.exception
        }]);
    };
    setInterval(function(){
        ajax.call([{
            methodname: 'mod_wordcloud_get_words',
            args: {aid: aid, timestamphtml: timestamphtml, listview: listview},
            done: function (returnval) {
                if (returnval.cloudhtml) {
                    $('#mod-wordcloud-words-box').html(returnval.cloudhtml);
                    $('#mod-wordcloud-wcount').text(returnval.sumcount);
                    timestamphtml = returnval.timestamphtml;
                }
            },
            fail: notification.exception
        }]);
    },refreshtime*1000);
};