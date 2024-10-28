import ajax from "core/ajax";
import notification from "core/notification";
import ModalCancel from 'core/modal_cancel';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from "core/modal_events";
import {get_string as getString} from 'core/str';

export const modWordcloudUpdateEntry = async (aid, backurl) => {
    let wordlist = document.querySelectorAll(".mod-wordcloud-edit-word");
    const changes = [];
    const doubleentries = [];
    const allentries = new Set();
    const collectedentries = new Set();

    wordlist.forEach(worditem => {
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
    });

    wordlist = document.querySelectorAll(".mod-wordcloud-edit-count");
    wordlist.forEach(worditem => {
        if (worditem.dataset.count != worditem.value && !collectedentries.has(worditem.dataset.id)) {
            changes.push({
                wordid: worditem.dataset.id,
                newword: document.getElementById("mod-wordcloud-word" + worditem.dataset.id).value,
                newcount: worditem.value
            });
        }
    });

    if (doubleentries.length) {
        const modal = await ModalSaveCancel.create({
            title: getString('warning', 'mod_wordcloud'),
            body: confirmationMessage(doubleentries.toString())
        });
        modal.getRoot().on(ModalEvents.save, e => {
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
    } else {
        sendWsCall(aid, changes, backurl);
    }
};

const confirmationMessage = async (data) => {
    const confirmationWarning = await getString('warningdoubleentries', 'mod_wordcloud');
    return confirmationWarning + data;
};

const sendWsCall = (aid, changes, backurl) => {
    ajax.call([{
        methodname: 'mod_wordcloud_update_entry',
        args: {aid: aid, entry: changes},
        done: async function (returnval) {
            if (!returnval.success) {
                const modal = await ModalCancel.create({
                    title: getString('warning', 'mod_wordcloud'),
                    body: returnval.warnings[0].message,
                });
                modal.getRoot().on(ModalEvents.cancel, () => {
                    location.reload();
                });
                modal.show();
            } else {
                location.href = backurl;
            }
        },
        fail: notification.exception
    }]);
};

export const init = () => {
    const updateButton = document.getElementById('mod-wordcloud-update-btn');

    if (updateButton) {
        updateButton.addEventListener('click', (e) => {
            const aid = e.target.dataset.aid;
            const backurl = e.target.dataset.backurl;

            // Call the function directly on button click
            modWordcloudUpdateEntry(aid, backurl);
        });
    }
};
