
(function () {
    function CKConfiguration(isMobile) {
        this.isMobile = isMobile,
                this.mobileCkeConfiguration = {
                    email: {
                        height: '100%',
                        width: '100%',
                        fullPage: true,
                        allowedContent: true,
                        skin: 'antares,skins/antares-theme/',
                        toolbar: [
                            ['Bold', 'Italic', 'Underline', 'Strike', 'NumberedList', 'BulletedList', 'SpellChecker', 'Maximize']
                        ],

                        toolbarGroups: [],
                        removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
                    },
                    sms: {
                        height: '100%',
                        width: '100%',
                        fullPage: true,
                        allowedContent: true,
                        skin: 'antares,skins/antares-theme/',
                        toolbar: [
                            ['Bold', 'Italic', 'Underline', 'Strike', 'NumberedList', 'BulletedList', 'SpellChecker', 'Maximize']
                        ],
                        toolbarGroups: [],
                        removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
                    }
                },
                this.desktopCkeConfiguration = {
                    email: {
                        height: 600,
                        width: '100%',
                        fullPage: true,
                        allowedContent: true,
                        skin: 'antares,/public/ckeditor/skins/antares-theme/'
                    },
                    sms: {
                        height: 680,
                        width: '100%',
                        fullPage: true,
                        allowedContent: true,
                        skin: 'antares,skins/antares-theme/',
                        toolbarGroups: [],
                        removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
                    }
                },
                this.getActiveState = function () {
                    var isSms = $('.grid--notification form:first').data('is-sms');
                    var config = (this.isMobile) ? this.mobileCkeConfigurationthis : this.desktopCkeConfiguration;
                    if (isSms) {
                        return config.sms;
                    }
                    return config.email;
                }
    }
    ;
//    var clipboard = require('clipboard');
//    window.Clipboard = clipboard;



    var notificationTemplates = {

        configuration: null,
        mirrorHTMLEditor: [],
        init: function () {
            this.initCodeMirror();
            var isMobile = !$(window).width() > 768;

            this.configuration = new CKConfiguration(isMobile);


            if (isMobile) {
                this.initMobileCKE();
            } else {
                this.initCKEditor();
            }
            this.reszie();
            this.clipboard();
            this.variablesPanel();
            this.copyFromHTMLEditor();
            this.refreshCodeMirror();
            this.insertVariable();
            this.onSelectLang();

        },

        variablesPanel: function () {
            $('.mdl-tabs .mdl-tabs--open-variables-panel').on('click', function () {
                $(this).closest('.mdl-tabs').addClass('mdl-tabs--open-variables-panel');
            });
            $('.mdl-tabs--close-variables-panel, .variables-pane__mobile-button a').on('click', function () {
                $(this).closest('.page-notification-templates').find('.mdl-tabs--open-variables-panel').removeClass('mdl-tabs--open-variables-panel');
            });

            $('.variables-pane .variables-pane__inner').perfectScrollbar({
                wheelPropagation: true
            });

            $(window).on('reszie', function () {
                $('.variables-pane .variables-pane__inner').perfectScrollbar('update');
            });
            $('.left-tabs__desktop-links a').on('resize', function (e) {
                e.preventDefault();
            });

        },

        bindCKEditor: function () {
            var self = this, $return = [];
            $('textarea.has-ckeditor').each(function (index, item) {
                var textarea = $(item), name = textarea.attr('name');



                if (CKEDITOR.instances[name]) {
                    CKEDITOR.instances[name].destroy();
                }
                CKEDITOR.replace(name, self.configuration.getActiveState());
                $return.push(name);
            });
            return $return;
        },

        initCKEditor: function () {

            var names = this.bindCKEditor();
            names.forEach(function (name) {
                CKEDITOR.instances[name].on('blur', function (event) {
                    var data = CKEDITOR.instances[name].getData();
                    $("#html-editor").html(data).change();
                });
            });
            CKEDITOR.config.removePlugins = 'resize,autogrow';

            CKEDITOR.on('instanceReady', function () {
                $('.variables-pane .variables-pane__inner').perfectScrollbar({
                    wheelPropagation: true
                });
            });
        },
        initMobileCKE: function () {
            this.bindCKEditor();
        },
        initCodeMirror: function () {
            var self = this;
            $('[name=html-editor]').each(function (index, editor) {

                var id = $(editor).attr('id'), htmlEditor = document.getElementById(id);
                var CM_cfg = {
                    theme: 'ambiance',
                    lineNumbers: true,
                    lineWrapping: true,
                    styleActiveLine: true,
                    matchBrackets: true,
                    scrollbarStyle: "overlay",
                    readOnly: false,
                    matchTags: {bothTags: true}
                };
                self.mirrorHTMLEditor.push(CodeMirror.fromTextArea(htmlEditor, $.extend({}, CM_cfg, {mode: "xml", htmlMode: true, })));
            })

        },
        copyFromHTMLEditor: function () {
            var self = this;
            self.mirrorHTMLEditor.forEach(function (editor) {
                var rel = $(editor.getTextArea()).attr('rel');
                editor.on('blur', function () {
                    var data = editor.getValue();
                    CKEDITOR.instances[rel].setData(data)
                });
            });

        },
        insertVariable: function () {
            self = this;
            $('.variables-pane .variables-pane__paste').on('click', function () {
                var $dataPre = $(this).closest('.variables-pane__instruction-content').find('pre');
                var content = $dataPre.text();

                if ($(".left-tabs a[href='#wyswig-panel']").hasClass("is-active")) {
                    $(".page-notification-templates__content .mdl-tabs--open-variables-panel").removeClass('mdl-tabs--open-variables-panel');
                    var id = $dataPre.attr('id');
                    CKEDITOR.instances.wysiwyg.insertHtml(content);
                } else {
                    self.mirrorHTMLEditor.replaceSelection(content.trim());

                    var data = self.mirrorHTMLEditor.getValue();
                    CKEDITOR.instances.wysiwyg.setData(data);
                }
            });
        },
        refreshCodeMirror: function () {
            var self = this;
            self.mirrorHTMLEditor.forEach(function (editor) {
                var textarea = $(editor.getTextArea());
                $(textarea).on('change', function () {
                    editor.doc.setValue($(this).val());
                });
            });

            $('.left-tabs .mdl-tabs__tab').on('click', function () {
                setTimeout(function () {
                    self.mirrorHTMLEditor.forEach(function (editor) {
                        editor.refresh();
                    });

                }, 1);
            });

        },
        clipboard: function () {

            //new Clipboard('.variables-pane .variables-pane__copy');

        },
        reszie: function () {
            self = this;
            enquire.register("screen and (max-width:767px)", {
                match: function () {
                    self.initMobileCKE();
                },
                unmatch: function () {
                    self.initCKEditor();
                }
            });
        },
        onSelectLang: function () {
            $('.lang-selector').on("select2:select", function (e) {
                var selected = $(this).val();
                $('.grid--notification [rel]').addClass('hidden');
                $('.grid--notification [rel=' + selected + ']').removeClass('hidden');

            });
        }
    }

    $(function () {
        notificationTemplates.init();
    });


}).call(this);