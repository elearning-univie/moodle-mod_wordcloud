import html2canvas from "mod_wordcloud/html2canvas";
import $ from "jquery";

export const init = wcname => {
    wcname = wcname.replace(' ', '_');

    $.mod_wordcloud_pic = function () {
        var wc_content = document.getElementById("mod-wordcloud-words-box");
        html2canvas(wc_content, {
            scrollX: 0,
            scrollY: -window.scrollY
        }).then(canvas => {
            var img = canvas.toDataURL("image/png");
            var a = document.createElement('a');
            a.href = img;
            a.download = wcname + ".png";
            a.click();
        });
    };
};