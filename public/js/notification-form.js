Vue.component('v-select', VueSelect.VueSelect);

new Vue({
    el: '#form-notification',
    mixins: [formMixin],
    delimiters: ['${', '}'],
    data: {
        categories: [],
        types: [],
        severities: [],
        events: [],
        contents: {},
        variables: [],
        notification: {},
        selected_lang: null,

        eventRecipients: [],
        eventVariables: [],
        disabledContentTitle: false
    },

    created: function() {
        var $form = $('#form-notification');

        this.categories = $form.data('provider-categories');
        this.types = $form.data('provider-types');
        this.severities = $form.data('provider-severities');
        this.events = $form.data('provider-events');
        this.contents = $form.data('provider-contents');
        this.variables = $form.data('provider-variables');
        this.notification = $form.data('provider-notification');
        this.selected_lang = $form.data('provider-selected-lang');
        this.actionUrl = $form.attr('action');

        if(this.notification.recipients && this.notification.event_model) {
            this.notification.recipients = _(this.notification.event_model.recipients).keyBy('id').at(this.notification.recipients).value();
            this.notification.event = this.notification.event_model;
        }
    },

    mounted: function() {
        var self = this;

        $('#notification-preview-button').click(function() {
            var url = $(this).data('url');

            self.preview(url);

            return false;
        });

        $('#notification-send-test-button').click(function() {
            var url = $(this).data('url');

            self.sendTest(url);

            return false;
        });
    },

    computed: {

        contentConfig: function() {
            return {
                height: 500,
                width: '100%',
                fullPage: false,
                skin: 'antares,/public/ckeditor/skins/antares-theme/',
                protectedSource: [
                    /<\?[\s\S]*?\?>/g,
                    /\{%\s.+\s%\}/g
                ],
                toolbar: [
                    { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
                    { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ], items: [ 'Scayt' ] },
                    { name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
                    { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
                    { name: 'tools', items: [ 'Maximize' ] },
                    { name: 'document', groups: [ 'mode', 'document', 'doctools' ], items: [ 'Source' ] },
                    { name: 'others', items: [ '-' ] },
                    '/',
                    { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ] },
                    { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
                    { name: 'styles', items: [ 'Styles', 'Format' ] },
                    { name: 'about', items: [ 'About' ] }
                ]
            };
        },

        notificationType: function() {
            return this.notification.type;
        }
    },

    watch: {
        notificationType: function(value) {
            this.disabledContentTitle = (value.name === 'sms');
        }
    },

    methods: {
        eventChanged: function(value) {
            this.notification.event = value;

            if(value) {
                this.eventRecipients = value.recipients;
                this.eventVariables = value.variables;
            }
            else {
                this.eventRecipients = [];
                this.eventVariables = [];
            }
        },

        preview: function(url) {
            var
                modal = $('#notificationTemplatePreview'),
                container = $('.template-preview-container'),
                modalBody = container.parent(),
                data = {
                    type: this.notification.type.name,
                    title: this.contents[this.selected_lang].title,
                    content: this.contents[this.selected_lang].content
                };

            APP.modal.init({
                element: modal,
                title: modal.attr('title')
            });

            container.height(100);

            $.post(url, data)
                .done(function(response) {
                    modalBody.LoadingOverlay('hide');
                    container.html(response);

                    var
                        height = container.find('.preview-response').height(),
                        targetHeight = height + 50;

                    if (targetHeight > 600) {
                        targetHeight = 600;
                    }

                    container.height(targetHeight);
                    var iframe = document.createElement('iframe');
                    var frameborder = document.createAttribute("frameborder");
                    frameborder.value = 0;
                    iframe.setAttributeNode(frameborder);
                    var hght = document.createAttribute("height");
                    hght.value = '100%';
                    iframe.setAttributeNode(hght);
                    var wdth = document.createAttribute("width");
                    wdth.value = '100%';
                    iframe.setAttributeNode(wdth);
                    iframe.src = 'data:text/html;charset=utf-8,' + encodeURI(container.find('.preview-response').html());
                    container.html(iframe);
                })
                .fail(function (error) {
                    swal($.extend({}, APP.swal.cb1Error(), {
                        title: 'Error appear while generating template preview',
                        text: error.statusText,
                        html: error.statusText,
                        showConfirmButton: false,
                        showCancelButton: true,
                        cancelButtonText: 'Close',
                        closeOnCancel: true
                    }));

                    modalBody.LoadingOverlay('hide');
                });
        },

        sendTest: function(url) {
            var
                form = $('.send-test-notification').parents('form:first'),
                data = this.getPreparedNotification();

            data['test'] = true;

            form.LoadingOverlay('show');

            $.post(url, data)
                .done(function(response) {
                    form.LoadingOverlay('hide');

                    var type = response.type === 'success'
                        ? APP.swal.cb1Success()
                        : APP.swal.cb1Error();

                    swal($.extend({}, type, {
                        title: response.message,
                        text: '',
                        showConfirmButton: false,
                        showCancelButton: true,
                        cancelButtonText: 'Close',
                        closeOnCancel: true
                    }));
                })
                .fail(function(error) {
                    swal($.extend({}, APP.swal.cb1Error(), {
                        title: error.responseText,
                        text: '',
                        showConfirmButton: false,
                        showCancelButton: true,
                        cancelButtonText: 'Close',
                        closeOnCancel: true
                    }));

                    form.LoadingOverlay('hide');
                });
        },

        getPreparedNotification: function() {
            var notification = {};

            notification.name = this.notification.name;
            notification.source = this.notification.source;
            notification.type_id = this.notification.type ? this.notification.type.id : null;
            notification.category_id = this.notification.category ? this.notification.category.id : null;
            notification.severity_id = this.notification.severity ? this.notification.severity.id : null;
            notification.active = this.notification.active;
            notification.event = this.notification.event ? this.notification.event.event_class : null;
            notification.recipients = _.map(this.notification.recipients || [], 'id');
            notification.contents = this.contents;

            return notification;
        },

        send: function() {
            this.post(this.getPreparedNotification());
        }
    }
});
