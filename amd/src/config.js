define([], function() {
    window.requirejs.config({
        paths: {
            // Enter the paths to your required java-script files.
            "html2canvas": M.cfg.wwwroot + '/mod/wordcloud/js/html2canvas/html2canvas',
            "wordcloud": M.cfg.wwwroot + '/mod/wordcloud/js/wordcloud2/wordcloud2'
        },
        shim: {
            // Enter the "names" that will be used to refer to your libraries.
            'html2canvas': {exports: 'html2canvas'},
            'wordcloud': {exports: 'wordcloud'}
        }
    });
});
