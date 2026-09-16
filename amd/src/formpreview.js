import {renderNewView, renderClassicView} from "mod_wordcloud/renderer";
import {wordcloudColors} from "mod_wordcloud/uicontroller";
import ModalCancel from "core/modal_cancel";
import ModalEvents from "core/modal_events";
import {get_string as getString} from "core/str";

/**
 * Sample word/weight pairs used to render the preview, since a new or
 * not-yet-submitted-to activity has no real words to draw from.
 *
 * @type {Array}
 */
const sampleEntries = [
    ["Moodle", 30],
    ["Learning", 24],
    ["Wordcloud", 20],
    ["Course", 18],
    ["Student", 16],
    ["Teacher", 14],
    ["Activity", 12],
    ["Feedback", 11],
    ["Quiz", 10],
    ["Forum", 9],
    ["Assignment", 8],
    ["Grade", 7],
    ["Collaboration", 6],
    ["Engagement", 5],
    ["Discussion", 4],
];

/**
 * The only font the classic renderer can display. Must match
 * WORDCLOUD_CLASSIC_FONT in lib.php.
 *
 * @type {string}
 */
const CLASSIC_FONT = 'Arial, sans-serif';

/**
 * The site-wide default font colours (fontcolor1-6) and base render settings,
 * passed in from mod_form.php.
 */
let defaultFontColors = [];
let defaultRenderSettings = {};

/**
 * Returns the value of the checked radio button in the given named group.
 *
 * @param {string} name the "name" attribute of the radio group.
 * @return {string|null} the checked value, or null if none is checked.
 */
const getCheckedRadioValue = (name) => {
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    return checked ? checked.value : null;
};

/**
 * Builds the colour palette to preview with, mirroring the logic in view.php:
 * either the 6 site-wide default colours (random scheme) or a single colour
 * (custom hex or one of the site defaults) for the sequentially shaded scheme.
 *
 * @return {string[]} array of hex colour strings (without '#'), for mod_wordcloud/uicontroller.
 */
const getConfiguredColors = () => {
    const usemonocolor = getCheckedRadioValue('usemonocolor');

    if (usemonocolor === '1') {
        const monocolor = getCheckedRadioValue('monocolor');

        // uicontroller.js's getColorsToDisplay() treats a single-colour array
        // differently to the 6-colour array: it passes colors[0] straight into
        // hexToHsl(), which expects a leading '#' (it strips the first
        // character assuming it's the '#'). The 6-colour branch, by contrast,
        // expects bare hex and adds the '#' itself. This mirrors how view.php
        // builds $colors for the web view: '#' . $fontcolor for the single-colour
        // case, bare $fontcolor values in the 6-colour loop.
        if (monocolor === '0') {
            const hexField = document.getElementById('id_monocolorhex');
            const hex = hexField && /^[0-9a-fA-F]{6}$/.test(hexField.value.trim())
                ? hexField.value.trim()
                : '000000';
            return ['#' + hex];
        }

        const index = Number(monocolor) - 1;
        return ['#' + (defaultFontColors[index] || defaultFontColors[0] || '000000')];
    }

    return defaultFontColors.length === 6 ? [...defaultFontColors] : ['0063A6'];
};

/**
 * Reads the current (possibly unsaved) values of the appearance settings fields
 * and renders a sample wordcloud into the given container, using the same
 * classic/modern view logic and rendering code as the real activity page.
 *
 * @param {HTMLElement} container the element to render the preview into.
 */
const renderPreview = (container) => {
    const fontField = document.getElementById('id_font');
    const alignmentField = document.getElementById('id_textalignment');

    const fontFamily = fontField ? fontField.value : CLASSIC_FONT;
    const alignment = alignmentField ? alignmentField.value : 'h';

    wordcloudColors.colors = getConfiguredColors();

    // Mirrors mod_wordcloud_get_render_style(): the classic renderer cannot
    // apply a font or rotate words, so it is only used for horizontal text in
    // the single font it can display.
    if (alignment === 'h' && fontFamily === CLASSIC_FONT) {
        const weights = sampleEntries.map(entry => entry[1]);
        const wordcountrange = {
            mincount: Math.min(...weights),
            maxcount: Math.max(...weights),
        };

        renderClassicView(sampleEntries, wordcountrange, container);
    } else {
        const rendersettings = {
            ...defaultRenderSettings,
            fontFamily,
            alignmentmode: alignment,
        };

        renderNewView(sampleEntries, container, rendersettings, undefined, 'wordcloud-preview-canvas');
    }
};

/**
 * Opens a modal showing a preview of the wordcloud, rendered with the appearance
 * settings currently entered on the form (including unsaved changes).
 */
const showPreview = async() => {
    const modal = await ModalCancel.create({
        title: await getString('previewbtn', 'mod_wordcloud'),
        body: '<div class="path-mod-wordcloud">' +
            '<div id="wordcloud-preview-words-box" class="wordcloud-preview-canvas-container"></div></div>',
    });

    modal.getRoot().on(ModalEvents.hidden, () => {
        modal.destroy();
    });

    modal.getRoot().on(ModalEvents.shown, () => {
        const container = document.getElementById('wordcloud-preview-words-box');
        if (container) {
            renderPreview(container);
        }
    });

    modal.show();
};

export const init = (fontcolors, rendersettingsjson) => {
    defaultFontColors = fontcolors || [];

    try {
        defaultRenderSettings = rendersettingsjson ? JSON.parse(rendersettingsjson) : {};
    } catch (e) {
        defaultRenderSettings = {};
    }
    delete defaultRenderSettings.weightFactor;

    const button = document.getElementById('id_wordcloudpreviewbtn');

    if (button) {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            showPreview();
        });
    }
};
