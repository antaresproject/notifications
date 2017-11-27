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
        onlyText: false,
        category: null,

        eventRecipients: [],
        eventVariables: [],
        disabledContentTitle: false,
        form: null,
        _ckeConfig: {},
        currentTab: null
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
            this.notification.recipients = _(this.notification.event_model.recipients).keyBy('area').at(this.notification.recipients).value();
            this.notification.event = this.notification.event_model;
        }

        if(this.notification.category) {
            var category = this.notification.category;
            this.category = _.find(this.categories, function(_category) {
                return _category.id === category;
            });
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

        self.currentTab = this.form.find('.mdl-tabs__tab.is-active').attr('href');

        this.form.on('click', '.mdl-tabs__tab', function() {
            self.currentTab = $(this).attr('href');
        });
    },

    computed: {
        activeCategoryEvents: function() {
            if(this.category) {
                var name = this.category.id;

                return _.filter(this.events, function(event) {
                    return event.category_name === name;
                });
            }

            return [];
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
                config = new CKConfiguration(isMobile);

            this.disabledContentTitle = (value.name === 'sms');
            this.onlyText = _.indexOf(['alert', 'sms', 'notification'], value.name) >= 0;
            this._ckeConfig = this.onlyText ? config.getMini() : config.getFull();

            this._ckeConfig.removePlugins = 'resize,autogrow';

            if(this.onlyText) {
                this.currentTab = '#text-panel';
            }
            else {
                this.currentTab = '#wysiwyg-panel';
            }

            componentHandler.upgradeAllRegistered();
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

                if(self.currentTab === '#wysiwyg-panel') {
                    $('.page-notification-templates__content .mdl-tabs--open-variables-panel').removeClass('mdl-tabs--open-variables-panel');

                    editors.wysiwyg.instance.insertHtml(code);
                }
                else if(self.currentTab === '#html-panel') {
                    editors.html.instance.replaceSelection(code);
                }
            });
        },

        getCurrentEditors: function() {
            var lang = this.selectedLang;

            return {
                wysiwyg: this.$refs['editor-wysiwyg-' + lang][0],
                html: this.$refs['editor-html-' + lang][0],
                text: this.$refs['editor-text-' + lang][0]
            };
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

        preview: function(url) {
            var
                modal = this.$refs.previewModal,
                data = {
                    type: this.notification.type.name,
                    title: this.contents[this.selectedLang].title,
                    content: this.contents[this.selectedLang].content
                };

            modal.open();

            $.post(url, data)
                .done(function(response) {
                    modal.set(response.title, response.content);
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
                });
        },

        sendTest: function(url) {
            var
                self = this,
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

                    self.updateSidebar();
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
            notification.active = this.notification.active;
            notification.category = this.category ? this.category.id : null;
            notification.event = this.notification.event ? this.notification.event.event_class : null;
            notification.recipients = _.map(this.notification.recipients || [], 'area');
            notification.contents = this.contents;
            notification.lang_code = this.selectedLang;

            return notification;
        },

        send: function() {
            this.post(this.getPreparedNotification());
        },

        updateSidebar: function() {
            var url = $('.sidebar--notifications').data('url');
            $.ajax({
                url: url,
                success: function (response) {
                    var
                        $sidebar = $('aside.sidebar--notifications'),
                        notifications = response.notifications.items,
                        notificationsCount = response.notifications.count;

                    if (notifications.length > 0) {
                        $sidebar.find('.sidebar__footer').removeClass('hidden');
                        $sidebar.find('.sidebar__content .sidebar__list').html('');
                    }
                    for (var i = 0; i < notifications.length; ++i) {
                        $sidebar.find('.sidebar__content .sidebar__list').append(notifications[i]);
                    }

                    $('.sidebar__header .notification-counter').html(notifications.length);

                    $('#notification-counter')
                        .html(notificationsCount)
                        .attr('data-count', notificationsCount);

                    var
                        $sidebarAlerts = $('.sidebar--alerts'),
                        alerts = response.alerts.items,
                        alertsCount = response.alerts.count;

                    $('#main-alerts').parent().find('span.badge').html(alertsCount);

                    $sidebarAlerts.find('.badge').html(alerts.length);

                    if (alerts.length > 0) {
                        $sidebarAlerts.find('.sidebar__footer').removeClass('hidden');
                        $sidebarAlerts.find('.sidebar__content .sidebar__list').html('');
                    }
                    for (var i = 0; i < alerts.length; ++i) {
                        $sidebarAlerts.find('.sidebar__content .sidebar__list').append(alerts[i]);
                    }

                    $('.notification-item .flex-block__close', document).on('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var handler = $(this), url = $('.sidebar .sidebar__content:first').data('delete'), id = handler.closest('.notification-item').data('id'), badge = handler.closest('.sidebar').find('.badge');
                        handler.closest('.flex-block').remove();
                        var count = parseInt(badge.text());
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {id: id},
                            success: function (response) {
                                if (count <= 0) {
                                    return
                                }
                                badge.text(count - 1);
                                if ((count - 1) <= 0) {
                                    $('.sidebar__footer').addClass('hidden');
                                }
                            }
                        });
                    });

                    $('aside.sidebar--notifications .sidebar__header-right .btn-more', document).on('click', function (e) {
                        $sidebar.removeClass('sidebar--open');
                    });
                }
            });
        }
    }
});
