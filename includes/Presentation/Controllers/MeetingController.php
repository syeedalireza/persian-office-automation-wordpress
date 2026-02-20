<?php
/**
 * Meeting Controller
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verified in form handlers; GET used for display only and sanitized.
 * @package OfficeAutomation
 */

namespace OfficeAutomation\Presentation\Controllers;

use OfficeAutomation\Application\DTO\MeetingDTO;
use OfficeAutomation\Application\Services\MeetingService;
use OfficeAutomation\Infrastructure\Repository\MeetingRepository;

class MeetingController {
    
    private $service;
    
    public function __construct() {
        $repository = new MeetingRepository();
        $this->service = new MeetingService($repository);
    }
    
    /**
     * Handle create meeting form submission
     */
    public function handleCreate() {
        if (!isset($_POST['persian_oa_meeting_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_meeting_nonce'])), 'persian_oa_create_meeting')) {
            wp_die('امنیت فرم تایید نشد.');
        }
        
        $dto = MeetingDTO::fromRequest($_POST);
        $result = $this->service->createMeeting($dto);
        
        if ($result['success']) {
            if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'calendar') {
                wp_safe_redirect( add_query_arg( array( 'page' => 'persian-oa-calendar', 'message' => 'created' ), admin_url( 'admin.php' ) ) );
                exit;
            }
            
            wp_safe_redirect( add_query_arg( array( 'page' => 'persian-oa-meetings', 'message' => 'created' ), admin_url( 'admin.php' ) ) );
            exit;
        }
        
        $errors = isset($result['errors']) && is_array($result['errors']) ? $result['errors'] : ['general' => 'خطا در ثبت جلسه.'];
        set_transient('persian_oa_meeting_create_errors', $errors, 60);
        $redirect_url = add_query_arg([
            'page' => 'persian-oa-meetings',
            'action' => 'new',
            'error' => '1'
        ], admin_url('admin.php'));
        if (!empty($_POST['redirect_to'])) {
            $redirect_url = add_query_arg('redirect_to', sanitize_text_field(wp_unslash($_POST['redirect_to'])), $redirect_url);
        }
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    /**
     * Render Meeting List (paginated, 15 per page)
     */
    public function renderList() {
        $current_user_id = get_current_user_id();
        $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $result = $this->service->getUserMeetingsPaginated($current_user_id, $paged, 15);

        $meetings = $result['meetings'];
        $pagination = [
            'total'        => $result['total'],
            'total_pages'  => $result['total_pages'],
            'current_page' => $result['current_page'],
            'per_page'     => $result['per_page'],
        ];

        $repository = new MeetingRepository();
        $participantsByMeeting = [];
        foreach ($meetings as $meeting) {
            $participantsByMeeting[$meeting->getId()] = $repository->getParticipants($meeting->getId());
        }

        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/meetings/list.php';
    }
    
    /**
     * Render Create Meeting Form
     */
    public function renderCreate() {
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/meetings/create.php';
    }

    /**
     * Render Edit Meeting Form
     */
    public function renderEdit() {
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;
        if (!$id) {
            wp_safe_redirect(admin_url('admin.php?page=persian-oa-meetings'));
            exit;
        }
        $current_user_id = get_current_user_id();
        $meeting = $this->service->getMeetingById($id, $current_user_id);
        if (!$meeting) {
            wp_die('جلسه یافت نشد یا شما مجوز ویرایش آن را ندارید.');
        }
        $repository = new MeetingRepository();
        $participants = $repository->getParticipants($id);
        $participantIds = array_map(function ($p) {
            return (int) (is_object($p) ? $p->user_id : $p['user_id']);
        }, $participants);
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/meetings/edit.php';
    }

    /**
     * Handle update meeting form submission
     */
    public function handleEdit() {
        if (!isset($_POST['persian_oa_meeting_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_meeting_nonce'])), 'persian_oa_update_meeting')) {
            wp_die('امنیت فرم تایید نشد.');
        }
        $dto = MeetingDTO::fromRequest($_POST);
        $result = $this->service->updateMeeting($dto);
        if ($result['success']) {
            wp_safe_redirect(add_query_arg(['page' => 'persian-oa-meetings', 'message' => 'updated'], admin_url('admin.php')));
            exit;
        }
        $errors = isset($result['errors']) && is_array($result['errors']) ? $result['errors'] : ['general' => 'خطا در به‌روزرسانی جلسه.'];
        set_transient('persian_oa_meeting_edit_errors', $errors, 60);
        $edit_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
        wp_safe_redirect(add_query_arg(['page' => 'persian-oa-meetings', 'action' => 'edit', 'id' => $edit_id, 'error' => '1'], admin_url('admin.php')));
        exit;
    }

    /**
     * Handle delete meeting (GET with nonce)
     */
    public function handleDelete() {
        if (!isset($_GET['id']) || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'persian_oa_delete_meeting_' . absint($_GET['id']))) {
            wp_die('امنیت عملیات تایید نشد.');
        }
        $id = absint($_GET['id']);
        $userId = get_current_user_id();
        if ($this->service->deleteMeeting($id, $userId)) {
            wp_safe_redirect(add_query_arg(['page' => 'persian-oa-meetings', 'message' => 'deleted'], admin_url('admin.php')));
            exit;
        }
        wp_die('جلسه یافت نشد یا شما مجوز حذف آن را ندارید.');
    }
}


