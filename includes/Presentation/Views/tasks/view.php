<?php
/**
 * Task Detail View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;

// --- Helper Data & Configuration ---
$statusColors = [
    'todo' => 'bg-gray-100 text-gray-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'review' => 'bg-yellow-100 text-yellow-800',
    'completed' => 'bg-green-100 text-green-800'
];

$statusLabels = [
    'todo' => 'برای انجام',
    'in_progress' => 'در حال انجام',
    'review' => 'در حال بررسی',
    'completed' => 'تکمیل شده'
];

$priorityColors = [
    'low' => 'bg-blue-50 text-blue-600 border-blue-200',
    'medium' => 'bg-green-50 text-green-600 border-green-200',
    'high' => 'bg-orange-50 text-orange-600 border-orange-200',
    'urgent' => 'bg-red-50 text-red-600 border-red-200'
];

$priorityLabels = [
    'low' => 'کم',
    'medium' => 'متوسط',
    'high' => 'زیاد',
    'urgent' => 'فوری'
];

// --- Checklist Logic ---
$checklistItems = json_decode($task->getChecklist() ?: '[]', true);
if (!is_array($checklistItems)) $checklistItems = [];
$checklistTotal = count($checklistItems);
$checklistCompleted = count(array_filter($checklistItems, function($item) { return !empty($item['checked']); }));
$checklistPercent = $checklistTotal > 0 ? round(($checklistCompleted / $checklistTotal) * 100) : 0;

// --- Attachments Logic (Extracted from Comments) ---
$attachments = [];
foreach ($comments as $comment) {
    if (!empty($comment['file_attachment'])) {
        $attachments[] = [
            'url' => $comment['file_attachment'],
            'name' => basename($comment['file_attachment']),
            'date' => $comment['created_at'],
            'user' => $comment['user_id']
        ];
    }
}
?>

<script>
    // Define global data object immediately
    window.oaTaskData = {
        id: <?php echo (int) $task->getId(); ?>,
        nonce: '<?php echo esc_js( wp_create_nonce( 'oa_task_nonce' ) ); ?>',
        status: '<?php echo esc_js( $task->getStatus() ); ?>',
        ajaxUrl: '<?php echo esc_js( esc_url( admin_url( 'admin-ajax.php' ) ) ); ?>'
    };
    console.log('OA View: Task Data Initialized', window.oaTaskData);
</script>

<div class="wrap oa-wrap">
    
    <!-- Main Header -->
    <div class="oa-task-header-modern">
        <div class="oa-header-top">
            <div class="oa-header-title-group">
                <div class="oa-task-id">#<?php echo esc_html( (string) $task->getId() ); ?></div>
                <h1 class="oa-main-title"><?php echo esc_html($task->getTitle()); ?></h1>
                <div class="oa-status-badge <?php echo esc_attr( $statusColors[ $task->getStatus() ] ?? 'bg-gray-100' ); ?>">
                    <?php echo esc_html( $statusLabels[ $task->getStatus() ] ?? $task->getStatus() ); ?>
                </div>
            </div>
            
            <div class="oa-header-actions">
                <!-- Timer Widget -->
                <div class="oa-timer-widget" id="oa-timer-widget">
                    <span class="oa-timer-display" id="oa-timer-display">00:00:00</span>
                    <button class="oa-btn-icon oa-btn-play" id="oa-btn-start-timer" title="شروع تایمر">
                        <span class="dashicons dashicons-controls-play"></span>
                    </button>
                    <button class="oa-btn-icon oa-btn-stop" id="oa-btn-stop-timer" title="توقف و ثبت" style="display:none;">
                        <span class="dashicons dashicons-controls-stop"></span>
                    </button>
                </div>

                <div class="oa-action-buttons">
                    <?php if($task->getStatus() === 'todo'): ?>
                        <button class="oa-btn oa-btn-primary oa-status-btn" data-status="in_progress">
                            <span class="dashicons dashicons-controls-play"></span> شروع کار
                        </button>
                    <?php elseif($task->getStatus() === 'in_progress'): ?>
                        <button class="oa-btn oa-btn-success oa-status-btn" data-status="completed">
                            <span class="dashicons dashicons-yes"></span> تکمیل شد
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'edit', 'id' => $task->getId() ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>" class="oa-btn oa-btn-outline">
                        <span class="dashicons dashicons-edit"></span>
                    </a>
                    
                    <button class="oa-btn oa-btn-danger-outline oa-delete-btn">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Meta Data Bar -->
        <div class="oa-meta-bar-modern">
            <?php if ($task->getTags()): ?>
                <div class="oa-tags-list">
                    <?php foreach (explode(',', $task->getTags()) as $tag): ?>
                        <span class="oa-tag-modern">#<?php echo esc_html(trim($tag)); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="oa-divider-vertical"></div>
            <?php endif; ?>

            <div class="oa-meta-item">
                <span class="dashicons dashicons-calendar-alt"></span>
                <span>ایجاد: <?php echo esc_html( JalaliDate::toJalali( $task->getCreatedAt() ) ); ?></span>
            </div>
            
            <div class="oa-meta-item <?php echo esc_attr( ( strtotime( $task->getDeadline() ) < time() && $task->getStatus() !== 'completed' ) ? 'text-danger' : '' ); ?>">
                <span class="dashicons dashicons-clock"></span>
                <span>مهلت: <?php echo esc_html( $task->getDeadline() ? JalaliDate::toJalali( $task->getDeadline() ) : 'بدون مهلت' ); ?></span>
            </div>
            
            <div class="oa-meta-item">
                <span class="oa-priority-badge <?php echo esc_attr( $priorityColors[ $task->getPriority() ] ); ?>">
                    <?php echo esc_html( $priorityLabels[ $task->getPriority() ] ); ?>
                </span>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="oa-progress-container">
            <div class="oa-progress-bar">
                <div class="oa-progress-value" style="width: <?php echo esc_attr( (string) intval( $task->getProgress() ) ); ?>%"></div>
            </div>
        </div>
    </div>

    <div class="oa-layout-grid">
        <!-- Main Column -->
        <div class="oa-col-main">
            
            <!-- Description Card -->
            <div class="oa-card">
                <div class="oa-card-header">
                    <h3><span class="dashicons dashicons-text-page"></span> توضیحات</h3>
                    <button class="oa-btn-icon oa-btn-small" id="oa-edit-desc-btn" title="ویرایش توضیحات">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                </div>
                <div class="oa-card-body oa-prose" id="oa-desc-content">
                    <?php echo wp_kses_post( wpautop( $task->getDescription() ) ); ?>
                </div>
                <div class="oa-card-body" id="oa-desc-edit-container" style="display:none;">
                    <textarea id="oa-desc-edit-area" class="oa-input" rows="10"><?php echo esc_textarea($task->getDescription()); ?></textarea>
                    <div class="oa-mt-2">
                        <button id="oa-save-desc-btn" class="oa-btn oa-btn-primary">ذخیره</button>
                        <button id="oa-cancel-desc-btn" class="oa-btn oa-btn-outline">لغو</button>
                    </div>
                </div>
            </div>

            <!-- Checklist Card -->
            <div class="oa-card">
                <div class="oa-card-header">
                    <h3><span class="dashicons dashicons-yes-alt"></span> چک‌لیست</h3>
                    <span class="oa-badge-counter"><?php echo esc_html( (string) $checklistCompleted ); ?> / <?php echo esc_html( (string) $checklistTotal ); ?></span>
                </div>
                <div class="oa-card-body">
                    <div class="oa-checklist-wrapper">
                        <ul id="oa-checklist-items" class="oa-checklist-modern">
                            <?php foreach($checklistItems as $idx => $item): ?>
                                <li class="oa-checklist-row">
                                    <label class="oa-checkbox-container">
                                        <input type="checkbox" class="oa-checklist-cb" data-idx="<?php echo esc_attr( (string) $idx ); ?>" <?php checked(!empty($item['checked'])); ?>>
                                        <span class="oa-checkmark"></span>
                                        <span class="oa-text <?php echo esc_attr( ! empty( $item['checked'] ) ? 'completed' : '' ); ?>">
                                            <?php echo esc_html($item['text']); ?>
                                        </span>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="oa-checklist-add">
                            <input type="text" id="oa-new-checklist-text" placeholder="مورد جدید را بنویسید و اینتر بزنید...">
                            <button id="oa-add-check-btn" class="oa-btn-small"><span class="dashicons dashicons-plus-alt2"></span></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtasks Card -->
            <?php if (!empty($subtasks)): ?>
            <div class="oa-card">
                <div class="oa-card-header">
                    <h3><span class="dashicons dashicons-networking"></span> زیر وظایف</h3>
                </div>
                <div class="oa-card-body p-0">
                    <ul class="oa-subtasks-modern">
                        <?php foreach ($subtasks as $sub): ?>
                            <li class="oa-subtask-row">
                                <div class="oa-subtask-info">
                                    <span class="oa-status-dot <?php echo esc_attr( $sub->getStatus() ); ?>"></span>
                                    <a href="<?php echo esc_url( add_query_arg( [ 'action' => 'view', 'id' => $sub->getId() ], admin_url( 'admin.php?page=persian-oa-tasks' ) ) ); ?>">
                                        <?php echo esc_html($sub->getTitle()); ?>
                                    </a>
                                </div>
                                <div class="oa-subtask-meta">
                                    <span class="oa-badge-mini"><?php echo esc_html( $statusLabels[ $sub->getStatus() ] ); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Activity Tabs (Comments, History, Attachments) -->
            <div class="oa-card">
                <div class="oa-tabs-header">
                    <button class="oa-tab-btn active" data-tab="comments">نظرات (<?php echo esc_html( (string) count( $comments ) ); ?>)</button>
                    <button class="oa-tab-btn" data-tab="attachments">فایل‌ها (<?php echo esc_html( (string) count( $attachments ) ); ?>)</button>
                    <button class="oa-tab-btn" data-tab="history">تاریخچه</button>
                </div>
                
                <div class="oa-card-body">
                    <!-- Comments Tab -->
                    <div id="tab-comments" class="oa-tab-content active">
                        <div id="oa-comments-list" class="oa-comments-flow">
                            <?php foreach ($comments as $comment): 
                                $user = get_userdata($comment['user_id']);
                                $displayName = $user ? $user->display_name : 'کاربر حذف شده';
                            ?>
                                <div class="oa-comment-item">
                                    <div class="oa-comment-avatar">
                                        <?php echo wp_kses_post( get_avatar( $comment['user_id'], 40 ) ); ?>
                                    </div>
                                    <div class="oa-comment-content">
                                        <div class="oa-comment-meta">
                                            <span class="author"><?php echo esc_html($displayName); ?></span>
                                            <span class="date"><?php echo esc_html( JalaliDate::toJalaliDateTime( $comment['created_at'] ) ); ?></span>
                                        </div>
                                        <div class="oa-comment-text">
                                            <?php echo nl2br(esc_html($comment['comment'])); ?>
                                        </div>
                                        <?php if(!empty($comment['file_attachment'])): ?>
                                            <a href="<?php echo esc_url($comment['file_attachment']); ?>" class="oa-attachment-link" target="_blank">
                                                <span class="dashicons dashicons-paperclip"></span> دانلود پیوست
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="oa-comment-input-area">
                            <textarea id="oa-new-comment" placeholder="نظر خود را بنویسید..."></textarea>
                            <div class="oa-comment-tools">
                                <button type="button" id="oa-upload-trigger" class="oa-tool-btn" title="افزودن فایل">
                                    <span class="dashicons dashicons-paperclip"></span>
                                </button>
                                <input type="hidden" id="oa-comment-attachment-url">
                                <span id="oa-attachment-name" style="font-size:12px; color:#666; margin-right:10px;"></span>
                                <button type="button" id="oa-submit-comment" class="oa-btn oa-btn-primary">ارسال</button>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Tab -->
                    <div id="tab-attachments" class="oa-tab-content">
                        <?php if(empty($attachments)): ?>
                            <div class="oa-empty-state">
                                <span class="dashicons dashicons-media-default"></span>
                                <p>هنوز فایلی پیوست نشده است.</p>
                            </div>
                        <?php else: ?>
                            <div class="oa-attachments-grid">
                                <?php foreach($attachments as $att): 
                                    $u = get_userdata($att['user']);
                                    $uName = $u ? $u->display_name : 'کاربر حذف شده';
                                ?>
                                    <div class="oa-att-card">
                                        <div class="oa-att-icon">
                                            <span class="dashicons dashicons-media-document"></span>
                                        </div>
                                        <div class="oa-att-info">
                                            <a href="<?php echo esc_url($att['url']); ?>" target="_blank" class="name"><?php echo esc_html($att['name']); ?></a>
                                            <span class="meta">توسط <?php echo esc_html( $uName ); ?> در <?php echo esc_html( JalaliDate::toJalali( $att['date'] ) ); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- History Tab -->
                    <div id="tab-history" class="oa-tab-content">
                        <ul class="oa-history-list">
                            <?php foreach ($logs as $log): 
                                 $user = get_userdata($log['user_id']);
                                 $logName = $user ? $user->display_name : 'کاربر حذف شده';
                            ?>
                                <li class="oa-history-item">
                                    <div class="oa-history-icon"><span class="dashicons dashicons-backup"></span></div>
                                    <div class="oa-history-details">
                                        <strong><?php echo esc_html($logName); ?></strong>
                                        <?php echo esc_html($log['details']); ?>
                                        <span class="time"><?php echo esc_html( JalaliDate::toJalaliDateTime( $log['created_at'] ) ); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar Column -->
        <div class="oa-col-side">
            
            <!-- People Card -->
            <div class="oa-card">
                <div class="oa-card-header">
                    <h3>افراد مرتبط</h3>
                </div>
                <div class="oa-card-body">
                    <div class="oa-person-row">
                        <label>مسئول انجام:</label>
                        <div class="oa-person">
                            <?php 
                            $u = get_userdata($task->getAssignedTo()); 
                            if ($u):
                            ?>
                                <?php echo wp_kses_post( get_avatar( $u->ID, 32 ) ); ?>
                                <span><?php echo esc_html( $u->display_name ); ?></span>
                            <?php else: ?>
                                <?php echo wp_kses_post( get_avatar( 0, 32 ) ); ?>
                                <span>کاربر حذف شده</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="oa-person-row">
                        <label>ایجاد کننده:</label>
                        <div class="oa-person">
                            <?php 
                            $u = get_userdata($task->getAssignedBy()); 
                            if ($u):
                            ?>
                                <?php echo wp_kses_post( get_avatar( $u->ID, 32 ) ); ?>
                                <span><?php echo esc_html( $u->display_name ); ?></span>
                            <?php else: ?>
                                <?php echo wp_kses_post( get_avatar( 0, 32 ) ); ?>
                                <span>کاربر حذف شده</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="oa-card">
                <div class="oa-card-header"><h3>جزئیات</h3></div>
                <div class="oa-card-body">
                    <ul class="oa-details-list">
                        <li>
                            <span class="label">وضعیت</span>
                            <span class="value">
                                <select id="oa-status-select" class="oa-select-mini" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 4px;">
                                    <?php foreach($statusLabels as $key => $label): ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $task->getStatus(), $key ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                        </li>
                        <li>
                            <span class="label">دسته‌بندی</span>
                            <span class="value"><?php echo esc_html( $task->getCategory() ?: 'عمومی' ); ?></span>
                        </li>
                        <li>
                            <span class="label">تخمین زمان</span>
                            <span class="value"><?php echo esc_html( (string) $task->getEstimatedTime() ); ?> ساعت</span>
                        </li>
                        <li>
                            <span class="label">زمان صرف شده</span>
                            <span class="value"><?php echo esc_html( (string) $task->getSpentTime() ); ?> دقیقه</span>
                        </li>
                    </ul>
                    
                    <?php if ($task->getCorrespondenceId()): ?>
                        <div class="oa-linked-item">
                            <span class="dashicons dashicons-email-alt"></span>
                            <div>
                                <small>نامه مرتبط</small>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-incoming-letters&action=view&id=' . $task->getCorrespondenceId() ) ); ?>">مشاهده نامه</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Manual Time Log Button -->
            <button class="oa-btn oa-btn-secondary oa-w-full" onclick="jQuery('#oa-manual-time-modal').fadeIn()">
                <span class="dashicons dashicons-clock"></span> ثبت دستی زمان
            </button>

            <!-- Actions -->
            <div class="oa-side-actions">
                 <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-tasks&action=new&parent_id=' . $task->getId() ) ); ?>" class="oa-link-btn">
                    + افزودن زیر وظیفه
                </a>
            </div>

        </div>
    </div>
</div>

<!-- Manual Time Log Modal -->
<div id="oa-manual-time-modal" class="oa-modal-overlay" style="display:none;">
    <div class="oa-modal">
        <div class="oa-modal-header">
            <h3>ثبت زمان دستی</h3>
            <button class="close-modal" onclick="jQuery('#oa-manual-time-modal').fadeOut()">×</button>
        </div>
        <div class="oa-modal-body">
            <div class="oa-form-group">
                <label>زمان شروع</label>
                <input type="datetime-local" id="oa-time-start" class="oa-input">
            </div>
            <div class="oa-form-group">
                <label>زمان پایان</label>
                <input type="datetime-local" id="oa-time-end" class="oa-input">
            </div>
            <div class="oa-form-group">
                <label>توضیحات</label>
                <textarea id="oa-time-desc" class="oa-input" rows="3"></textarea>
            </div>
        </div>
        <div class="oa-modal-footer">
            <button class="oa-btn oa-btn-primary" id="oa-submit-time-manual">ثبت</button>
        </div>
    </div>
</div>


