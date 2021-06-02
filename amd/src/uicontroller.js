const mod_wordcloud_fs_toggle = () => {
    if (document.fullscreenElement === null) {
        var fs_element = document.getElementById('mod-wordcloud-content');
        fs_element.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

export const init = colors => {
    if (colors.length == 6) {
        var stylerules = '';
        var editCSS = document.createElement('style');

        for (let i = colors.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [colors[i], colors[j]] = [colors[j], colors[i]];
        }

        for (let i = 1; i <= colors.length; i++) {
            stylerules += '.path-mod-wordcloud .w' + i + ' {color: #' + colors[i - 1] + ';} \n';
        }

        editCSS.innerHTML = stylerules;
        document.head.appendChild(editCSS);
    }

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