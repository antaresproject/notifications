Vue.component('vue-ckeditor', {
    template: '<textarea :name="name" :id="id" :value="value" :types="types" :config="config" class="richtext"></textarea>',
    props: {
        name: {
            type: String,
            default: function () {
                return 'editor';
            }
        },
        value: {
            type: String
        },
        id: {
            type: String,
            default: function () {
                return 'editor';
            }
        },
        types: {
            type: String,
            default: function () {
                return 'classic';
            }
        },
        config: {
            type: Object,
            default: function () {
                return {};
            }
        }
    },

    data: function() {
        return {
            destroyed: false
        }
    },

    computed: {
        instance: function() {
            return CKEDITOR.instances[this.id];
        }
    },

    watch: {
        value: function(val) {
            if (this.instance) {
                this.update(val)
            }
        }
    },

    mounted: function() {
        this.create();
    },

    beforeDestroy: function() {
        this.destroy();
    },

    methods: {
        create: function () {
            if (typeof CKEDITOR === 'undefined') {
                console.log('CKEDITOR is missing (http://ckeditor.com/)')
            }
            else {
                if (this.types === 'inline') {
                    CKEDITOR.inline(this.id, this.config);
                } else {
                    CKEDITOR.replace(this.id, this.config);
                }

                this.instance.setData(this.value);
                this.instance.on('change', this.onChange);
                this.instance.on('blur', this.onBlur);
                this.instance.on('focus', this.onFocus);
            }
        },

        update: function (val) {
            var html = this.instance.getData();

            if (html !== val) {
                this.instance.setData(val)
            }
        },

        destroy: function () {
            if (!this.destroyed) {
                this.instance.focusManager.blur(true);
                this.instance.removeAllListeners();
                this.instance.destroy();
                this.destroyed = true
            }
        },

        onChange: function () {
            var html = this.instance.getData();

            if (html !== this.value) {
                this.$emit('input', html)
            }
        },

        onBlur: function () {
            this.$emit('blur', this.instance);
        },

        onFocus: function () {
            this.$emit('focus', this.instance);
        }
    }

});