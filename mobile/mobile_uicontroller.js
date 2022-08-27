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
 *
 * @package   mod_wordcloud
 * @copyright 2022 University of Vienna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var that = this;

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
            case r: h = ((g - b) / d + 0)*60; break;
            case g: h = ((b - r) / d + 2)*60; break;
            case b: h = ((r - g) / d + 4)*60; break;
        }
    }
    return [Math.round(h), Math.round(s*100), Math.round(l*100)];
};

(function() {
    var editCSS = document.createElement('style');
    var stylerules = '';

    if (document.getElementById('mod-wordcloud-words-box').clientWidth < 450) {
        stylerules = '#mod-wordcloud-words-box {font-size: 70%;}\n';
    }

    if (that.CONTENT_OTHERDATA.colors.charAt(0) == '#') {
        var [h, s, l] = mod_wordcloud_hex_to_hsl(that.CONTENT_OTHERDATA.colors);
        var nextstep = 8;

        l = 30;

        for (let i = 1; i < 7; i++) {
            stylerules += '.w' + i + ' {color: hsl(' + h + ', ' + s + '%, ' + l + '%);} \n';
            l = l + nextstep;
            nextstep++;
        }
    } else {
        stylerules += that.CONTENT_OTHERDATA.colors;
    }

    editCSS.innerHTML = stylerules;
    document.head.appendChild(editCSS);

    setTimeout(function() {
        document.getElementById('mod-wordcloud-words-box').innerHTML = that.CONTENT_OTHERDATA.cloudhtml;
    });
})();

that.addWordCallDone = function(response) {
    if (response.cloudhtml) {
        document.getElementById('mod-wordcloud-words-box').innerHTML = response.cloudhtml;
    }
    document.getElementById('mod-wordcloud-new-word').value = '';
};