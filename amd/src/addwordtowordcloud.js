import ajax from 'core/ajax';
import notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';

const addwordtowordcloud = (() => {
    // Private variables
    let aid, listview, timestamphtml, refreshtime;

    // Function to add a new word
    const addWord = () => {
        const newWord = document.getElementById('mod-wordcloud-new-word');
        const wordBox = document.getElementById('mod-wordcloud-words-box');
        const wordCount = document.getElementById('mod-wordcloud-wcount');
        const viewMenu = document.getElementById('mod-wordcloud-view-menu');

        const word = newWord.value.trim();
        if (!word) {return;}

        // AJAX call to add the word to the word cloud
        ajax.call([{
            methodname: 'mod_wordcloud_add_word',
            args: { aid, word, listview },
            done: (returnval) => {
                if (!returnval.cloudhtml) {
                    // Show warning modal if there's an error
                    ModalFactory.create({
                        type: ModalFactory.types.CANCEL,
                        title: getString('warning', 'mod_wordcloud'),
                        body: returnval.warnings[0].message
                    }).then(modal => modal.show());
                } else {
                    // Update word cloud
                    wordBox.innerHTML = returnval.cloudhtml;
                    wordCount.textContent = returnval.sumcount;
                    newWord.value = '';
                    viewMenu.disabled = false;
                }
            },
            fail: notification.exception
        }]);
    };

    // Function to auto-refresh the word cloud periodically
    const autoRefreshWords = () => {
        const wordBox = document.getElementById('mod-wordcloud-words-box');
        const wordCount = document.getElementById('mod-wordcloud-wcount');

        setInterval(() => {
            ajax.call([{
                methodname: 'mod_wordcloud_get_words',
                args: { aid, timestamphtml, listview },
                done: (returnval) => {
                    if (returnval.cloudhtml) {
                        wordBox.innerHTML = returnval.cloudhtml;
                        wordCount.textContent = returnval.sumcount;
                        timestamphtml = returnval.timestamphtml;
                    }
                },
                fail: notification.exception
            }]);
        }, refreshtime * 1000);
    };

    return {
        init: (refreshTime, aidParam, timestampHtmlParam, listviewParam) => {
            refreshtime = refreshTime;
            aid = aidParam;
            timestamphtml = timestampHtmlParam;
            listview = listviewParam;

            const wordInput = document.getElementById('mod-wordcloud-new-word');
            const addButton = document.getElementById('mod-wordcloud-btn');

            wordInput.addEventListener('keypress', (e) => {
                if (e.keyCode === 13) {
                    addButton.click();
                }
            });

            addButton.addEventListener('click', addWord);

            // Start auto-refresh of word cloud
            autoRefreshWords();
        }
    };
})();

export default addwordtowordcloud;