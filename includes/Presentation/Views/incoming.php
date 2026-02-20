<?php
/**
 * Incoming Letters View - Beautiful Design
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list/filter; GET params sanitized.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
use OfficeAutomation\Common\Constants;

global $wpdb;
$table = $wpdb->prefix . 'persian_oa_correspondence';

// Show success message (static markup, no user input).
$incoming_message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
if ( $incoming_message === 'success' ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup, no user input.
    echo '<div class="oa-card oa-mb-4" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-right: 4px solid #10b981; animation: fadeInUp 0.6s;">';
    echo '<div style="padding: 20px;"><strong style="color: #065f46;">✅ عملیات موفق:</strong> <span style="color: #047857;">نامه با موفقیت ذخیره شد.</span></div>';
    echo '</div>';
}

// Get letters
$where = "WHERE type = 'incoming'";
if ( ! empty( $_GET['status'] ) ) {
    $status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
    if ( ! empty( $status ) && in_array( $status, [ 'draft', 'pending', 'approved', 'rejected' ], true ) ) {
        $where .= $wpdb->prepare( ' AND status = %s', $status );
    }
}
if ( ! empty( $_GET['s'] ) ) {
    $search = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '%';
    $where .= $wpdb->prepare( ' AND (subject LIKE %s OR number LIKE %s)', $search, $search );
}
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix; $where built with prepare.
$letters = $wpdb->get_results( "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT 50" );
$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE type = 'incoming'" );
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '📥' ) ); ?></span>
                    نامه‌های وارده
                </h1>
                <p class="oa-subtitle">
                    مجموع <?php echo esc_html( number_format( $total ) ); ?> نامه وارده در سیستم • 
                    تاریخ: <?php echo esc_html(JalaliDate::now('l، j F Y')); ?>
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <?php if (current_user_can('oa_create_letter') || current_user_can('manage_options')) { ?>
                <a href="?page=persian-oa-incoming-letters&action=new" class="oa-btn oa-btn-primary">
                    ➕ نامه وارده جدید
                </a>
                <?php } ?>
                <button class="oa-btn oa-btn-outline" onclick="window.print()">
                    🖨️ چاپ لیست
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="oa-card oa-mb-4" style="animation-delay: 0.1s;">
        <div style="padding: 24px;">
            <form method="get" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end;">
                <input type="hidden" name="page" value="persian-oa-incoming-letters">
                
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 600; color: var(--oa-gray-700); margin-bottom: 8px;">
                        🔍 جستجو
                    </label>
                    <input type="text" name="s" class="oa-input" placeholder="موضوع یا شماره نامه..." value="<?php echo esc_attr( sanitize_text_field( isset( $_GET['s'] ) ? wp_unslash( $_GET['s'] ) : '' ) ); ?>">
                </div>

                <div>
                    <label style="display: block; font-size: 14px; font-weight: 600; color: var(--oa-gray-700); margin-bottom: 8px;">
                        📊 وضعیت
                    </label>
                    <select name="status" class="oa-select">
                        <option value="">همه</option>
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
?>
<option value="draft" <?php selected($status_filter, 'draft'); ?>>پیش‌نویس</option>
<option value="pending" <?php selected($status_filter, 'pending'); ?>>در انتظار</option>
<option value="approved" <?php selected($status_filter, 'approved'); ?>>تایید شده</option>
<option value="rejected" <?php selected($status_filter, 'rejected'); ?>>رد شده</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 14px; font-weight: 600; color: var(--oa-gray-700); margin-bottom: 8px;">
                        ⚡ اولویت
                    </label>
                    <select name="priority" class="oa-select">
                        <option value="">همه</option>
                        <option value="low">کم</option>
                        <option value="medium">متوسط</option>
                        <option value="high">زیاد</option>
                        <option value="urgent">فوری</option>
                    </select>
                </div>

                <button type="submit" class="oa-btn oa-btn-primary" style="margin-top: 29px;">
                    فیلتر
                </button>
            </form>
        </div>
    </div>

    <!-- Letters Grid -->
    <?php if (!empty($letters)) { ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($letters as $letter) { 
                $statusColors = [
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'draft' => 'primary'
                ];
                $statusLabels = [
                    'pending' => 'در انتظار پاسخ',
                    'approved' => 'تایید و پاسخ داده شده',
                    'rejected' => 'رد شده',
                    'draft' => 'پیش‌نویس'
                ];
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
            ?>
                <div class="oa-card" style="animation-delay: 0.<?php echo esc_attr( (string) array_search( $letter, $letters, true ) ); ?>s;">
                    <div style="padding: 28px; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                    <span style="font-size: 20px; font-weight: 800; color: var(--oa-primary);">
                                        #<?php echo esc_html( JalaliDate::convertNumbers( $letter->number ) ); ?>
                                    </span>
                                    <span class="oa-badge oa-badge-<?php echo esc_attr( $statusColors[ $letter->status ] ?? 'primary' ); ?>">
                                        <?php echo esc_html( $statusLabels[ $letter->status ] ?? 'نامشخص' ); ?>
                                    </span>
                                    <?php if (!empty($letter->priority)) { ?>
                                        <span class="oa-badge oa-badge-warning">
                                            <?php echo esc_html($priorityIcons[$letter->priority] ?? ''); ?> <?php echo esc_html($priorityLabels[$letter->priority] ?? $letter->priority); ?>
                                        </span>
                                    <?php } ?>
                                </div>
                                
                                <h3 style="font-size: 22px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 12px 0;">
                                    <?php echo esc_html($letter->subject); ?>
                                </h3>
                                
                                <div style="display: flex; gap: 24px; color: var(--oa-gray-600); font-size: 14px;">
                                    <?php 
                                        $categories = get_option('persian_oa_incoming_categories', Constants::LETTER_TYPES);
                                        $catLabel = $categories[$letter->category] ?? $letter->category;
                                    ?>
                                    <?php if ($catLabel): ?>
                                        <span>📋 <strong><?php echo esc_html($catLabel); ?></strong></span>
                                    <?php endif; ?>
                                    <span>👤 فرستنده: <strong><?php echo esc_html($letter->sender ?? 'نامشخص'); ?></strong></span>
                                    <span>📅 تاریخ: <strong><?php echo esc_html(JalaliDate::format($letter->created_at, 'date')); ?></strong></span>
                                    <span>🕐 زمان: <strong><?php echo esc_html(JalaliDate::timeAgo($letter->created_at)); ?></strong></span>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 8px;">
                                <?php if (current_user_can('oa_view_letter') || current_user_can('manage_options')) { ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-incoming-letters&action=view&id=' . absint( $letter->id ) ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 10px 16px; font-size: 14px;">
                                    👁️ مشاهده
                                </a>
                                <?php } ?>
                                <?php if ( current_user_can( 'oa_edit_letter' ) || current_user_can( 'manage_options' ) ) { ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-incoming-letters&action=edit&id=' . absint( $letter->id ) ) ); ?>" class="oa-btn oa-btn-primary" style="padding: 10px 16px; font-size: 14px;">
                                    ✏️ ویرایش
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($letter->description)) { ?>
                            <div style="padding: 16px; background: var(--oa-gray-50); border-radius: 12px; font-size: 15px; color: var(--oa-gray-700); line-height: 1.6;">
                                <?php echo wp_kses_post(substr($letter->description, 0, 200)); ?>
                                <?php if ( strlen( $letter->description ) > 200 ) echo esc_html( '...' ); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="oa-card">
            <div style="padding: 80px; text-align: center;">
                <div style="font-size: 72px; margin-bottom: 24px; animation: float 3s ease-in-out infinite;">📥</div>
                <h3 style="font-size: 24px; font-weight: 700; color: var(--oa-gray-900); margin-bottom: 12px;">
                    هیچ نامه وارده‌ای یافت نشد
                </h3>
                <p style="font-size: 16px; color: var(--oa-gray-600); margin-bottom: 32px;">
                    با کلیک روی دکمه بالا، اولین نامه وارده را ثبت کنید
                </p>
                <?php if (current_user_can('oa_create_letter') || current_user_can('manage_options')) { ?>
                <a href="?page=persian-oa-incoming-letters&action=new" class="oa-btn oa-btn-primary oa-btn-lg">
                    ➕ ثبت نامه وارده جدید
                </a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>


