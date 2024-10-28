import html2canvas from "mod_wordcloud/html2canvas";

export const init = wcname => {
    wcname = wcname.replace(' ', '_');

    const modWordcloudPic = () => {
        const wcContent = document.getElementById("mod-wordcloud-words-box");

        html2canvas(wcContent, {
            scrollX: 0,
            scrollY: -window.scrollY
        }).then(canvas => {
            const img = canvas.toDataURL("image/png");
            const link = document.createElement('a');
            link.href = img;
            link.download = `${wcname}.png`;
            link.click();
        });
    };

    // Expose the function for external use (if necessary)
    window.modWordcloudPic = modWordcloudPic;
};