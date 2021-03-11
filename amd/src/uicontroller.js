define(['jquery'],
    function ($) {
        return {
            init: function () {
                if (!document.fullscreenEnabled) {
                    var fs_btn = document.getElementById('mod-wordcloud-fs-btn');
                    fs_btn.style.display = "none";
                }
                $.mod_wordcloud_fs_toggle = function () {
                    if (document.fullscreenElement === null) {
                        var fs_element = document.getElementById('mod-wordcloud-content');
                        fs_element.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                };
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
    });