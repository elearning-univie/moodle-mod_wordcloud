import $ from "jquery";
import ajax from "core/ajax";
import notification from "core/notification";
import ModalFactory from "core/modal_factory";
import str from "core/str";

export const init = () => {
    $.mod_wordcloud_update_entry = function (aid, wordid) {
        var wcword = document.getElementById("mod-wordcloud-word" + wordid).value;
        var wccount = document.getElementById("mod-wordcloud-count" + wordid).value;

        ajax.call([{
            methodname: 'mod_wordcloud_update_entry',
            args: {aid: aid, wordid: wordid, newword: wcword, newcount: wccount},
            done: function (returnval) {
                if (!returnval.success) {
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: str.get_string('warning', 'mod_wordcloud'),
                        body: returnval.warnings[0].message,
                    }).then(function (modal) {
                        modal.show();
                    });
                } else {
                    var icon = document.getElementById("mod-wordcloud-fade-success" + wordid);
                    icon.classList.add("fade");
                    setTimeout(function() {
                        icon.classList.remove("fade");
                    }, 4000);
                }
            },
            fail: notification.exception
        }]);
    };
};