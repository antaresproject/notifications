Vue.component('vue-template-preview', {
    template: '#notificationTemplatePreview-template',

    data: function() {
        return {
            title: '',
            content: '',
            show: false,
            loading: false,
            height: '100%'
        }
    },

    mounted: function() {
        var self = this;

        $(this.$el).on('click', '.modal__close', function() {
            self.close();
        });
    },

    computed: {
        src: function() {
            return 'data:text/html;charset=utf-8,' + encodeURI(this.content);
        }
    },

    methods: {
        open: function() {
            this.show = true;
            this.loading = true;
            this.title = '';
            this.content = '';

            this.$nextTick(function() {
                var $modal = $('#notificationTemplatePreview');

                APP.modal.init({
                    element: $modal,
                    title: $modal.attr('title')
                });
            });
        },

        close: function() {
            this.loading = false;
            this.show = false;
            this.title = '';
            this.content = '';
        },

        set: function(title, content) {
            this.title = title;
            this.content = content;
            this.loading = false;

            this.$nextTick(function() {
                var height = $('#notificationTemplatePreview').find('iframe:first')[0].contentWindow.document.body.scrollHeight;

                if(height > 600) {
                    height = 600;
                }

                this.height = height;
            });
        }
    }
});