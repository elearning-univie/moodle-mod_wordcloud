define([], function () {
    window.requirejs.config({
        paths: {
            //Enter the paths to your required java-script files
            "filesaver": M.cfg.wwwroot + '/mod/wordcloud/js/filesaver/FileSaver'

        },
        shim: {
            //Enter the "names" that will be used to refer to your libraries
            'filesaver': {exports: 'saveAs'}
        }
    });
});