# Changelog


## [0.9.2]

##### New

* Descriptions to notification form. 
* Packages version. 
* Comments to methods and properties. ADD: license to files. 
* Backend integration with new frontend. 
* Unit tests ADD: check event class type FIX: get handler type. 
* Review project for missing frontend changes. 
* Notification validation message for content. 
* Notifiable event builder as helper. CHG: CKE editor toolbars. 
* Backend integration with new frontend. 
* Unit tests CHG: back to the previous variable handle logic. Without this the twig variables are not passed correctly. CHG: removed static properties from services CHG: moved notification listener to the "booted" method. 
* Some unit tests CHG: unused method arguments CHG: clean code. 
* Frontend integration. 
* Backups. 
* Support for alerts, sms and simple notifications channels. 
* Support for Laravel notifications. 
* Add notification listeners. 
* Notificaitons import command. 
* Notification channels. 
* Extend notification templates. 
* New module structure. 
* Views. 
* Readme.md, FIX: small fixes. 
* Documenation supplemenation. 
* From stash. 
* Missing translations. 
* Refactoring unit tests. 
* Laravel 5.4 integration. 
* Laravel 5.3 integration. 
* New composer.json structure. 
* New composer.json structure. 
* Release 0.9.2. 
* Release 0.9.2|merge. 
* Notification logs auto remover. 
* Notification logs. 
* Notification logs. 
* Notification logs. 

##### Changes

* Commented code for later investigation. 
* Store system notifications as compiled. 
* Link to create a new notification template as zero data for datatables. 
* Removed redundant type declarations CHG: widget notification CSS classes. 
* Notification data table filter. 
* Changes in recipients logic, helpers to build notifications ADD: events categories CHG: removed old categories from notifications. 
* Travis config file. 
* Only text editor for notifications and alerts FIX: send exception alert only for notifications which are not testable. 
* Removed invalid unit tests ADD: alert notification report for exceptions. 
* Preview modal changes in styles and title. 
* Preview as VUE component. 
* Form controls ADD: simple text for SMS type. 
* Save notifications for other locales if controls are empty. 
* Moved errors. 
* Brand template decorator for emails. 
* Execute notifications and alerts sidebar after send tests. 
* Notification widget, form controls. 
* Moved vue form builder from core FIX: content attribute for notification without source. 
* Removed PHP 7 strict type. 
* Remove returning types. 
* Form view CHG: namespaces for external classes. 
* Integration for new grid layout and frontend improvements. 
* No commit message. 

  - Refactorization to handle own events which can be chosen in notification form.
  - Assign own configured recipients to event.
  - Form built with Vue JS.
  - Possibility to built custom handler for notification event.
  - Minor fixes.

* Notifications synchronizer - update of notification content. 
* Remove unused classes. 
* Minor and major changes. 
* :book: update of README.md. 
* Large refactorization about building notification templates which includes variables declaration, Laravel notification class structure, fixes for datatables. 
* Large refactorization about building notification templates which includes variables declaration, Laravel notification class structure, fixes for datatables. 
* Phpunit tests refactoring. 
* Composer.json for travis-vi builds. 
* Composer.json - add description of module, reorder in README.md. 
* Composer.json - installer plugin version update. Description of module in README.md. 
* Antares Project -> Antares. 
* Installer plugin version. 
* Readme and changelog files. 
* Readme.md structure change. 
* Change form fields dimensions, DEL: remove unused watchdog after install. 
* Change default notification areas. 
* Added homepage and friendly names to components. 
* Notifications unit tests refactoring. 
* Updated ACL file. 
* Main menu title. 

##### Fixes

* Fade timeout for dropdowns FIX: send notification or alert when source is null. 
* Exception notification. 
* Breadcrumb CHG: notification data method accessibility. 
* Styles for form. 
* Long fade effect for dropdowns. 
* Should send only notification object. 
* Wrong category passed on form. 
* JS error for CKEditor. 
* Poor performance between notification editors. 
* Dispatching events for notifications FIX: notification sending CHG: event label will be get from class name. 
* Padding for widget. 
* JS wrong method. 
* Empty template FIX: error for not found extension in import command ADD: unit tests for HTTP (broken due core right now) 
* Cke editor instance rebuild. 
* Exception notification FIX: wrong alert type checker. 
* Config. 
* Sending notification test for types of notification and alert. 
* Height calculation for template preview. 
* Tabs active state. 
* Sending SMS notifications CHG: general notification sender CHG: returned exception message. 
* Getting only required variables for parsing ADD: unit tests. 
* Translations FIX: notification alert type callback FIX: send preview from data table. 
* Class namespace ADD: missing JS file. 
* Class namespace. 
* Languages dropdown and i18n contents. 
* Unresolvable class inside notification form. 
* Remove unused table column. 
* Depracated 'lists' method to new one - 'pluck' 

##### Other

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 
* Update composer.json. 
* Merge pull request #7 from antaresproject/0.9.2-laravel5.5. 
* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 
* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	.travis.yml
  #	src/Helpers/NotificationsEventHelper.php
  #	src/Http/Controllers/Admin/IndexController.php
  #	src/Notifications/ExceptionNotification.php
  #	src/Services/VariablesService.php

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 
* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	resources/views/admin/index/_content.twig
  #	src/Model/NotifiableEvent.php
  #	src/Services/VariablesService.php
  #	tests/Http/Controllers/Admin/IndexControllerTest.php

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 
* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	public/js/notification-form.js
  #	resources/lang/en/messages.php
  #	resources/views/admin/index/_info.twig
  #	src/Channels/MailChannel.php
  #	src/Channels/NotificationChannel.php
  #	src/Decorator/SidebarItemDecorator.php
  #	src/Http/Controllers/Admin/IndexController.php
  #	src/Http/Datatables/LogsDataTable.php
  #	src/Http/Datatables/NotificationsDataTable.php
  #	src/Http/Form/NotificationForm.php
  #	src/Model/NotifiableEvent.php
  #	src/NotificationsServiceProvider.php
  #	src/Parsers/ContentParser.php
  #	src/Processor/IndexProcessor.php
  #	src/Processor/LogsProcessor.php
  #	src/Repository/Repository.php
  #	src/Widgets/NotificationSender/Controller/NotificationController.php

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 

  # Conflicts:
  #	src/Model/NotifiableEvent.php

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 

  # Conflicts:
  #	src/Collections/TemplatesCollection.php
  #	src/Services/ModuleVariables.php
  #	src/Services/VariablesService.php

* Merge remote-tracking branch 'origin/0.9.2' into 0.9.2. 

  # Conflicts:
  #	public/js/ckeditor-notifications.js
  #	public/js/default.js
  #	resources/views/admin/edit.twig
  #	resources/views/admin/index/edit.twig
  #	src/Http/Filter/NotificationNameFilter.php
  #	src/Listener/ConfigurationListener.php

  Vue form integration

* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	resources/lang/en/messages.php
  #	resources/views/admin/index/form.twig
  #	src/Http/Form/Form.php
  #	src/Processor/IndexProcessor.php

* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	resources/lang/en/messages.php
  #	src/ChannelManager.php
  #	src/Channels/MailChannel.php
  #	src/Channels/NotificationChannel.php
  #	src/Channels/SmsChannel.php
  #	src/Console/NotificationsImportCommand.php
  #	src/Decorator/SidebarItemDecorator.php
  #	src/Http/Datatables/Notifications.php
  #	src/Http/Presenters/IndexPresenter.php
  #	src/Listener/NotificationsListener.php
  #	src/Messages/MailMessage.php
  #	src/Messages/NotificationMessage.php
  #	src/Messages/SimpleMessage.php
  #	src/Messages/SmsMessage.php
  #	src/NotificationsServiceProvider.php
  #	src/Synchronizer.php
  #	src/Variables.php

* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2. 

  # Conflicts:
  #	resources/lang/en/messages.php
  #	src/ChannelManager.php
  #	src/Channels/MailChannel.php
  #	src/Channels/NotificationChannel.php
  #	src/Channels/SmsChannel.php
  #	src/Console/NotificationsImportCommand.php
  #	src/Decorator/SidebarItemDecorator.php
  #	src/Http/Datatables/Notifications.php
  #	src/Http/Presenters/IndexPresenter.php
  #	src/Listener/NotificationsListener.php
  #	src/Messages/MailMessage.php
  #	src/Messages/NotificationMessage.php
  #	src/Messages/SimpleMessage.php
  #	src/Messages/SmsMessage.php
  #	src/NotificationsServiceProvider.php
  #	src/Synchronizer.php
  #	src/Variables.php

* Update composer.json. 
* Merge pull request #5 from antaresproject/0.9.2.2. 

  0.9.2.2

* Merge pull request #4 from antaresproject/0.9.2.2. 

  0.9.2.2

* Merge pull request #3 from antaresproject/0.9.2.1. 

  0.9.2.1

* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2.1. 
* Merge branch '0.9.2' of https://github.com/antaresproject/notifications into 0.9.2.1. 
* Update composer.json. 
* Update composer.json. 
* Fix for new composer handler. 
* Notifications configuration should be moved to the System configuration page. 
* Merge pull request #1 from Germanaz0/bugfix/composer-init. 

  Changed project name on composer

* Changed project name on composer. 
* INITIAL ANTARES COMMIT. 
* Initial commit. 

