<?php
/**
 * Task List View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only filter; GET params sanitized.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

// Helper to get user display name safely (prefixed for Plugin Check).
if ( ! function_exists( 'persian_oa_get_user_name_safe' ) ) {
    function persian_oa_get_user_name_safe( $user_id ) {
        $user = get_userdata( $user_id );
        return $user ? $user->display_name : 'کاربر حذف شده';
    }
}

$current_filter = isset( $_GET['filter'] ) ? sanitize_text_field( wp_unslash( $_GET['filter'] ) ) : 'my_tasks';
if ( ! in_array( $current_filter, [ 'my_tasks', 'assigned_by_me' ], true ) ) {
    $current_filter = 'my_tasks';
}
?>

<div class="oa-wrap">
    <!-- Header Section with Modern Filters -->
    <div class="oa-header" style="padding: 24px; margin-bottom: 20px;">
        <div class="oa-header-content" style="align-items: center;">
            <div class="oa-title" style="margin: 0; font-size: 28px;">
                <span class="oa-title-icon" style="width: 48px; height: 48px; font-size: 24px;"><?php echo wp_kses_post( UIHelper::getTitleIcon( '☑️' ) ); ?></span>
                مدیریت وظایف
            </div>
            
            <div class="oa-filter-bar">
                <div class="oa-search-box">
                    <input type="text" class="oa-input" placeholder="جستجو در وظایف..." id="oa-task-search-list">
                    <span class="dashicons dashicons-search"></span>
                </div>
                
                <div class="oa-view-switcher-group">
                    <a href="#" class="oa-btn-icon active" title="نمای لیست">
                        <span class="dashicons dashicons-list-view"></span>
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( [ 'view' => 'kanban' ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-btn-icon" title="نمای کانبان">
                        <span class="dashicons dashicons-columns"></span>
                    </a>
                </div>
                
                <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'new' ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-btn oa-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> وظیفه جدید
                </a>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="oa-tabs" style="margin-top: 20px; box-shadow: none; border: 1px solid var(--oa-gray-200); padding: 4px;">
            <a href="<?php echo esc_url( add_query_arg( [ 'filter' => 'my_tasks' ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-tab <?php echo esc_attr( ( $current_filter === 'my_tasks' ) ? 'active' : '' ); ?>">
                📥 وظایف من
            </a>
            <a href="<?php echo esc_url( add_query_arg( [ 'filter' => 'assigned_by_me' ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-tab <?php echo esc_attr( ( $current_filter === 'assigned_by_me' ) ? 'active' : '' ); ?>">
                📤 محول شده توسط من
            </a>
        </div>
    </div>

    <!-- Task List Grid -->
    <?php if (!empty($tasks)) { ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;" id="oa-task-list-container">
            <?php foreach ($tasks as $task) { 
                $statusColors = [
                    'todo' => 'secondary',
                    'in_progress' => 'primary',
                    'review' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger'
                ];
                $statusLabels = [
                    'todo' => 'برای انجام',
                    'in_progress' => 'در حال انجام',
                    'review' => 'در حال بررسی',
                    'completed' => 'تکمیل شده',
                    'cancelled' => 'لغو شده'
                ];
                
                $priorityIcons = [
                    'low' => '🟢',
                    'medium' => '🟡',
                    'high' => '🟠',
                    'urgent' => '🔴'
                ];
            ?>
                <div class="oa-card oa-task-item" style="display: flex; flex-direction: column; height: 100%;">
                    <div style="padding: 20px; flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                            <span class="oa-badge oa-badge-<?php echo esc_attr( $statusColors[ $task->getStatus() ] ?? 'secondary' ); ?>">
                                <?php echo esc_html( $statusLabels[ $task->getStatus() ] ?? $task->getStatus() ); ?>
                            </span>
                            <span title="اولویت">
                                <?php echo esc_html( $priorityIcons[ $task->getPriority() ] ?? '' ); ?>
                            </span>
                        </div>
                        
                        <h3 class="oa-task-title" style="font-size: 18px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 8px 0;">
                            <?php echo esc_html($task->getTitle()); ?>
                        </h3>
                        
                        <p style="color: var(--oa-gray-600); font-size: 14px; margin-bottom: 16px; line-height: 1.5;">
                            <?php echo esc_html( wp_trim_words( $task->getDescription(), 20, '...' ) ); ?>
                        </p>
                        
                        <?php if ($task->getDeadline()): ?>
                            <div style="font-size: 13px; color: var(--oa-gray-500); margin-bottom: 8px;">
                                📅 مهلت: <?php echo esc_html( JalaliDate::format( $task->getDeadline(), 'date' ) ); ?>
                                <?php 
                                    // Calculate days remaining
                                    $deadline = strtotime($task->getDeadline());
                                    $now = time();
                                    $diff = $deadline - $now;
                                    $days = floor($diff / (60 * 60 * 24));
                                    
                                    if ($task->getStatus() !== 'completed') {
                                        if ($days < 0) {
                                            echo wp_kses_post( '<span style="color: #ef4444; font-weight: bold;">(' . esc_html( (string) abs( $days ) ) . ' روز گذشته)</span>' );
                                        } elseif ( $days === 0 ) {
                                            echo '<span style="color: #f59e0b; font-weight: bold;">' . esc_html( '(امروز)' ) . '</span>';
                                        } else {
                                            echo '<span style="color: #10b981;">(' . esc_html( (string) $days ) . ' روز مانده)</span>';
                                        }
                                    }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="font-size: 13px; color: var(--oa-gray-500);">
                            <?php if ($current_filter === 'my_tasks'): ?>
                                👤 محول کننده: <?php echo esc_html( persian_oa_get_user_name_safe( $task->getAssignedBy() ) ); ?>
                            <?php else: ?>
                                👤 مسئول: <?php echo esc_html( persian_oa_get_user_name_safe( $task->getAssignedTo() ) ); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <?php if ($task->getProgress() > 0): ?>
                    <div style="height: 4px; background: #f3f4f6; width: 100%;">
                        <div style="height: 100%; background: #4f46e5; width: <?php echo esc_attr( (string) $task->getProgress() ); ?>%;"></div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="padding: 12px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 12px 12px;">
                        <span style="font-size: 12px; color: #6b7280;">
                            <?php echo esc_html( (string) $task->getProgress() ); ?>% تکمیل شده
                        </span>
                        <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'view', 'id' => $task->getId() ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 6px 12px; font-size: 12px;">
                            مشاهده جزئیات
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="oa-card">
            <div style="padding: 60px; text-align: center;">
                <div style="font-size: 64px; margin-bottom: 20px;">☑️</div>
                <h3 style="font-size: 20px; font-weight: 700; color: var(--oa-gray-900);">
                    هیچ وظیفه‌ای یافت نشد
                </h3>
                <p style="color: var(--oa-gray-600); margin-bottom: 24px;">
                    لیست وظایف شما خالی است.
                </p>
                <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'new' ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-btn oa-btn-primary">
                    ➕ تعریف اولین وظیفه
                </a>
            </div>
        </div>
    <?php } ?>
</div>

// Task search logic moved to assets/js/tasks.js
