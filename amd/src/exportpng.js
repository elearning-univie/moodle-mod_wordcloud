import html2canvas from "lib/html2canvas/html2canvas.js";
import saveAs from "mod_wordcloud/filesaver";
import $ from "jquery";

export const init = () => {
    $.mod_wordcloud_pic = function () {
        var wc_content = document.getElementById("mod-wordcloud-content");
        //var url;
        html2canvas(wc_content, {
            scrollX: 0,
            scrollY: -window.scrollY + 10,
            height: wc_content.clientHeight + 20
        }).then(canvas => {
            //var imgData = canvas.toDataURL('image/png');
            canvas.toBlob(function(blob) {
                /*url = window.URL.createObjectURL(blob);
                downloads.download({
                    'url': url,
                    'filename': 'file.png',
                });*/
                saveAs(blob, "pretty_image.png");
            });
            //window.URL.revokeObjectURL(url);
            //window.open(imgData , "_blank");
        });
    };
};