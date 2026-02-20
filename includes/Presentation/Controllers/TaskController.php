<?php
/**
 * Task Controller
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce verified in form handlers; GET used for display only and sanitized.
 * @package OfficeAutomation
 */

namespace OfficeAutomation\Presentation\Controllers;

use OfficeAutomation\Application\DTO\TaskDTO;
use OfficeAutomation\Application\Services\TaskService;
use OfficeAutomation\Infrastructure\Repository\TaskRepository;
use OfficeAutomation\Common\JalaliDate;

class TaskController {
    
    private $service;
    
    public function __construct() {
        $repository = new TaskRepository();
        $this->service = new TaskService($repository);
    }
    
    /**
     * Handle create task form submission
     */
    public function handleCreate() {
        if (!isset($_POST['persian_oa_task_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_task_nonce'])), 'persian_oa_create_task')) {
            wp_die('امنیت فرم تایید نشد.');
        }
        
        $dto = TaskDTO::fromRequest($_POST);
        $result = $this->service->createTask($dto);
        
        if ($result['success']) {
            if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'calendar') {
                wp_safe_redirect( add_query_arg( array( 'page' => 'persian-oa-calendar', 'message' => 'created' ), admin_url( 'admin.php' ) ) );
                exit;
            }

            wp_safe_redirect( add_query_arg( array( 'page' => 'persian-oa-tasks', 'message' => 'created' ), admin_url( 'admin.php' ) ) );
            exit;
        } else {
            // Handle errors
            wp_die( esc_html__( 'Error creating task', 'persian-office-automation' ) );
        }
    }
    
    /**
     * Handle edit task form submission
     */
    public function handleEdit() {
        if (!isset($_POST['persian_oa_task_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['persian_oa_task_nonce'])), 'persian_oa_edit_task')) {
            wp_die('امنیت فرم تایید نشد.');
        }
        
        $dto = TaskDTO::fromRequest($_POST);
        if (!$dto->id) {
            wp_die('شناسه وظیفه نامعتبر است.');
        }

        $result = $this->service->updateTask($dto->id, $dto);
        
        if ($result['success']) {
            wp_safe_redirect( add_query_arg( array( 'page' => 'persian-oa-tasks', 'message' => 'updated' ), admin_url( 'admin.php' ) ) );
            exit;
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- message passed through esc_html.
            wp_die( esc_html( isset( $result['message'] ) ? (string) $result['message'] : __( 'Error updating task', 'persian-office-automation' ) ) );
        }
    }
    
    /**
     * Render Task List (Kanban or List). GET params for display only; nonce not required for read.
     * phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only; params sanitized and whitelisted.
     */
    public function renderList() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $get_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        if ( $action === 'view' && $get_id ) {
            $this->renderDetails( $get_id );
            return;
        }
        if ( $action === 'edit' && $get_id ) {
            $this->renderEdit( $get_id );
            return;
        }

        $view   = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'kanban';
        $filter = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : 'my_tasks';
        if ( ! in_array( $view, [ 'kanban', 'list' ], true ) ) {
            $view = 'kanban';
        }
        if ( ! in_array( $filter, [ 'my_tasks', 'assigned_by_me' ], true ) ) {
            $filter = 'my_tasks';
        }
        
        $current_user_id = get_current_user_id();
        
        if ($filter === 'assigned_by_me') {
            $tasks = $this->service->getCreatedTasks($current_user_id);
        } else {
            $tasks = $this->service->getUserTasks($current_user_id);
        }
        
        if ($view === 'kanban') {
            require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/tasks/kanban.php';
        } else {
            require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/tasks/list.php';
        }
    }
    
    /**
     * Render Create Task Form
     */
    public function renderCreate() {
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/tasks/create.php';
    }

    /**
     * Render Edit Task Form
     */
    public function renderEdit($id) {
        $task = $this->service->getTask($id);
        if (!$task) {
            echo '<div class="notice notice-error"><p>وظیفه یافت نشد.</p></div>';
            return;
        }
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/tasks/edit.php';
    }

    /**
     * Render Task Details
     */
    public function renderDetails($id) {
        $task = $this->service->getTask($id);
        if (!$task) {
            echo '<div class="notice notice-error"><p>وظیفه یافت نشد.</p></div>';
            return;
        }

        $comments = $this->service->getComments($id);
        $logs = $this->service->getTaskLogs($id);
        $timeLogs = $this->service->getTaskTimeLogs($id);
        $subtasks = $this->service->getSubtasks($id);
        
        require_once PERSIAN_OA_PLUGIN_DIR . 'includes/Presentation/Views/tasks/view.php';
    }

    // AJAX Handlers

    public function ajaxUpdateStatus() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
        
        $result = $this->service->updateStatus($taskId, $status);
        
        wp_send_json($result);
    }

    public function ajaxAddComment() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $comment = isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';
        $attachment = isset( $_POST['attachment'] ) ? esc_url_raw( wp_unslash( $_POST['attachment'] ) ) : null;
        
        $result = $this->service->addComment($taskId, $comment, $attachment);
        
        if ($result['success']) {
            $user = wp_get_current_user();
            $result['html'] = sprintf(
                '<div class="oa-comment">
                    <div class="oa-comment-header">
                        <span class="oa-comment-author">%s</span>
                        <span class="oa-comment-date">%s</span>
                    </div>
                    <div class="oa-comment-body">%s</div>
                </div>',
                esc_html($user->display_name),
                'لحظاتی پیش',
                nl2br(esc_html($comment))
            );
        }
        
        wp_send_json($result);
    }

    public function ajaxAddTimeLog() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $startTime = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
        $endTime = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
        $description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
        
        // Convert Jalali to Gregorian if needed, for now assume inputs are valid datetimes
        // A better approach would be to handle date conversion here
        
        $result = $this->service->addTimeLog($taskId, $startTime, $endTime, $description);
        
        wp_send_json($result);
    }

    public function ajaxUpdateChecklist() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $checklist = isset( $_POST['checklist'] ) ? sanitize_textarea_field( wp_unslash( $_POST['checklist'] ) ) : ''; // JSON string; validated in service
        
        $result = $this->service->updateChecklist($taskId, $checklist);
        
        wp_send_json($result);
    }

    public function ajaxDelete() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        if (!$taskId) {
            wp_send_json(['success' => false, 'message' => 'شناسه نامعتبر']);
        }

        $result = $this->service->deleteTask($taskId);
        wp_send_json($result);
    }
    
    public function ajaxUpdateDescription() {
        check_ajax_referer('persian_oa_task_nonce', 'nonce');
        $taskId = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : 0;
        $description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        
        $result = $this->service->updateDescription($taskId, $description);
        
        wp_send_json($result);
    }
}
