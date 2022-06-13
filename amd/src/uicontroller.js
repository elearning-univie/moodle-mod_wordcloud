const mod_wordcloud_fs_toggle = () => {
    if (document.fullscreenElement === null) {
        var fs_element = document.getElementById('mod-wordcloud-content');
        fs_element.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

const mod_wordcloud_hex_to_hsl = (color) => {
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

export const init = colors => {
    var stylerules = '';
    var editCSS = document.createElement('style');

    if (colors.length == 6) {
        for (let i = colors.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [colors[i], colors[j]] = [colors[j], colors[i]];
        }

        for (let i = 1; i <= colors.length; i++) {
            stylerules += '.path-mod-wordcloud .w' + i + ' {color: #' + colors[i - 1] + ';} \n';
        }
    } else if (colors.length == 1) {
        var [h, s, l] = mod_wordcloud_hex_to_hsl(colors[0]);
        var nextstep = 8;

        l = 30;

        for (let i = 1; i < 7; i++) {
            stylerules += '.path-mod-wordcloud .w' + i + ' {color: hsl(' + h + ', ' + s + '%, ' + l + '%);} \n';
            l = l + nextstep;
            nextstep++;
        }
    }

    editCSS.innerHTML = stylerules;
    document.head.appendChild(editCSS);

    var fs_btn = document.getElementById('mod-wordcloud-fs-btn');

    if (!document.fullscreenEnabled) {
        fs_btn.style.display = "none";
    } else {
        fs_btn.addEventListener("click", function () {
            mod_wordcloud_fs_toggle();
        });

        document.onfullscreenchange = function () {
            var fs_icon = document.getElementById('mod-wordcloud-fs-icon');
            if (document.fullscreenElement === null) {
                fs_icon.className = fs_icon.className.replace(/\bfa-compress\b/g, "fa-expand");
            } else {
                fs_icon.className = fs_icon.className.replace(/\bfa-expand\b/g, "fa-compress");
            }
        };
    }
};