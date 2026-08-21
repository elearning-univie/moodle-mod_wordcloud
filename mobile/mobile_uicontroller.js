// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Renders the wordcloud in the Moodle App, mirroring amd/src/renderer.js and
 * amd/src/uicontroller.js as closely as this isolated JS context allows (no AMD
 * module loading is available here, so the shared logic is reimplemented).
 *
 * @package   mod_wordcloud
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var that = this;

/**
 * Converts a hex colour to [hue, saturation%, lightness%].
 */
function mod_wordcloud_hex_to_hsl(color) {
    var [r, g, b] = color.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, (m, r, g, b) => '#' + r + r + g + g + b + b)
        .substring(1).match(/.{2}/g).map(x => parseInt(x, 16));

    r /= 255;
    g /= 255;
    b /= 255;

    var max = Math.max(r, g, b), min = Math.min(r, g, b);
    var h, s, l = (max + min) / 2;

    if (max == min) {
        h = s = 0;
    } else {
        var d = (max - min);
        s = l >= 0.5 ? d / (2 - (max + min)) : d / (max + min);
        switch (max) {
            case r: h = ((g - b) / d + 0) * 60; break;
            case g: h = ((b - r) / d + 2) * 60; break;
            case b: h = ((r - g) / d + 4) * 60; break;
        }
    }
    return [Math.round(h), Math.round(s * 100), Math.round(l * 100)];
}

/**
 * Port of amd/src/uicontroller.js's getColorsToDisplay(): turns the 6 (random
 * scheme) or 1 (sequentially shaded scheme) configured colours into a final,
 * ready-to-use palette.
 *
 * @param {string[]} colors hex colours (without '#'), as sent in CONTENT_OTHERDATA.colors.
 * @return {string[]} final palette, e.g. ["#aabbcc", ...] or ["hsl(210,50%,30%)", ...].
 */
function mod_wordcloud_get_colors_to_display(colors) {
    var finishedColors = [];

    if (colors.length == 6) {
        var shuffled = colors.slice();
        for (var i = shuffled.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = shuffled[i];
            shuffled[i] = shuffled[j];
            shuffled[j] = tmp;
        }
        for (var k = 0; k < shuffled.length; k++) {
            finishedColors.push('#' + shuffled[k]);
        }
    } else if (colors.length == 1) {
        var hsl = mod_wordcloud_hex_to_hsl(colors[0]);
        var h = hsl[0];
        var s = hsl[1];
        var l = 30;
        var nextstep = 8;

        for (var n = 1; n < 7; n++) {
            finishedColors.push('hsl(' + h + ',' + s + '%,' + l + '%)');
            l = l + nextstep;
            nextstep++;
        }
    }

    return finishedColors;
}

/**
 * Port of renderer.js's getRotationSettings(): wordcloud2 rotation options for a
 * given text alignment mode.
 *
 * @param {string} mode one of 'h', 'v', 'hv', 'hvd'.
 * @return {Object} wordcloud2 rotation options.
 */
function mod_wordcloud_get_rotation_settings(mode) {
    var halfPi = Math.PI / 2;

    switch (mode) {
        case 'v':
            return {minRotation: -halfPi, maxRotation: halfPi, rotationSteps: 2, rotateRatio: 1};
        case 'hv':
            return {minRotation: -halfPi, maxRotation: halfPi, rotationSteps: 3, rotateRatio: 0.66};
        case 'hvd':
            return {minRotation: -halfPi, maxRotation: halfPi, rotationSteps: 5, rotateRatio: 0.8};
        default:
            return {minRotation: 0, maxRotation: 0, rotationSteps: 1, rotateRatio: 0};
    }
}

/**
 * Turns [word, count] entries into the classic word-cloud HTML markup, mirroring
 * the weight calculation used by renderClassicView() in amd/src/renderer.js.
 *
 * @param {Array} entries [word, count] pairs.
 * @param {Object} wordcountrange {mincount, maxcount} across all entries.
 * @return {string} HTML markup for #mod-wordcloud-words-box.
 */
function mod_wordcloud_build_cloudhtml(entries, wordcountrange) {
    var steps = 6;
    var mincount = Number(wordcountrange.mincount);
    var maxcount = Number(wordcountrange.maxcount);
    var range = (maxcount - mincount) * 1.0001;
    var cloudhtml = '';

    entries.forEach(function(entry) {
        var word = entry[0];
        var count = entry[1];
        var weight;

        if (range >= 3) {
            weight = 1 + Math.floor(steps * (count - mincount) / range);
        } else {
            weight = 1;
        }

        cloudhtml += '<span class="word center w' + weight + '" title="' + count + '">' + word + '</span>';
    });

    return cloudhtml;
}

/**
 * Renders the classic (CSS-based) view: injects the .w1-.w6 colour rules and fills
 * #mod-wordcloud-words-box with the word spans.
 *
 * @param {HTMLElement} box #mod-wordcloud-words-box.
 * @param {Array} entries [word, count] pairs.
 * @param {Object} wordcountrange {mincount, maxcount} across all entries.
 * @param {string[]} colors hex colours (without '#'), as sent in CONTENT_OTHERDATA.colors.
 */
function mod_wordcloud_render_classic(box, entries, wordcountrange, colors) {
    var editCSS = document.createElement('style');
    var stylerules = '';
    var processedColors = mod_wordcloud_get_colors_to_display(colors);

    processedColors.forEach(function(colorValue, index) {
        stylerules += '#mod-wordcloud-words-box .w' + (index + 1) + ' {color: ' + colorValue + ';} \n';
    });

    editCSS.innerHTML = stylerules;
    document.head.appendChild(editCSS);

    box.innerHTML = mod_wordcloud_build_cloudhtml(entries, wordcountrange);
}

var wordCloud2Promise = null;

/**
 * Dynamically loads js/wordcloud2/wordcloud2.js (it isn't bundled with the app).
 * Outside of an AMD/CommonJS context, the library attaches itself to window.WordCloud.
 *
 * @return {Promise} resolves with the WordCloud function once loaded.
 */
function mod_wordcloud_load_wordcloud2() {
    if (window.WordCloud) {
        return Promise.resolve(window.WordCloud);
    }

    if (!wordCloud2Promise) {
        wordCloud2Promise = new Promise(function(resolve, reject) {
            var script = document.createElement('script');
            script.src = that.CONTENT_OTHERDATA.wordcloud2url;
            script.onload = function() {
                if (window.WordCloud) {
                    resolve(window.WordCloud);
                } else {
                    reject(new Error('wordcloud2 loaded but did not expose window.WordCloud'));
                }
            };
            script.onerror = function() {
                reject(new Error('Failed to load wordcloud2.js'));
            };
            document.head.appendChild(script);
        });
    }

    return wordCloud2Promise;
}

/**
 * Renders the modern (canvas-based) view via wordcloud2, mirroring renderNewView()
 * in amd/src/renderer.js.
 *
 * @param {HTMLElement} box #mod-wordcloud-words-box.
 * @param {Array} entries [word, count] pairs.
 * @param {string[]} colors hex colours (without '#'), as sent in CONTENT_OTHERDATA.colors.
 * @param {Object} rendersettings wordcloud2 settings, including "alignmentmode" and "fontFamily".
 * @param {Object} wordcountrange {mincount, maxcount}, used only for the classic-view fallback.
 */
function mod_wordcloud_render_modern(box, entries, colors, rendersettings, wordcountrange) {
    mod_wordcloud_load_wordcloud2().then(function(WordCloud) {
        var processedColors = mod_wordcloud_get_colors_to_display(colors);

        box.innerHTML = '';
        var canvas = document.createElement('canvas');
        box.appendChild(canvas);

        var baseHeight = 250;
        var wordThreshold = 20;
        var extraHeightPerWord = 15;
        var height = baseHeight;
        if (entries.length > wordThreshold) {
            height += (entries.length - wordThreshold) * extraHeightPerWord;
        }
        box.style.height = height + 'px';

        var width = box.clientWidth || 300;
        var dpr = window.devicePixelRatio || 1;

        canvas.width = Math.max(1, Math.round(width * dpr));
        canvas.height = Math.max(1, Math.round(height * dpr));
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';

        var weights = entries.map(function(entry) {
            return entry[1];
        });
        var maxWeight = Math.max.apply(null, weights) || 1;
        var minWeight = Math.min.apply(null, weights) || 0;
        var weightRange = (maxWeight - minWeight) || 1;
        var targetMaxFontSize = Math.min(width, height) * 0.25;
        var internalMaxFontSize = targetMaxFontSize * dpr;
        var safeMinFontSize = 12 * dpr;

        var rotation = mod_wordcloud_get_rotation_settings(rendersettings.alignmentmode);
        var cleanRenderSettings = Object.assign({}, rendersettings);
        delete cleanRenderSettings.weightFactor;

        var baseConfig = {
            list: entries,
            weightFactor: function(weight) {
                if (maxWeight === 0) {
                    return safeMinFontSize;
                }
                return Math.max(safeMinFontSize, (weight / maxWeight) * internalMaxFontSize);
            },
            color: function(word, weight) {
                if (!processedColors.length) {
                    return '#000000';
                }
                var normalizedWeight = (weight - minWeight) / weightRange;
                var colorIndex = Math.min(Math.floor(normalizedWeight * processedColors.length), processedColors.length - 1);
                return processedColors[colorIndex];
            },
        };

        WordCloud(canvas, Object.assign({}, cleanRenderSettings, baseConfig, rotation));
    }).catch(function() {
        // wordcloud2 failed to load - fall back to the classic view so the
        // activity still shows something useful.
        mod_wordcloud_render_classic(box, entries, wordcountrange, colors);
    });
}

/**
 * Normalizes a value that may already be parsed or may still be a JSON string.
 *
 * The app auto-decodes any CONTENT_OTHERDATA string that looks like JSON before
 * exposing it here, so values like CONTENT_OTHERDATA.entries arrive as real arrays
 * or objects already. Values coming straight from a Web Service response (e.g.
 * response.entries in addWordCallDone) are untouched raw JSON strings (PARAM_RAW).
 * This handles both without needing to know which kind of source it came from.
 *
 * @param {*} value a possibly-already-parsed value, a JSON string, or a plain string.
 * @return {*} the parsed value, or the original value if it wasn't a JSON string.
 */
function mod_wordcloud_normalize(value) {
    if (typeof value === 'string') {
        try {
            return JSON.parse(value);
        } catch (e) {
            return value;
        }
    }
    return value;
}

/**
 * Renders the wordcloud into #mod-wordcloud-words-box from the "entries" and
 * "wordcountrange" values returned by the mod_wordcloud_get_entries/add_word Web
 * Services, using either the classic or modern view depending on CONTENT_OTHERDATA.renderstyle
 * (matches how view.php picks a view for the web page). When entries isn't a wordlist
 * array (e.g. it's a "words are hidden until..." message), it's shown as plain text.
 *
 * @param {string|Array} entriesraw [word, count] pairs (JSON-encoded or already parsed), or a plain message.
 * @param {string|Object} wordcountrangeraw {mincount, maxcount} (JSON-encoded or already parsed).
 */
function mod_wordcloud_render(entriesraw, wordcountrangeraw) {
    var box = document.getElementById('mod-wordcloud-words-box');
    if (!box) {
        return;
    }

    var entries = mod_wordcloud_normalize(entriesraw);

    if (!Array.isArray(entries)) {
        box.textContent = (typeof entries === 'string') ? entries : String(entriesraw);
        return;
    }

    if (!entries.length) {
        box.innerHTML = '';
        return;
    }

    var wordcountrange = mod_wordcloud_normalize(wordcountrangeraw) || {};
    var colors = mod_wordcloud_normalize(that.CONTENT_OTHERDATA.colors) || [];

    if (Number(that.CONTENT_OTHERDATA.renderstyle) === 1) {
        var rendersettings = mod_wordcloud_normalize(that.CONTENT_OTHERDATA.rendersettings) || {};
        mod_wordcloud_render_modern(box, entries, colors, rendersettings, wordcountrange);
    } else {
        mod_wordcloud_render_classic(box, entries, wordcountrange, colors);
    }
}

(function() {
    var box = document.getElementById('mod-wordcloud-words-box');
    if (box && box.clientWidth < 450) {
        var editCSS = document.createElement('style');
        editCSS.innerHTML = '#mod-wordcloud-words-box {font-size: 70%;}\n';
        document.head.appendChild(editCSS);
    }

    setTimeout(function() {
        mod_wordcloud_render(that.CONTENT_OTHERDATA.entries, that.CONTENT_OTHERDATA.wordcountrange);
    });
})();

that.addWordCallDone = function(response) {
    if (response.entries) {
        mod_wordcloud_render(response.entries, response.wordcountrange);
    }
    document.getElementById('mod-wordcloud-new-word').value = '';
};
