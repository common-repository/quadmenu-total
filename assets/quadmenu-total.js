(function ($) {
    var re = /([^&=]+)=?([^&]*)/g;
    var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
    var decode = function (str) {
        return decodeURIComponent(str.replace(decodeRE, " "));
    };
    $.parseParams = function (query) {
        var params = {}, e;
        while (e = re.exec(query)) {
            var k = decode(e[1]), v = decode(e[2]);
            if (k.substring(k.length - 2) === '[]') {
                k = k.substring(0, k.length - 2);
                (params[k] || (params[k] = [])).push(v);
            } else
                params[k] = v;
        }
        return params;
    };


    $(document).on('ajaxSuccess', function (e, xhr, settings) {

        var data = $.parseParams(settings.data);

        if (data.action !== 'wpex_post_import_complete')
            return;

        console.log('Run compiler!');

        if (typeof (quadmenu) == 'undefined')
            return;

        if (!quadmenu.files)
            return;

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'quadmenu_total_compiler',
                nonce: quadmenu.nonce,
            },
            success: function (response) {

                if (!response)
                    return;

                try {
                    $(document).trigger('quadmenu_compiler_files', [quadmenu.files, response, 'save']);
                } catch (error) {
                    alert('Not JSON');
                }

            },
        });

    });


})(jQuery);