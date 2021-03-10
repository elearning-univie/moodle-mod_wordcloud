define(['jquery'],
    function ($) {
        return {
            init: function () {
                if (!document.fullscreenEnabled) {
                    var fs_btn = document.getElementById('mod-wordcloud-fs-btn-div');
                    fs_btn.style.display = "none";
                }
                $.mod_wordcloud_fs_toggle = function () {
                    if (document.fullscreenElement === null) {
                        var fs_element = document.getElementById('region-main');
                        fs_element.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                };
            }
        };
    });