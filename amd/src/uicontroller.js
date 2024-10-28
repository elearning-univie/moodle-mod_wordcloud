const mod_wordcloud_set_height = () => {
    const wb = document.getElementById('mod-wordcloud-div');
    const divheight = wb.offsetHeight;
    let newwidth = 0;

    if (divheight < 300) {
        newwidth = 50;
    } else if (divheight < 500) {
        newwidth = 65;
    } else if (divheight < 700) {
        newwidth = 80;
    } else {
        newwidth = 100;
    }

    if (window.innerWidth > 900) {
        if (wb.style.width == '' || newwidth > parseInt(wb.style.width)) {
            wb.style.width = newwidth + "%";
        }
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

const mod_wordcloud_export_listener = () => {
    var exportmenu = document.getElementById('mod-wordcloud-export-menu');
    exportmenu.onchange = function() {
        var selectedval = this.options[this.selectedIndex].value;
        if (selectedval == 'png') {
            window.modWordcloudPic();
        } else {
            window.open(selectedval, '_blank');
        }
        exportmenu.selectedIndex = 0;
    };
};

export const initlistener = () => {
    mod_wordcloud_export_listener();
};

export const init = colors => {
    const targetnode = document.getElementById('mod-wordcloud-words-box');
    const config = { childList: true};
    const callback = (mutationList) => {
        for (const mutation of mutationList) {
            if (mutation.type === 'childList') {
                mod_wordcloud_set_height();
            }
        }
    };

    const observer = new MutationObserver(callback);
    observer.observe(targetnode, config);

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

    var viewmenu = document.getElementById('mod-wordcloud-view-menu');
    viewmenu.onchange = function() {
        window.location.href = this.options[this.selectedIndex].value;
    };

    mod_wordcloud_export_listener();
    mod_wordcloud_set_height();

    var element = document.getElementById('mod-wordcloud-div');
    element.style.visibility = "visible";
};