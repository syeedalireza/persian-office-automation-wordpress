<?php
/**
 * Incoming Letter Controller
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verified in form handlers; GET used for display only and sanitized.
 * @package OfficeAutomation
 */

namespace OfficeAutomation\Presentation\Controllers;

use OfficeAutomation\Application\DTO\IncomingLetterDTO;
use OfficeAutomation\Application\Services\CorrespondenceService;
use OfficeAutomation\Application\Services\CartableService;
use OfficeAutomation\Infrastructure\Repository\CorrespondenceRepository;

class IncomingLetterController {
    
    private $service;
    
    public function __construct() {
        $repository = new CorrespondenceRepository();
        $this->service = new CorrespondenceService($repository);
    }
    
    /**
     * Handle form submission
     */
    public function handleSubmit() {
        // Check nonce
        if (!isset($_POST['persian_oa_incoming_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_incoming_nonce'])), 'persian_oa_save_incoming_letter')) {
            wp_die('امنیت فرم تایید نشد.');
        }
        
        // Check permissions (oa_create_letter is assigned by RoleService; manage_options for WP admins)
        if (!current_user_can('oa_create_letter') && !current_user_can('manage_options')) {
            wp_die('شما مجوز ایجاد نامه ندارید.');
        }
        
        // Handle file uploads
        $attachments = $this->handleFileUploads();
        
        // Create DTO
        $dto = IncomingLetterDTO::fromRequest($_POST);
        $dto->attachments = $attachments;
        
        // Check if update or create
        if (!empty($dto->id)) {
            // Update
            if (!current_user_can('oa_edit_letter') && !current_user_can('manage_options')) {
                wp_die('شما مجوز ویرایش نامه ندارید.');
            }
            $result = $this->service->updateIncomingLetter($dto);
        } else {
            // Create
            $result = $this->service->createIncomingLetter($dto);
        }
        
        // Redirect with message
        if ($result['success']) {
            $redirect_url = add_query_arg([
                'page' => 'persian-oa-incoming-letters',
                'message' => 'success'
            ], admin_url('admin.php'));
            
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            // Store errors in transient
            set_transient('persian_oa_form_errors_' . get_current_user_id(), $result['errors'], 60);
            
            $redirect_url = add_query_arg([
                'page' => 'persian-oa-incoming-letters',
                'action' => 'new',
                'error' => '1'
            ], admin_url('admin.php'));
            
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Handle referral submission
     */
    public function handleReferral() {
        // Check nonce
        if (!isset($_POST['persian_oa_referral_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_referral_nonce'])), 'persian_oa_save_referral')) {
            wp_die('امنیت فرم تایید نشد.');
        }

        // Check permissions
        if (!current_user_can('oa_manage_all_letters') && !current_user_can('manage_options')) {
            wp_die('شما مجوز ارجاع نامه ندارید.');
        }

        $correspondence_id = isset( $_POST['correspondence_id'] ) ? absint( $_POST['correspondence_id'] ) : 0;
        $to_user = isset( $_POST['to_user'] ) ? absint( $_POST['to_user'] ) : 0;
        $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
        
        if (!$correspondence_id || !$to_user) {
            wp_die('اطلاعات نامعتبر است.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'persian_oa_referrals';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix.
        $result = $wpdb->insert($table, [
            'correspondence_id' => $correspondence_id,
            'from_user' => get_current_user_id(),
            'to_user' => $to_user,
            'referral_type' => 'forward',
            'comments' => $message,
            'status' => 'pending',
            'referred_at' => current_time('mysql')
        ]);
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

        if ($result) {
            $redirect_url = add_query_arg([
                'page' => 'persian-oa-incoming-letters',
                'action' => 'view',
                'id' => $correspondence_id,
                'message' => 'referred'
            ], admin_url('admin.php'));
            
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            wp_die('خطا در ثبت ارجاع.');
        }
    }
    
    /**
     * Render form page
     */
    public function renderForm() {
        // Check permissions (GET used for display only; nonce not required for view).
        $letter_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        
        if ($letter_id) {
            // Check edit permission
            if (!current_user_can('oa_edit_letter') && !current_user_can('manage_options')) {
                wp_die('شما مجوز ویرایش نامه ندارید.');
            }
        } else {
            // Check create permission
            if (!current_user_can('oa_create_letter') && !current_user_can('manage_options')) {
                wp_die('شما مجوز ایجاد نامه ندارید.');
            }
        }
        
        $letter = null;
        $cc_recipients = [];
        $attachments = [];
        
        if ($letter_id) {
            $letter = $this->service->getIncomingLetter($letter_id);
            if (!$letter) {
                wp_die('نامه مورد نظر یافت نشد.');
            }
            
            // Get CC recipients
            $cc_recipients = $this->getCCRecipients($letter_id);
            
            // Get attachments
            $attachments = $this->getAttachments($letter_id);
        }
        
        // Get errors from transient (ensure array for view)
        $errors = get_transient('persian_oa_form_errors_' . get_current_user_id());
        if ($errors) {
            delete_transient('persian_oa_form_errors_' . get_current_user_id());
        }
        if (!is_array($errors)) {
            $errors = [];
        }
        
        // Generate next number if new
        $next_number = '';
        if (!$letter_id) {
            $next_number = $this->service->generateNextIncomingNumber('IN-');
        }
        
        // Get all users for recipient selection
        $users = get_users(['role__in' => ['administrator', 'oa_manager', 'oa_staff']]);
        
        // Check for view-only mode
        $is_view_only_mode = false;
        
        // 1. Explicit view action (GET sanitized; nonce not required for read-only display).
        $get_action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        if ( $get_action === 'view' ) {
            $is_view_only_mode = true;
        }
        
        // 2. Permission check (if not creator and not super editor)
        if ($letter_id && $letter) {
            $is_creator = ($letter->getCreatedBy() == get_current_user_id());
            $can_edit_others = current_user_can('oa_edit_letter') || current_user_can('manage_options');
            
            if (!$is_creator && !$can_edit_others) {
                $is_view_only_mode = true;
            }
        }
        
        if ($is_view_only_mode) {
            // Mark as read when a recipient views the letter (so "خوانده شده" on sent page is correct)
            if ($letter_id && $letter) {
                $currentUserId = get_current_user_id();
                $isRecipient = ( (int) $letter->getPrimaryRecipient() === $currentUserId )
                    || in_array( $currentUserId, array_map( 'intval', $cc_recipients ), true )
                    || in_array( $currentUserId, array_map( 'intval', $this->getReferralRecipientIds( $letter_id ) ), true );
                if ( $isRecipient ) {
                    CartableService::markAsRead( $letter_id, $currentUserId );
                }
            }
            require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/incoming-view.php';
            return;
        }
        
        // Load view
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/incoming-form.php';
    }
    
    /**
     * Handle file uploads. Nonce verified in handleSubmit() before this is called.
     *
     * phpcs:disable WordPress.Security.NonceVerification.Missing -- Caller verifies nonce.
     */
    private function handleFileUploads() {
        $attachments = [];
        if ( empty( $_FILES['attachments'] ) || ! is_array( $_FILES['attachments']['name'] ?? null ) ) {
            return $attachments;
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $max_size = get_option( 'persian_oa_max_upload_size', 10 ) * 1024 * 1024;
        $allowed_types = get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated in loop (error, size, type, name).
        $files = $_FILES['attachments'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                // Check file size
                if ( $file['size'] > $max_size ) {
                    $max_mb = (int) get_option( 'persian_oa_max_upload_size', 10 );
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- max_mb and file name passed through esc_html in sprintf.
                    wp_die( esc_html( sprintf( 'فایل "%s" بیش از حد مجاز %s مگابایت است.', esc_html( $file['name'] ), esc_html( (string) $max_mb ) ) ) );
                }
                $file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
                if ( ! in_array( $file_ext, $allowed_types, true ) ) {
                    $allowed_list = array_map( 'esc_html', array_map( 'strval', $allowed_types ) );
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- file name and allowed_list escaped above.
                    wp_die( esc_html( sprintf( 'نوع فایل "%s" مجاز نیست. فرمت‌های مجاز: %s', esc_html( $file['name'] ), implode( ', ', $allowed_list ) ) ) );
                }
                
                // Additional MIME type validation for security
                $allowed_mimes = $this->getAllowedMimeTypes($allowed_types);
                
                $upload = wp_handle_upload($file, [
                    'test_form' => false,
                    'mimes' => $allowed_mimes
                ]);
                
                if (isset($upload['error'])) {
                    wp_die('خطا در آپلود فایل: ' . esc_html($upload['error']));
                }
                
                if (isset($upload['file'])) {
                    $attachments[] = [
                        'name' => basename($upload['file']),
                        'path' => $upload['file'],
                        'type' => $upload['type'],
                        'size' => $file['size']
                    ];
                }
            }
        }
        
        return $attachments;
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing

    /**
     * Get allowed MIME types based on file extensions
     */
    private function getAllowedMimeTypes($extensions) {
        $mime_map = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'zip' => 'application/zip',
        ];
        
        $allowed = [];
        foreach ($extensions as $ext) {
            if (isset($mime_map[$ext])) {
                $allowed[$ext] = $mime_map[$ext];
            }
        }
        
        return $allowed;
    }
    
    /**
     * Get CC recipients for a letter
     */
    private function getCCRecipients($correspondenceId) {
        global $wpdb;
        $table = $wpdb->prefix . 'persian_oa_cc_recipients';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix.
        $col = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$table} WHERE correspondence_id = %d",
            $correspondenceId
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return is_array($col) ? $col : [];
    }

    /**
     * Get referral recipient user IDs (to_user) for a letter
     */
    private function getReferralRecipientIds($correspondenceId) {
        global $wpdb;
        $table = $wpdb->prefix . 'persian_oa_referrals';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix.
        $col = $wpdb->get_col($wpdb->prepare(
            "SELECT to_user FROM {$table} WHERE correspondence_id = %d",
            $correspondenceId
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        
        return is_array($col) ? $col : [];
    }
    
    /**
     * Get attachments for a letter
     */
    private function getAttachments($correspondenceId) {
        global $wpdb;
        $table = $wpdb->prefix . 'persian_oa_attachments';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix.
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE correspondence_id = %d ORDER BY id ASC",
            $correspondenceId
        ));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return is_array($results) ? $results : [];
    }
}





