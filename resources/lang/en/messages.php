<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Notifications
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */
return [
    'notification_templates'              => 'Notification Templates',
    'notification_templates_create'       => 'Create Notification Template',
    'notification_templates_edit'         => 'Edit: #:id :name',
    'title'                               => 'Title',
    'language'                            => [
        'en' => 'English',
        'pl' => 'Polish'
    ],
    'datatables'                          => [
        'select_category' => 'Category',
        'select_type'     => 'Type',
        'select_recipient' => 'Recipient',
    ],
    'sidebar'                             => [
        'unable_to_delete_notification_item'   => 'Unable to delete notification item.',
        'unable_to_mark_notifications_as_read' => 'Unable to mark notifications as read.'
    ],
    'widget_unable_to_send'               => 'Unable to send notification. Notification object not exists.',
    'widget_notification_added_to_queue'  => 'Notification has been added to send queue.',
    'notification_view_not_exists'        => 'Notification view file :path, not exists',
    'notification_view_is_not_set'        => 'Notification view is not set',
    'notifications_has_been_synchronized' => 'Notifications has been synchronized.',
    'notifications_sync_completed_line'   => 'Sync completed.',
    'generating_notification_preview'     => 'Generating notification preview...',
    'template_preview_title'              => 'Notification preview',
    'modal_close_title'                   => 'Close',
    'notification_name'                   => 'Name',
    'notification_type'                   => 'Type',
    'notification_recipients'             => 'Recipients',
    'notification_category'               => 'Category',
    'notification_events_category'        => 'Event Category',
    'notification_severity'               => 'Severity',
    'notification_event'                  => 'Event',
    'notification_enabled'                => 'Enabled',
    'notification_enable'                 => 'Enable',
    'notification_disable'                => 'Disable',
    'notification_published'              => 'Published',
    'notification_template_info'          => 'Template Info',
    'notification_template_content'       => 'Template Content',
    'notification_edit_for'               => 'Edit template for',
    'notification_available_variables'    => 'Available Variables',
    'notification_send_test'              => 'Send Test',
    'notification_preview'                => 'Preview',
    'notification_send_preview'           => 'Send preview',
    'notification_delete'                 => 'Delete',
    'notification_insert'                 => 'Insert',
    'notification_copy'                   => 'Copy',
    'notification_delete_success'         => 'Notification has been deleted.',
    'notification_delete_failed'          => 'Notification has not been deleted.',
    'notification_create_success'         => 'Notification has been created.',
    'notification_create_failed'          => 'Notification has not been created.',
    'notification_update_success'         => 'Notification has been updated.',
    'notification_update_failed'          => 'Notification has not been updated.',
    'notification_change_status_failed'   => 'Notification status has not been changed.',
    'notification_change_status_success'  => 'Notification status has been changed.',
    'notification_mass_enabled_success'   => 'Notifications successfully enabled.',
    'notification_mass_disabled_success'  => 'Notifications successfully disabled.',
    'notification_mass_enabled_failed'    => 'Cannot enable notifications due error.',
    'notification_mass_disabled_failed'   => 'Cannot disable notifications due error.',
    'notification_mass_not_selected'      => 'No notifications have been selected.',
    'notification_preview_error'          => 'Errors appear while sending notification preview.',
    'notification_preview_sent'           => 'Notification preview has been sent.',
    'notification_content_legend'         => 'Notification content for ":lang"',
    'notification_content_title'          => 'Title',
    'notification_content_content'        => 'Content',
    'notification_content_subject'        => 'Subject',
    'notification_content_enabled'        => 'Enabled',

    'modals' => [
        'general_prompt'    => 'Are you sure?',
        'change_status'     => 'Changing status of notification #:id :name',
        'send_preview'      => 'Sending preview of notification #:id :name',
        'delete'            => 'Deleting notification #:id :name'
    ],

];
