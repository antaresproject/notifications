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
        langs: [],
        selectedLang: null,

        eventRecipients: [],
        eventVariables: [],
        disabledContentTitle: false,
        form: null,
        _ckeConfig: {}
    },

    created: function() {
        this.form = $('#form-notification');

        this.categories = this.form.data('provider-categories');
        this.types = this.form.data('provider-types');
        this.severities = this.form.data('provider-severities');
        this.events = this.form.data('provider-events');
        this.contents = this.form.data('provider-contents');
        this.variables = this.form.data('provider-variables');
        this.notification = this.form.data('provider-notification');
        this.langs = this.form.data('provider-langs');
        this.selectedLang = this.form.data('provider-selected-lang');
        this.actionUrl = this.form.attr('action');

        if(this.notification.recipients && this.notification.event_model) {
            this.notification.recipients = _(this.notification.event_model.recipients).keyBy('id').at(this.notification.recipients).value();
            this.notification.event = this.notification.event_model;
        }
    },

    mounted: function() {
        var self = this;

        this.form = $('#form-notification');

        this.form.find('.notification-template-preview').click(function(e) {
            var url = $(this).attr('href');

            self.preview(url);

            e.preventDefault();
        });

        this.form.find('.send-test-notification').click(function(e) {
            var url = $(this).attr('href');

            self.sendTest(url);

            e.preventDefault();
        });

        $('#contents-languages-dropdown').on('change', function() {
            Vue.set(self, 'selectedLang', $(this).val());
        });

        this.initVariables();
        this.initCodeMirror();

        enquire.register("screen and (max-width:767px)", {
            match: function () {
                var config = new CKConfiguration(true);

                self._ckeConfig = self.disabledContentTitle ? config.getMini() : config.getFull();
                self._ckeConfig.removePlugins = 'resize,autogrow';
            },
            unmatch: function () {
                var config = new CKConfiguration(false);

                self._ckeConfig = self.disabledContentTitle ? config.getMini() : config.getFull();
                self._ckeConfig.removePlugins = 'resize,autogrow';
            }
        });
    },

    computed: {
        langsOptions: function() {
            var langs = _.clone(this.langs);

            return _.map(langs, function(lang) {
                lang.icon_code = lang.code;

                if(lang.code === 'en') {
                    lang.icon_code = 'us';
                }

                return lang;
            });
        },

        codeMirrorConfig: function() {
            return {
                theme: 'ambiance',
                lineNumbers: true,
                lineWrapping: true,
                styleActiveLine: true,
                matchBrackets: true,
                scrollbarStyle: 'overlay',
                readOnly: false,
                matchTags: {
                    bothTags: true
                },
                mode: 'xml',
                htmlMode: true
            };
        },

        notificationType: function() {
            return this.notification.type;
        },

        ckeConfig: function() {
            var
                isMobile = !$(window).width() > 768,
                config = new CKConfiguration(isMobile);

            this._ckeConfig = this.disabledContentTitle ? config.getMini() : config.getFull();

            this._ckeConfig.removePlugins = 'resize,autogrow';

            return this._ckeConfig;
        }
    },

    watch: {
        notificationType: function(value) {
            var
                isMobile = !$(window).width() > 768,
                config = new CKConfiguration(isMobile),
                isSms = (value.name === 'sms');

            this.disabledContentTitle = isSms;
            this._ckeConfig = isSms ? config.getMini() : config.getFull();

            this._ckeConfig.removePlugins = 'resize,autogrow';
        }
    },

    methods: {
        initVariables: function() {
            this.form.find('.mdl-tabs--open-variables-panel').on('click', function (e) {
                $(this).closest('.mdl-tabs').addClass('mdl-tabs--open-variables-panel');

                e.preventDefault();
            });

            this.form.find('.mdl-tabs--close-variables-panel, .variables-pane__mobile-button a').on('click', function (e) {
                $(this).closest('.page-notification-templates').find('.mdl-tabs--open-variables-panel').removeClass('mdl-tabs--open-variables-panel');

                e.preventDefault();
            });

            var $panes = this.form.find('.variables-pane .variables-pane__inner');

            new Clipboard('.variables-pane .variables-pane__copy');

            $panes.perfectScrollbar({
                wheelPropagation: true
            });

            $(window).on('reszie', function () {
                $panes.perfectScrollbar('update');
            });

            $('a', '.left-tabs__desktop-links').on('resize', function (e) {
                e.preventDefault();
            });

            var self = this;

            $panes.find('.variables-pane__paste').on('click', function () {
                var
                    code = $(this).data('variable-code'),
                    editors = self.getCurrentEditors();

                if ($('a[href="#wysiwyg-panel"]', '.left-tabs').hasClass("is-active")) {
                    $('.page-notification-templates__content .mdl-tabs--open-variables-panel').removeClass('mdl-tabs--open-variables-panel');

                    editors.wysiwyg.instance.insertHtml(code);
                }
                else {
                    editors.html.instance.replaceSelection(code);
                }
            });
        },

        getCurrentEditors: function() {
            var lang = this.selectedLang;

            return {
                wysiwyg: this.$refs['editor-wysiwyg-' + lang][0],
                html: this.$refs['editor-html-' + lang][0]
            };
        },

        initCodeMirror: function() {
            var self = this;

            this.form.find('.left-tabs .mdl-tabs__tab').on('click', function () {
                setTimeout(function () {
                    self.getCurrentEditors().html.refresh();
                }, 1);
            });
        },

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

        showVariables: function() {

        },

        preview: function(url) {
            var
                modal = $('#notificationTemplatePreview'),
                container = $('.template-preview-container'),
                modalBody = container.parent(),
                data = {
                    type: this.notification.type.name,
                    title: this.contents[this.selectedLang].title,
                    content: this.contents[this.selectedLang].content
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
                        height = container.find('.preview-response').height() || 450,
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
