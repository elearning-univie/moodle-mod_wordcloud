import html2canvas from 'lib/html2canvas';

export const init = () => {
    window.log.console("testcall");
};

export const exportpng = () => {
    window.log.console("testcall");
    html2canvas(document.body).then(function(canvas) {
        //document.body.appendChild(canvas);
        var base64image = canvas.toDataURL("image/png");

        // Open the image in a new window
        window.open(base64image , "_blank");
    });
};