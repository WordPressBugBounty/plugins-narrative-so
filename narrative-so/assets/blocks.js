/**
 * Hello World: Step 1
 *
 * Simple block, renders and saves the same content without interactivity.
 *
 * Using inline styles - no external stylesheet needed.  Not recommended
 * because all of these styles will appear in `post_content`.
 */

(function (blocks, editor, i18n, element, components, _) {

    var InspectorControls = wp.editor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var el = element.createElement;
    var Fragment = wp.element.Fragment;
    var __ = i18n.__;
    var $ = jQuery;

    var iconEl = el('svg', { width: 18, height: 18 },
        el('path', { fill: "#9D9D9D", d: "m15,18l-7,0l-3,0l0,-13l3,0l0,10l7,0l0,-15l3,0l0,18l-3,0zm-5,-15l-7,0l0,15l-3,0l0,-18l3,0l10,0l0,3l0,10l-3,0l0,-13",'id':'narrative-colour-25x25' } )
    );

    function insertScript(str) {

        if (!str.length) return;

        var regex = /\<script.*?src=(?:(?:'([^']*)')|(?:"([^"]*)")|([^\s]*))/i;
        var src = regex.exec(str.replace(/\r?\n/g, ""));

        $('#narrative_script').remove();

        if (src[2]) {
            $('body').append('<script id="narrative_script" src=\"' + src[2] + '\"></script>');
        } else {
            return;
        }
    }

    blocks.registerBlockType('narrative/block', {
        title: __('Narrative', 'narrative-publisher'),
        icon: iconEl,
        attributes: {
            narrative_script: {
                type: 'string',
                source: 'meta',
                meta: 'narrative_post_script'
            }
        },
        category: 'layout',
        supports: {
            className: false,
            customClassName: true,
            inserter: true,
        },
        edit: function (props) {

            let attributes = props.attributes;

            if ( ! attributes.narrative_script ){
                return el(
                    'b',
                    null,
                    __('It\'s not a narrative post', 'narrative-publisher')
                );
            }
            try {

                insertScript(atob(attributes.narrative_script));

            } catch (err) {

                console.error('Narrative error: ' + err.name + ":" + err.message + "\n" + err.stack);

            }

            var data = wp.data.select("core/editor"),
                post_id = data.getCurrentPostId() ? data.getCurrentPostId() : '';

            var output = el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        null,
                        el(
                            'div',
                            null,
                            el(
                                'a',
                                {
                                    placeholder: __('(optional)', 'narrative-publisher'),
                                    href: 'narrative-app://open/' + post_id,
                                    target: '_blank',
                                    className: 'narrative_open_app_button components-button is-button is-primary'
                                },
                                __('Edit in Narrative', 'narrative-publisher')
                            )
                        )
                    )
                ),
                el(
                    element.RawHTML,
                    null,
                    atob(attributes.narrative_script)
                )
            );
            return output;

        },
        save: function () {
            // Rendering in PHP
            return null;
        },
    });

}(
    window.wp.blocks,
    window.wp.editor,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window._
));
