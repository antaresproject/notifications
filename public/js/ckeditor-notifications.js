function CKConfiguration(isMobile) {
    this.isMobile = isMobile;

    this.common = {
        width: '100%',
        fullPage: false,
        allowedContent: true,
        extraPlugins: 'placeholder',
        protectedSource: [
            /<\?[\s\S]*?\?>/g,
            /{%\s.+\s%}/g
        ],
        toolbar: [
            {
                name: 'styles',
                items : [ 'Format','Font','FontSize' ]
            },
            {
                name: 'colors',
                items : [ 'TextColor','BGColor' ]
            },
            {
                name: 'basicstyles',
                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', 'RemoveFormat']
            },
            {
                name: 'paragraph',
                items: ['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv', '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl']
            },
            {
                name: 'links',
                items : [ 'Link' ]
            },
            {
                name: 'insert',
                items : [ 'Image','Table','HorizontalRule','SpecialChar','PageBreak' ]
            }
        ]
    };

    var mobileConfig = {
        height: '100%',
        skin: 'antares,skins/antares-theme/'
    };

    this.mobileConfig = {full: {}, mini: {}};

    this.desktopConfig = {
        full: {
            height: 600,
            skin: 'antares,/public/ckeditor/skins/antares-theme/'
        },

        mini: {
            height: 680,
            skin: 'antares,skins/antares-theme/'
        }
    };

    _.merge(this.mobileConfig.full, this.common, mobileConfig);
    _.merge(this.mobileConfig.mini, this.common, mobileConfig);

    _.merge(this.desktopConfig.full, this.common);
    _.merge(this.desktopConfig.mini, this.common);

    this.getFull = function() {
        return this.isMobile ? this.mobileConfig.full : this.desktopConfig.full;
    };

    this.getMini = function() {
        return this.isMobile ? this.mobileConfig.mini : this.desktopConfig.mini;
    };
}