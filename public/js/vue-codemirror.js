Vue.component('codemirror', {
    template: '<textarea></textarea>',
    data: function() {
        return {
            editor: null
        }
    },
    props: {
        value: {
            type: String,
            default: function () {
                return '';
            }
        },
        events: Array,
        options: {
            type: Object,
            required: true
        }
    },

    computed: {
        instance: function() {
            return this.editor;
        }
    },

    mounted: function() {
        var component = this;

        this.editor = CodeMirror.fromTextArea(this.$el, this.options);

        this.editor.on('change', function(editor) {
            component.value = editor.getValue();

            component.$emit('change', component.value);
            component.$emit('input', component.value);
        });

        var events = [
            'scroll',
            'changes',
            'beforeChange',
            'cursorActivity',
            'keyHandled',
            'inputRead',
            'electricInput',
            'beforeSelectionChange',
            'viewportChange',
            'swapDoc',
            'gutterClick',
            'gutterContextMenu',
            'focus',
            'blur',
            'refresh',
            'optionChange',
            'scrollCursorIntoView',
            'update'
        ];
        if (this.events && this.events.length) {
            events = events.concat(this.events)
        }

        for (var i = 0; i < events.length; ++i) {
            (function(event) {
                component.editor.on(event, function(a, b, c) {
                    component.$emit(event, a, b, c)
                })
            })(events[i]);
        }

        this.$emit('ready', this.editor);

        // prevents funky dynamic rendering
        window.setTimeout(function() {
            component.editor.refresh();
        }, 0);
    },

    beforeDestroy: function() {
        var element = this.editor.doc.cm.getWrapperElement();

        if (element && element.remove) {
            element.remove()
        }
    },

    watch: {
        options: {
            deep: true,
            handler: function(options) {
                for (var key in options) {
                    if (options.hasOwnProperty(key)) {
                        this.editor.setOption(key, options[key]);
                    }
                }
            }
        },
        value: function(newVal) {
            var value = this.editor.getValue();

            if (newVal !== value) {
                var scrollInfo = this.editor.getScrollInfo();

                this.editor.setValue(newVal);
                this.editor.scrollTo(scrollInfo.left, scrollInfo.top)
            }
        }
    },
    methods: {
        refresh: function() {
            this.editor.refresh()
        }
    }
});