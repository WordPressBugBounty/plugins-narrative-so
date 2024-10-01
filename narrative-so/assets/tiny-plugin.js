tinymce.create('tinymce.plugins.narrative', {
    narrative: function (editor) {

        editor.on('BeforeSetContent', function (event) {


            if ('html' === event.format && !event.get) {

                if ( ! window.narrative_post_script ) {
                    return event.content;
                }

                var regex = /<\/?noscript>/ig,
                    narrative_post_script = atob(window.narrative_post_script),
                    narrative_post_script = narrative_post_script.replace(regex, "");

                console.log(narrative_post_script);

                event.content = event.content.replace(/\[narrative\]/g, '<br><script id="narrative_start"></script>' + narrative_post_script + '<script id="narrative_end"></script><br>');

            }

        });

        editor.on('PostProcess', function (event) {

            if (event.get) {
                event.content = event.content.replace(/<script\sid=\"narrative_start(.|\n)*?narrative_end\"><\/script>/g, '[narrative]');
            }

        });


    }
});


tinymce.PluginManager.add('narrative', tinymce.plugins.narrative);

