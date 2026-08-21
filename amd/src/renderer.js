import wordCloud from "mod_wordcloud/wordcloud";
import {initlistener, setClassicCss, getNewCss} from "mod_wordcloud/uicontroller";
import notification from 'core/notification';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';
import ajax from 'core/ajax';

/**
 * Lazily create (or return the existing) tooltip element used to display the
 * word count when hovering over a word in the canvas-based word cloud.
 *
 * @return {HTMLElement} the tooltip element.
 */
const getWordTooltip = () => {
    let tooltip = document.getElementById('mod-wordcloud-tooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'mod-wordcloud-tooltip';
        tooltip.className = 'mod-wordcloud-tooltip';
        tooltip.setAttribute('role', 'tooltip');
        tooltip.style.display = 'none';
        document.body.appendChild(tooltip);
    }
    return tooltip;
};

/**
 * Hide the word count tooltip.
 */
const hideWordTooltip = () => {
    getWordTooltip().style.display = 'none';
};

/**
 * Word -> original submitted count.
 *
 * @type {Map<string, number>}
 */
let currentWordCounts = new Map();

/**
 * wordcloud2 "hover" callback: shows a tooltip with how often the hovered
 * word was submitted, positioned next to the mouse cursor.
 *
 * @param {Array|undefined} item [word, weight] pair of the hovered word, or undefined when no word is hovered.
 * @param {Object|undefined} dimension bounding box of the hovered word (unused).
 * @param {MouseEvent} event the triggering mouse event.
 */
const onWordHover = (item, dimension, event) => {
    if (!item) {
        hideWordTooltip();
        return;
    }

    const [word] = item;
    const count = currentWordCounts.get(word);
    const tooltip = getWordTooltip();
    tooltip.textContent = `${count}`;
    tooltip.style.left = `${event.pageX + 12}px`;
    tooltip.style.top = `${event.pageY + 12}px`;
    tooltip.style.display = 'block';
};

/**
 * Renders the canvas-based ("modern") wordcloud view into the given container.
 *
 * Used both for the real activity page (with its defaults) and for the
 * appearance preview shown from mod_form.php, which supplies its own
 * container, rendersettings and colour palette.
 *
 * @param {Array} entries [word, count] pairs to render.
 * @param {HTMLElement} [container] element to render into. Defaults to the real activity page's word box.
 * @param {Object} [rendersettings] wordcloud2 render settings, including "alignmentmode". Defaults to the
 *                                  settings configured via {@link init}.
 * @param {string[]} [colors] colour palette to shade words with. Defaults to the colours set via
 *                             mod_wordcloud/uicontroller.
 * @param {string} [canvasId] id to give the (created if missing) canvas element.
 */
const renderNewView = (
    entries,
    container = document.getElementById('mod-wordcloud-words-box'),
    rendersettings = JSON.parse(wordcloudStyle.settings),
    colors = getNewCss(),
    canvasId = 'mod-wordcloud-words-canvas'
) => {
    // Snapshot the true counts before wordcloud2 gets a chance to mutate any
    // item's weight while trying to fit it on the canvas (see currentWordCounts).
    currentWordCounts = new Map(entries.map(([word, count]) => [word, count]));

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

    let canvas = container.querySelector('#' + canvasId);
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = canvasId;
        container.appendChild(canvas);
    }

    if (!canvas.dataset.tooltipBound) {
        canvas.addEventListener('mouseleave', hideWordTooltip);
        canvas.dataset.tooltipBound = '1';
    }

    const cs = getComputedStyle(container);
    const width = container.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight);
    const height = container.clientHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom);

    const dpr = window.devicePixelRatio || 1;
    canvas.width = Math.max(1, Math.round(width * dpr));
    canvas.height = Math.max(1, Math.round(height * dpr));
    canvas.style.width = `${width}px`;
    canvas.style.height = `${height}px`;

    const ctx = canvas.getContext('2d');
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const rotation = getRotationSettings(rendersettings.alignmentmode);

    const weights = entries.map(e => e[1]);
    const maxWeight = Math.max(...weights) || 1;
    const minWeight = Math.min(...weights) || 0;
    const weightRange = maxWeight - minWeight || 1;
    const targetMaxFontSize = Math.min(width, height) * 0.25;
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
        hover: onWordHover,
        ...rotation,
    };

    const cleanRenderSettings = {...rendersettings};
    delete cleanRenderSettings.weightFactor;

    wordCloud(canvas, {
        ...cleanRenderSettings,
        ...baseConfig
    });
};

/**
 * Renders the CSS-based ("classic") wordcloud view into the given container.
 *
 * Used both for the real activity page (with its defaults) and for the
 * appearance preview shown from mod_form.php. Since the font-size and colour
 * classes this relies on (.w1-.w6) are scoped to ".path-mod-wordcloud", the
 * container must have that class somewhere in its ancestry for styling to apply.
 *
 * @param {Array} entries [word, count] pairs to render.
 * @param {Object} wordcountrange {mincount, maxcount} across all entries.
 * @param {HTMLElement} [container] element to render into. Defaults to the real activity page's word box.
 */
const renderClassicView = (entries, wordcountrange, container = document.getElementById('mod-wordcloud-words-box')) => {
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

    setClassicCss();

    // The real activity page's container already has "flex-wrap gap-2" baked
    // into its markup (see wordcloud.mustache); adding them here too makes
    // this self-contained for any container, e.g. the mod_form.php preview.
    container.classList.add('d-flex', 'flex-wrap', 'gap-2');
    container.innerHTML = cloudhtml;
};

// State variables to track sorting
let currentSort = 'count'; // Default sort column
let sortAscending = false;

const renderListView = (entries) => {
    const wordBox = document.getElementById('mod-wordcloud-words-box');

    const sortedEntries = [...entries].sort((a, b) => {
        if (currentSort === 'word') {
            const comparison = a[0].localeCompare(b[0], undefined, {sensitivity: 'base'});
            return sortAscending ? comparison : -comparison;
        } else {
            // Numeric sort: subtracting forced numbers handles asc/desc cleanly.
            return sortAscending ? a[1] - b[1] : b[1] - a[1];
        }
    });

    const context = {
        words: sortedEntries.map(([word, count]) => ({
            word: word,
            count: count
        })),
        sortWordAsc: currentSort === 'word' && sortAscending,
        sortWordDesc: currentSort === 'word' && !sortAscending,
        sortCountAsc: currentSort === 'count' && sortAscending,
        sortCountDesc: currentSort === 'count' && !sortAscending
    };

    Templates.render('mod_wordcloud/wordlist', context)
        .then((html, js) => {
            wordBox.innerHTML = html;
            Templates.runTemplateJS(js);

            attachSortListeners(entries);

            // Listen for the link-style reset click.
            const resetBtn = document.getElementById('wordcloud-reset-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentSort = 'count';
                    sortAscending = false;

                    renderListView(entries);
                });
            }

            return html;
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

            renderListView(entries);
        });
    });
};

const getRotationSettings = (mode) => {
    const HALF_PI = Math.PI / 2;

    switch (mode) {
        case 'h':
            // Only 0 degrees.
            return {minRotation: 0, maxRotation: 0, rotationSteps: 1, rotateRatio: 0};

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
            return {minRotation: 0, maxRotation: 0, rotationSteps: 1, rotateRatio: 0};
    }
};

export {renderNewView, renderClassicView};

export const wordcloudStyle = {
    version: 0,
    settings: ''
};

export const renderWordcloud = async(jsonentries, wordcountrange) => {
    const view = Number(wordcloudStyle.version) || 0;
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
            renderNewView(entries);
            break;
        case 2:
            renderListView(entries);
            break;
        default: {
            const wcr = JSON.parse(wordcountrange);
            renderClassicView(entries, wcr);
            break;
        }
    }
};

export const init = (aid, viewstyle, rendersettings) => {
    wordcloudStyle.version = viewstyle;
    wordcloudStyle.settings = rendersettings;

    const wordCount = document.getElementById('mod-wordcloud-wcount');

    ajax.call([{
        methodname: 'mod_wordcloud_get_entries',
        args: {aid, timestamphtml: -1},
        done: (returnval) => {
            if (returnval.entries) {
                wordCount.textContent = returnval.sumcount;
                renderWordcloud(returnval.entries, returnval.wordcountrange);
            }
        },
        fail: notification.exception
    }]);

    initlistener();

    var element = document.getElementById('mod-wordcloud-div');
    element.style.visibility = "visible";
};