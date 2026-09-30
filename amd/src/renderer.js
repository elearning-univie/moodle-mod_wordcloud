import wordCloud from "mod_wordcloud/wordcloud";
import {initlistener, setClassicCss, getNewCss, updateCloudWidth} from "mod_wordcloud/uicontroller";
import notification from 'core/notification';
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
 * Layout tuning for the canvas ("modern") renderer. Sizes are in CSS pixels.
 *
 * @type {Object}
 */
const CLOUD_LAYOUT = {
    minHeight: 250,
    maxHeight: 2000,
    // The canvas grows until the least submitted word reaches this size.
    minReadableFont: 14,
    // Words shrunk below this are dropped, which triggers a redraw.
    minDrawFont: 8,
    // Size of the least submitted word relative to the most submitted one.
    smallestRatio: 0.3,
    // Largest word relative to the canvas' shorter side.
    maxFontRatio: 0.3,
    // Share of the canvas the words may cover; higher is more compact but redraws more often.
    fillRatio: 0.6,
    fillRatioRotated: 0.5,
    lineHeight: 1.2,
    // Height increase per redraw, and the most draws per render.
    growFactor: 1.35,
    maxAttempts: 4,
};

/** @type {WeakMap<HTMLElement, number>} Stops an outdated render's redraw from overwriting a newer one. */
const renderGenerations = new WeakMap();

/**
 * Measures how wide each word is per pixel of font size.
 *
 * @param {string[]} words the words to measure.
 * @param {string} fontFamily CSS font family the words are drawn in.
 * @param {string} [fontWeight] CSS font weight the words are drawn in.
 * @return {number[]} the width of each word at a font size of 1px.
 */
const measureTextWidths = (words, fontFamily, fontWeight = 'normal') => {
    const ctx = document.createElement('canvas').getContext('2d');
    ctx.font = `${fontWeight} 100px ${fontFamily}`;

    return words.map(word => ctx.measureText(word).width / 100);
};

/**
 * Returns the vertical padding and border of a border-box element.
 *
 * @param {HTMLElement} element the element.
 * @return {number} padding plus border, in pixels.
 */
const getVerticalChrome = (element) => {
    const cs = getComputedStyle(element);
    if (cs.boxSizing !== 'border-box') {
        return 0;
    }
    return parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom) +
        parseFloat(cs.borderTopWidth) + parseFloat(cs.borderBottomWidth);
};

/**
 * Returns the content box size of an element (inside its padding).
 *
 * @param {HTMLElement} element the element.
 * @return {{width: number, height: number}} the content size in pixels.
 */
const getContentSize = (element) => {
    const cs = getComputedStyle(element);
    return {
        width: Math.max(1, element.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight)),
        height: Math.max(1, element.clientHeight - parseFloat(cs.paddingTop) - parseFloat(cs.paddingBottom)),
    };
};

/**
 * Renders the canvas-based ("modern") wordcloud view, on the activity page or the mod_form.php preview.
 *
 * Font sizes are computed from the measured words so that all of them fit; if
 * wordcloud2 still drops or shrinks one, the canvas is made taller and redrawn.
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
    // Counts arrive as strings.
    const words = entries.map(([word, count]) => [word, Number(count)]);

    // Kept aside, as wordcloud2 changes the list items it draws.
    currentWordCounts = new Map(words);
    const countsByWord = new Map(words);

    // Words are placed in list order, so the biggest go first.
    words.sort((a, b) => b[1] - a[1]);

    const counts = words.map(([, count]) => count);
    const maxCount = counts[0];
    const minCount = counts[counts.length - 1];
    const countRange = maxCount - minCount;

    const relativeSizes = counts.map(count => {
        if (countRange === 0) {
            return 1;
        }
        return CLOUD_LAYOUT.smallestRatio + (1 - CLOUD_LAYOUT.smallestRatio) * (count - minCount) / countRange;
    });
    const smallestSize = Math.min(...relativeSizes);
    const largestSize = Math.max(...relativeSizes);

    const textWidths = measureTextWidths(
        words.map(([word]) => word),
        rendersettings.fontFamily || 'sans-serif',
        rendersettings.fontWeight
    );

    const rotation = getRotationSettings(rendersettings.alignmentmode);
    const rotates = rotation.rotateRatio > 0;
    // In mixed modes shrinkToFit re-rolls the rotation, so only "vertical only" forces words upright.
    const alwaysRotated = rotation.rotateRatio >= 1;
    const fillRatio = rotates ? CLOUD_LAYOUT.fillRatioRotated : CLOUD_LAYOUT.fillRatio;

    // Font size = scale * relative size; area and longest word per unit of scale.
    const areaPerScale = relativeSizes.reduce(
        (sum, size, i) => sum + textWidths[i] * size * size * CLOUD_LAYOUT.lineHeight,
        0
    ) || 1;
    const longestPerScale = Math.max(...relativeSizes.map((size, i) => textWidths[i] * size)) || 1;

    // Canvas height at which the least submitted word is readable.
    const heightFor = (width) => {
        const scale = CLOUD_LAYOUT.minReadableFont / smallestSize;
        let needed = scale * scale * areaPerScale / (fillRatio * width);
        if (alwaysRotated) {
            needed = Math.max(needed, scale * longestPerScale / 0.9);
        }
        return Math.min(CLOUD_LAYOUT.maxHeight, Math.max(CLOUD_LAYOUT.minHeight, Math.ceil(needed)));
    };

    const scaleFor = (width, height) => {
        const byArea = Math.sqrt(fillRatio * width * height / areaPerScale);
        const byLongestWord = 0.9 * (alwaysRotated ? height : width) / longestPerScale;
        const byMaxFont = CLOUD_LAYOUT.maxFontRatio * Math.min(width, height) / largestSize;
        return Math.min(byArea, byLongestWord, byMaxFont);
    };

    const dpr = window.devicePixelRatio || 1;
    const generation = (renderGenerations.get(container) || 0) + 1;
    renderGenerations.set(container, generation);

    const cleanRenderSettings = {...rendersettings};
    delete cleanRenderSettings.weightFactor;
    delete cleanRenderSettings.minSize;

    // Fixed on the first draw, so redraws only add room.
    let scale = null;

    const draw = (attempt, minimumHeight) => {
        const chrome = getVerticalChrome(container);
        const applyContentHeight = (contentHeight) => {
            container.style.height = `${Math.round(contentHeight + chrome)}px`;
        };

        applyContentHeight(Math.max(heightFor(getContentSize(container).width), minimumHeight));

        // Widen the activity page's cloud before sizing the canvas; it won't follow later.
        if (container.closest('#mod-wordcloud-div')) {
            updateCloudWidth();
            applyContentHeight(Math.max(heightFor(getContentSize(container).width), minimumHeight));
        }

        const {width, height} = getContentSize(container);

        if (scale === null) {
            scale = scaleFor(width, height);
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

        canvas.style.display = 'block';
        canvas.width = Math.max(1, Math.round(width * dpr));
        canvas.height = Math.max(1, Math.round(height * dpr));
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;

        // Weights are final font sizes, so shrinkToFit's "weight * 3/4" retries really shrink the word.
        const fontSizes = relativeSizes.map(size => size * scale * dpr);
        const list = words.map(([word], i) => [word, fontSizes[i]]);
        const smallestFont = Math.min(...fontSizes);

        let dropped = 0;

        const onDrawn = (e) => {
            if (!e.detail.drawn) {
                dropped++;
            }
        };

        const onStop = () => {
            canvas.removeEventListener('wordclouddrawn', onDrawn);
            canvas.removeEventListener('wordcloudstop', onStop);

            if (renderGenerations.get(container) !== generation || !canvas.isConnected) {
                return;
            }

            const shrunk = list.some(([, size], i) => size < fontSizes[i] * 0.99);
            const canGrow = height < CLOUD_LAYOUT.maxHeight && attempt + 1 < CLOUD_LAYOUT.maxAttempts;

            if ((dropped > 0 || shrunk) && canGrow) {
                draw(attempt + 1, Math.min(height * CLOUD_LAYOUT.growFactor, CLOUD_LAYOUT.maxHeight));
            }
        };

        canvas.addEventListener('wordclouddrawn', onDrawn);
        canvas.addEventListener('wordcloudstop', onStop);

        wordCloud(canvas, {
            ...cleanRenderSettings,
            ...rotation,
            list,
            // Fit the placement spiral to the canvas' shape, unless configured.
            ellipticity: rendersettings.ellipticity !== undefined ? rendersettings.ellipticity : height / width,
            weightFactor: (size) => size,
            minSize: Math.max(
                Number(rendersettings.minSize) || 0,
                Math.min(CLOUD_LAYOUT.minDrawFont * dpr, smallestFont / 2)
            ),
            color: (word) => {
                if (!colors || colors.length === 0) {
                    return '#000000';
                }

                const count = countsByWord.has(word) ? countsByWord.get(word) : minCount;
                const normalized = countRange === 0 ? 0 : (count - minCount) / countRange;
                const colorIndex = Math.min(Math.floor(normalized * colors.length), colors.length - 1);

                return colors[colorIndex];
            },
            hover: onWordHover,
        });
    };

    draw(0, 0);
};

/**
 * Renders the CSS-based ("classic") wordcloud view. Its .w1-.w6 styles are
 * scoped to ".path-mod-wordcloud", which must be an ancestor of the container.
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

    // Words are stored as entered, so they are set as text, never as HTML.
    const spans = entries.map(([word, count]) => {
        const weight = range >= 3 ? 1 + Math.floor(steps * (count - mincount) / range) : 1;
        return Object.assign(document.createElement('span'), {
            className: `word center w${weight}`,
            title: `${count}`,
            textContent: word,
        });
    });

    setClassicCss();

    // Also needed by containers other than the activity page's, e.g. the preview.
    container.classList.add('d-flex', 'flex-wrap', 'gap-2');
    container.replaceChildren(...spans);
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
    const container = document.getElementById('mod-wordcloud-words-box');

    // The list view (2) is a server-side table.
    if (view === 2) {
        return;
    }

    // Words hidden by the visibility setting come as a plain message, not JSON.
    let entries;
    try {
        entries = JSON.parse(jsonentries) || [];
    } catch (e) {
        entries = null;
    }

    if (!Array.isArray(entries)) {
        document.getElementById('mod-wordcloud-view-menu').disabled = true;
        container.innerHTML = '';
        container.appendChild(Object.assign(document.createElement('div'), {
            className: 'alert alert-info alert-block',
            role: 'alert',
            textContent: jsonentries,
        }));
        return;
    }

    if (entries.length === 0) {
        document.getElementById('mod-wordcloud-view-menu').disabled = true;
        const message = await getString('nothingtodisplay');

        container.innerHTML = '<div class="alert alert-info alert-block" role="alert">' + message + '</div>';
        return;
    } else {
        container.innerHTML = '';
    }

    if (view === 1) {
        renderNewView(entries);
    } else {
        renderClassicView(entries, JSON.parse(wordcountrange));
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