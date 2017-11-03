function CKConfiguration(isMobile) {
    this.isMobile = isMobile;

    this.mobileConfig = {
        full: {
            height: '100%',
            width: '100%',
            fullPage: false,
            allowedContent: true,
            skin: 'antares,skins/antares-theme/',
            protectedSource: [
                /<\?[\s\S]*?\?>/g,
                /\{%\s.+\s%\}/g
            ],
            toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike', 'NumberedList', 'BulletedList', 'SpellChecker', 'Maximize']
            ],

            toolbarGroups: [],
            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
        },
        mini: {
            height: '100%',
            width: '100%',
            fullPage: false,
            allowedContent: true,
            skin: 'antares,skins/antares-theme/',
            protectedSource: [
                /<\?[\s\S]*?\?>/g,
                /\{%\s.+\s%\}/g
            ],
            toolbar: [
                ['Bold', 'Italic', 'Underline', 'Strike', 'NumberedList', 'BulletedList', 'SpellChecker', 'Maximize']
            ],
            toolbarGroups: [],
            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar'
        }
    };

    this.desktopConfig = {
        full: {
            height: 600,
            width: '100%',
            fullPage: false,
            allowedContent: true,
            skin: 'antares,/public/ckeditor/skins/antares-theme/',
            protectedSource: [
                /<\?[\s\S]*?\?>/g,
                /\{%\s.+\s%\}/g
            ]
        },
        mini: {
            height: 680,
            width: '100%',
            fullPage: false,
            allowedContent: true,
            skin: 'antares,skins/antares-theme/',
            toolbarGroups: [],
            removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar',
            protectedSource: [
                /<\?[\s\S]*?\?>/g,
                /\{%\s.+\s%\}/g
            ]
        }
    };

    this.getFull = function() {
        return this.isMobile ? this.mobileConfig.full : this.desktopConfig.full;
    };

    this.getMini = function() {
        return this.isMobile ? this.mobileConfig.mini : this.desktopConfig.mini;
    };
}