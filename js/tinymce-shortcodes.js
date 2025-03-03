(function() {
    tinymce.create('tinymce.plugins.wp_multi_shortcodes', {
        init: function(editor, url) {
            editor.addButton('wp_multi_shortcodes', {
                type: 'menubutton',
                text: 'Shortcodes',
                icon: false,
                menu: wpMultiShortcodes.map(function(shortcode) {
                    return {
                        text: shortcode.shortcode_name,
                        onclick: function() {
                            editor.insertContent('[' + shortcode.shortcode_name + ']');
                        }
                    };
                })
            });
        }
    });

    tinymce.PluginManager.add('wp_multi_shortcodes', tinymce.plugins.wp_multi_shortcodes);
})();
