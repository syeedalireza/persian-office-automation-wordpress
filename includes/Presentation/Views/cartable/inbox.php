<?php
/**
 * Cartable - Inbox View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
use OfficeAutomation\Common\Constants;

$currentUser = wp_get_current_user();
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '📥' ) ); ?></span>
                    صندوق ورودی
                </h1>
                <p class="oa-subtitle">
                    <?php echo esc_html( (string) ( $totalCount ?? 0 ) ); ?> نامه •
                    <strong style="color: var(--oa-danger);"><?php echo esc_html( (string) ( $unreadCount ?? 0 ) ); ?> خوانده نشده</strong>
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="oa-btn oa-btn-primary" onclick="location.href='?page=persian-oa-incoming-letters&action=new'">
                    ➕ نامه جدید
                </button>
                <button class="oa-btn oa-btn-outline" onclick="location.reload()">
                    🔄 بروزرسانی
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="oa-card" style="margin-bottom: 24px;">
        <div style="padding: 20px;">
            <form method="get" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <input type="hidden" name="page" value="persian-oa-cartable-inbox">
                
                <!-- Search -->
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="s" class="oa-input" placeholder="🔍 جستجو در موضوع، شماره یا توضیحات..." 
                           value="<?php echo esc_attr($filters['search']); ?>" style="width: 100%;">
                </div>
                
                <!-- Priority Filter -->
                <select name="priority" class="oa-input" style="width: 150px;">
                    <option value="">همه اولویت‌ها</option>
                    <option value="low" <?php selected($filters['priority'], 'low'); ?>>🟢 کم</option>
                    <option value="medium" <?php selected($filters['priority'], 'medium'); ?>>🟡 متوسط</option>
                    <option value="high" <?php selected($filters['priority'], 'high'); ?>>🟠 زیاد</option>
                    <option value="urgent" <?php selected($filters['priority'], 'urgent'); ?>>🔴 فوری</option>
                </select>
                
                <!-- Status Filter -->
                <select name="status" class="oa-input" style="width: 150px;">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="pending" <?php selected($filters['status'], 'pending'); ?>>⏳ در انتظار</option>
                    <option value="approved" <?php selected($filters['status'], 'approved'); ?>>✅ تایید شده</option>
                    <option value="rejected" <?php selected($filters['status'], 'rejected'); ?>>❌ رد شده</option>
                </select>
                
                <!-- Unread Only -->
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="unread" value="1" <?php checked($filters['unread'], 1); ?>>
                    <span>فقط خوانده نشده</span>
                </label>
                
                <!-- Starred Only -->
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="starred" value="1" <?php checked($filters['starred'], 1); ?>>
                    <span>⭐ ستاره‌دار</span>
                </label>
                
                <!-- Submit -->
                <button type="submit" class="oa-btn oa-btn-primary">
                    فیلتر
                </button>
                
                <!-- Reset -->
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-cartable-inbox' ) ); ?>" class="oa-btn oa-btn-outline">
                    پاک کردن
                </a>
            </form>
        </div>
    </div>

    <!-- Items List -->
    <div class="oa-card">
        <div style="padding: 0;">
            <?php if (empty($items)): ?>
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
                    <h3 style="margin: 0 0 10px 0; color: var(--oa-gray-700);">صندوق ورودی خالی است</h3>
                    <p style="color: var(--oa-gray-500);">هیچ نامه‌ای برای نمایش وجود ندارد</p>
                </div>
            <?php else: ?>
                <div class="oa-inbox-list">
                    <?php foreach ($items as $item): 
                        $isUnread = empty($item->read_at);
                        $priorityIcons = [
                            'low' => '🟢',
                            'medium' => '🟡',
                            'high' => '🟠',
                            'urgent' => '🔴'
                        ];
                        $priorityLabels = [
                            'low' => 'کم',
                            'medium' => 'متوسط',
                            'high' => 'زیاد',
                            'urgent' => 'فوری'
                        ];
                        
                        $statusColors = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'draft' => 'primary'
                        ];
                        $statusLabels = [
                            'pending' => 'در انتظار',
                            'approved' => 'تایید شده',
                            'rejected' => 'رد شده',
                            'draft' => 'پیش‌نویس'
                        ];
                        
                        $priorityIcon = $priorityIcons[$item->priority] ?? '⚪';
                        $priorityLabel = $priorityLabels[$item->priority] ?? 'نامشخص';
                        $statusClass = $statusColors[$item->status] ?? 'primary';
                        $statusLabel = $statusLabels[$item->status] ?? 'نامشخص';
                        
                        // Calculate time remaining
                        $timeRemaining = '';
                        $viewPage = 'persian-oa-incoming-letters';
                        if ( ! empty( $item->type ) ) {
                            if ( $item->type === 'outgoing' ) {
                                $viewPage = 'persian-oa-outgoing';
                            } elseif ( $item->type === 'internal' ) {
                                $viewPage = 'persian-oa-internal';
                            }
                        }
                        $viewUrl = admin_url( 'admin.php?page=' . $viewPage . '&action=view&id=' . absint( $item->id ) );
                        if ($item->deadline) {
                            $deadlineDate = date_create($item->deadline);
                            $nowDate = date_create(current_time('Y-m-d H:i:s')); // Local time
                            
                            // Reset time to midnight for accurate day calculation
                            $deadlineDate->setTime(0, 0, 0);
                            $nowDate->setTime(0, 0, 0);
                            
                            $diff = $nowDate->diff($deadlineDate);
                            $days = (int)$diff->format('%r%a');
                            
                            if ($days < 0) {
                                $timeRemaining = '<span style="color: var(--oa-danger);">⚠️ ' . abs($days) . ' روز تاخیر</span>';
                            } elseif ($days == 0) {
                                $timeRemaining = '<span style="color: var(--oa-warning);">⏰ امروز</span>';
                            } elseif ($days <= 3) {
                                $timeRemaining = '<span style="color: var(--oa-warning);">⏰ ' . $days . ' روز</span>';
                            } else {
                                $timeRemaining = '<span style="color: var(--oa-success);">⏰ ' . $days . ' روز</span>';
                            }
                        }
                        ?>
                        <div class="oa-inbox-item <?php echo esc_attr( $isUnread ? 'unread' : '' ); ?>"
                             data-id="<?php echo esc_attr( (string) $item->id ); ?>"
                             data-view-url="<?php echo esc_url( $viewUrl ); ?>">
                            <div class="oa-inbox-item-checkbox">
                                <input type="checkbox" class="item-checkbox" value="<?php echo esc_attr( (string) $item->id ); ?>">
                            </div>
                            
                            <div class="oa-inbox-item-star" onclick="toggleStar(<?php echo absint( $item->id ); ?>)">
                                <span class="star-icon" id="star-<?php echo esc_attr( (string) $item->id ); ?>">⭐</span>
                            </div>
                            
                            <div class="oa-inbox-item-content" onclick="viewLetter(this)">
                                <div class="oa-inbox-item-header">
                                    <div class="oa-inbox-item-title">
                                        <?php if ( $isUnread ) : ?>
                                            <span class="oa-badge oa-badge-danger" style="font-size: 10px; padding: 2px 6px;">جدید</span>
                                        <?php endif; ?>
                                        <span><?php echo esc_html( $priorityIcon ); ?> <?php echo esc_html( $item->subject ); ?></span>
                                    </div>
                                    <div class="oa-inbox-item-meta">
                                        <button class="oa-btn oa-btn-outline" style="padding: 2px 8px; font-size: 11px; height: 24px;" onclick="event.stopPropagation(); viewCirculation(<?php echo absint( $item->id ); ?>)">
                                            📊 گردش
                                        </button>
                                        <span class="oa-badge oa-badge-<?php echo esc_attr( $statusClass ); ?>"><?php echo esc_html( $statusLabel ); ?></span>
                                        <?php if ( $timeRemaining ) : ?>
                                            <span><?php echo wp_kses_post( $timeRemaining ); ?></span>
                                        <?php endif; ?>
                                        <span><?php echo esc_html( JalaliDate::timeAgo( $item->created_at ) ); ?></span>
                                    </div>
                                </div>
                                
                                <div class="oa-inbox-item-info">
                                    <span><strong>شماره:</strong> #<?php echo esc_html( $item->number ); ?></span>
                                    <?php 
                                        $categories = get_option('oa_incoming_categories', Constants::LETTER_TYPES);
                                        $catLabel = $categories[$item->category] ?? $item->category;
                                    ?>
                                    <?php if ($catLabel): ?>
                                        <span><strong>نوع:</strong> <?php echo esc_html($catLabel); ?></span>
                                    <?php endif; ?>
                                    <span><strong>از:</strong> <?php echo esc_html($item->sender ?: 'نامشخص'); ?></span>
                                    <?php if ($item->attachment_count > 0): ?>
                                        <span>📎 <?php echo esc_html( (string) $item->attachment_count ); ?> پیوست</span>
                                    <?php endif; ?>
                                    <?php if ( $item->comment_count > 0 ) : ?>
                                        <span>💬 <?php echo esc_html( (string) $item->comment_count ); ?> نظر</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($item->description): ?>
                                    <div class="oa-inbox-item-description">
                                        <?php echo esc_html(mb_substr($item->description, 0, 120)); ?>
                                        <?php if (mb_strlen($item->description) > 120) echo '...'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.oa-inbox-list {
    display: flex;
    flex-direction: column;
}

.oa-inbox-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    border-bottom: 1px solid var(--oa-gray-200);
    transition: all 0.2s;
    cursor: pointer;
}

.oa-inbox-item:hover {
    background: var(--oa-gray-50);
}

.oa-inbox-item.unread {
    background: #f0f9ff;
    border-left: 3px solid var(--oa-primary);
}

.oa-inbox-item-checkbox {
    padding-top: 4px;
}

.oa-inbox-item-star {
    cursor: pointer;
    font-size: 20px;
    padding-top: 2px;
    filter: grayscale(100%);
    opacity: 0.3;
    transition: all 0.2s;
}

.oa-inbox-item-star:hover,
.oa-inbox-item-star.starred {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.2);
}

.oa-inbox-item-content {
    flex: 1;
}

.oa-inbox-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.oa-inbox-item-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--oa-gray-900);
    display: flex;
    align-items: center;
    gap: 8px;
}

.oa-inbox-item.unread .oa-inbox-item-title {
    font-weight: 900;
}

.oa-inbox-item-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--oa-gray-600);
}

.oa-inbox-item-info {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: var(--oa-gray-600);
    margin-bottom: 8px;
}

.oa-inbox-item-description {
    font-size: 14px;
    color: var(--oa-gray-700);
    line-height: 1.6;
}
</style>

<script>
function viewLetter(el) {
    var row = el.closest ? el.closest('.oa-inbox-item') : (el.querySelector ? el : null);
    if (!row) return;
    var id = row.dataset.id;
    var viewUrl = row.dataset.viewUrl;
    if (!viewUrl) viewUrl = '?page=persian-oa-incoming-letters&action=view&id=' + id;
    // Mark as read via AJAX (nonce escaped for JS via esc_js).
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce passed through esc_js().
    jQuery.post(ajaxurl, {
        action: 'persian_oa_mark_as_read',
        nonce: '<?php echo esc_js( wp_create_nonce( 'persian_oa_cartable_nonce' ) ); ?>',
        correspondence_id: id
    });
    window.location.href = viewUrl;
}

function toggleStar(id) {
    event.stopPropagation();
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Nonce passed through esc_js().
    jQuery.post(ajaxurl, {
        action: 'persian_oa_toggle_star',
        nonce: '<?php echo esc_js( wp_create_nonce( 'persian_oa_cartable_nonce' ) ); ?>',
        correspondence_id: id
    }, function(response) {
        if (response.success) {
            jQuery('#star-' + id).parent().toggleClass('starred');
        }
    });
}

// Auto refresh unread count every 30 seconds
setInterval(function() {
    jQuery.post(ajaxurl, {
        action: 'persian_oa_get_unread_count'
    }, function(response) {
        if (response.success) {
            // Update badge if exists
            jQuery('.oa-unread-badge').text(response.data.count);
        }
    });
}, 30000);
</script>

