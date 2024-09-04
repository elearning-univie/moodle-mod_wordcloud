import ajax from "core/ajax";
import notification from "core/notification";
import ModalFactory from "core/modal_factory";
import ModalEvents from "core/modal_events";
import {get_string as getString} from 'core/str';

export const init = () => {
    document.mod_wordcloud_update_entry = function (aid, backurl) {
        let wordlist = document.querySelectorAll(".mod-wordcloud-edit-word");
        const changes = [];
        const doubleentries = [];
        const allentries = new Set();
        const collectedentries = new Set();

        for (let i = 0; i < wordlist.length; i++) {
            let worditem = wordlist[i];

            if (allentries.has(worditem.value)) {
                doubleentries.push(worditem.value);
            } else {
                allentries.add(worditem.value);
            }

            if (worditem.dataset.word != worditem.value) {
                changes.push({
                    wordid: worditem.dataset.id,
                    newword: worditem.value,
                    newcount: document.getElementById("mod-wordcloud-count" + worditem.dataset.id).value
                });
                collectedentries.add(worditem.dataset.id);
            }
        }

        wordlist = document.querySelectorAll(".mod-wordcloud-edit-count");

        for (let i = 0; i < wordlist.length; i++) {
            let worditem = wordlist[i];
            if (worditem.dataset.count != worditem.value) {
                if (!collectedentries.has(worditem.dataset.id)) {
                    changes.push({
                        wordid: worditem.dataset.id,
                        newword: document.getElementById("mod-wordcloud-word" + worditem.dataset.id).value,
                        newcount: worditem.value
                    });
                }
            }
        }

        if (doubleentries.length) {
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: getString('warning', 'mod_wordcloud'),
                body: confirmationMessage(doubleentries.toString()),
            }).then(function (modal) {
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    sendWsCall(aid, changes, backurl);
                });

                modal.getRoot().on(ModalEvents.cancel, () => {
                    location.reload();
                });

                modal.getRoot().on(ModalEvents.hidden, () => {
                    modal.destroy();
                });

                modal.show();
            });
        } else {
            sendWsCall(aid, changes, backurl);
        }
    };
};

const confirmationMessage = async(data) => {
    const confirmationWarning = await getString('warningdoubleentries', 'mod_wordcloud');
    return confirmationWarning + data;
};

const sendWsCall = (aid, changes, backurl) => {
    ajax.call([{
        methodname: 'mod_wordcloud_update_entry',
        args: {aid: aid, entry: changes},
        done: function (returnval) {
            if (!returnval.success) {
                ModalFactory.create({
                    type: ModalFactory.types.CANCEL,
                    title: getString('warning', 'mod_wordcloud'),
                    body: returnval.warnings[0].message,
                }).then(function (modal) {
                    modal.getRoot().on(ModalEvents.cancel, () => {
                        location.reload();
                    });
                    modal.show();
                });
            } else {
                location.href = backurl;
            }
        },
        fail: notification.exception
    }]);
};