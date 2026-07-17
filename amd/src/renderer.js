import WordCloud from "mod_wordcloud/wordcloud";
import {initlistener, set_classic_css, get_new_css} from "mod_wordcloud/uicontroller";
import notification from 'core/notification';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';

const render_new_view = (entries) => {
    const container = document.getElementById('mod-wordcloud-words-box');
    const rendersettings = JSON.parse(wordcloud_style.settings);
    const colors = get_new_css();

    const baseHeight = 250;
    const wordThreshold = 20;
    const extraHeightPerWord = 15;

    let calculatedHeight = baseHeight;
    if (entries.length > wordThreshold) {
        calculatedHeight += (entries.length - wordThreshold) * extraHeightPerWord;
    }

    container.style.height = `${calculatedHeight}px`;
    if (entries.length > 50) {
        container.style.width = "100%";
    }

    let canvas = container.querySelector('#mod-wordcloud-words-canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'mod-wordcloud-words-canvas';
        container.appendChild(canvas);
    }

    const cs = getComputedStyle(container);
    const width = container.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight);
    const height = container.clientHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom);

    const dpr = window.devicePixelRatio || 1;
    canvas.width  = Math.max(1, Math.round(width * dpr));
    canvas.height = Math.max(1, Math.round(height * dpr));
    canvas.style.width  = `${width}px`;
    canvas.style.height = `${height}px`;

    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0); // Corrected scale handling
    ctx.clearRect(0, 0, width, height);

    const rotation = get_rotation_settings(rendersettings.alignmentmode);

    const weights = entries.map(e => e[1]);
    const maxWeight = Math.max(...weights) || 1;
    const minWeight = Math.min(...weights) || 0;
    const weightRange = maxWeight - minWeight || 1;
    const targetMaxFontSize = width * 0.25;
    const internalMaxFontSize = targetMaxFontSize * dpr;

    const safeMinFontSize = 12 * dpr;

    const baseConfig = {
        list: entries,
        weightFactor: function(weight) {
            if (maxWeight === 0) {
                return safeMinFontSize;
            }

            const calculatedSize = (weight / maxWeight) * internalMaxFontSize;

            return Math.max(safeMinFontSize, calculatedSize);
        },
        color: function(_, weight) {
            if (!colors || colors.length === 0) {
                return '#000000';
            }

            const normalizedWeight = (weight - minWeight) / weightRange;

            let colorIndex = Math.floor(normalizedWeight * colors.length);
            colorIndex = Math.min(colorIndex, colors.length - 1);

            return colors[colorIndex];
        },
        ...rotation,
    };

    const cleanRenderSettings = { ...rendersettings };
    delete cleanRenderSettings.weightFactor;

    WordCloud(canvas, {
        ...cleanRenderSettings,
        ...baseConfig
    });
};

const render_classic_view = (entries, wordcountrange) => {
    const steps = 6;
    const mincount = Number(wordcountrange.mincount);
    const maxcount = Number(wordcountrange.maxcount);
    const range = (maxcount - mincount) * 1.0001;

    let cloudhtml = '';

    entries.forEach(entry => {
        const [word, count] = entry;
        let weight;

        if (range >= 3) {
            weight = 1 + Math.floor(steps * (count - mincount) / range);
        } else {
            weight = 1;
        }

        const fontsize = 'w' + weight;
        cloudhtml += `<span class="word center ${fontsize}" title="${count}">${word}</span>`;
    });

    set_classic_css();

    const wordBox = document.getElementById('mod-wordcloud-words-box');
    wordBox.classList.add('d-flex');
    wordBox.innerHTML = cloudhtml;
};

// State variables to track sorting
let currentSort = 'count'; // Default sort column
let sortAscending = false;

const render_list_view = (entries) => {
    const wordBox = document.getElementById('mod-wordcloud-words-box');

    const sortedEntries = [...entries].sort((a, b) => {
        if (currentSort === 'word') {
            const comparison = a[0].localeCompare(b[0], undefined, { sensitivity: 'base' });
            return sortAscending ? comparison : -comparison;
        } else {
            // Numeric sort: subtracting forced numbers handles asc/desc cleanly
            return sortAscending ? a[1] - b[1] : b[1] - a[1];
        }
    });

    const context = {
        words: sortedEntries.map(([word, count]) => ({
            word: word,
            count: count
        })),
        sort_word_asc: currentSort === 'word' && sortAscending,
        sort_word_desc: currentSort === 'word' && !sortAscending,
        sort_count_asc: currentSort === 'count' && sortAscending,
        sort_count_desc: currentSort === 'count' && !sortAscending
    };

    Templates.render('mod_wordcloud/wordlist', context)
        .then((html, js) => {
            wordBox.innerHTML = html;
            Templates.runTemplateJS(js);

            attachSortListeners(entries);

            // Listen for the link-style reset click
            const resetBtn = document.getElementById('wordcloud-reset-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentSort = 'count';
                    sortAscending = false;

                    render_list_view(entries);
                });
            }

        }).catch(notification.exception);
};

const attachSortListeners = (entries) => {
    const headers = document.querySelectorAll('#wordcloud-table .sortable-header');

    headers.forEach(header => {
        header.addEventListener('click', (e) => {
            const clickedSort = e.currentTarget.getAttribute('data-sort');

            if (currentSort === clickedSort) {
                sortAscending = !sortAscending;
            } else {
                currentSort = clickedSort;
                sortAscending = true;
            }

            render_list_view(entries);
        });
    });
};

const get_rotation_settings = (mode) => {
    const HALF_PI = Math.PI / 2;

    switch (mode) {
        case 'h':
            // Only 0 degrees
            return { minRotation: 0, maxRotation: 0, rotationSteps: 1, rotateRatio: 0 };

        case 'v':
            // Options: -90, 90
            return {
                minRotation: -HALF_PI,
                maxRotation: HALF_PI,
                rotationSteps: 2,
                rotateRatio: 1
            };

        case 'hv':
            // Options: -90, 0, 90
            // With 3 steps: 1st is -90, 2nd is 0, 3rd is 90
            return {
                minRotation: -HALF_PI,
                maxRotation: HALF_PI,
                rotationSteps: 3,
                rotateRatio: 0.66
            };

        case 'hvd':
            // Options: -90, -45, 0, 45, 90
            // With 5 steps: -90, -45, 0, 45, 90
            return {
                minRotation: -HALF_PI,
                maxRotation: HALF_PI,
                rotationSteps: 5,
                rotateRatio: 0.8
            };

        default:
            return { minRotation: 0, maxRotation: 0, rotationSteps: 1, rotateRatio: 0 };
    }
};

export const wordcloud_style = {
    version: 0,
    settings: ''
};

export const render_wordcloud = async (jsonentries, wordcountrange) => {
    const view = Number(wordcloud_style.version) || 0;
    const entries = JSON.parse(jsonentries) || [];
    const container = document.getElementById('mod-wordcloud-words-box');

    if (entries.length === 0) {
        document.getElementById('mod-wordcloud-view-menu').disabled = true;
        const message = await getString('nothingtodisplay');

        container.innerHTML = '<div class="alert alert-info alert-block" role="alert">' + message + '</div>';
        return;
    } else {
        container.innerHTML = '';
    }

    switch (view) {
        case 1:
            render_new_view(entries);
            break;
        case 2:
            render_list_view(entries);
            break;
        default: {
            const wcr = JSON.parse(wordcountrange);
            render_classic_view(entries, wcr);
            break;
        }
    }
};

export const init = (aid, viewstyle, rendersettings) => {
    wordcloud_style.version = viewstyle;
    wordcloud_style.settings = rendersettings;

    const wordCount = document.getElementById('mod-wordcloud-wcount');

    ajax.call([{
        methodname: 'mod_wordcloud_get_entries',
        args: { aid, timestamphtml: -1 },
        done: (returnval) => {
            if (returnval.entries) {
                wordCount.textContent = returnval.sumcount;
                render_wordcloud(returnval.entries, returnval.wordcountrange);
            }
        },
        fail: notification.exception
    }]);

    initlistener();

    var element = document.getElementById('mod-wordcloud-div');
    element.style.visibility = "visible";
};