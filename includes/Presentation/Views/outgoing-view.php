<?php
/**
 * Outgoing Letter Read-Only View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

if (!isset($letter) || !$letter) {
    wp_die('نامه مورد نظر یافت نشد.');
}

$statusColors = [
    'draft' => 'primary',
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger'
];
$statusLabels = [
    'draft' => 'پیش‌نویس',
    'pending' => 'در انتظار تایید',
    'approved' => 'ارسال شده',
    'rejected' => 'رد شده'
];
$statusClass = $statusColors[$letter->getStatus()] ?? 'primary';
$statusLabel = $statusLabels[$letter->getStatus()] ?? 'نامشخص';

$letterDate = $letter->getLetterDate();
if (empty($letterDate) || $letterDate === '0000-00-00' || $letterDate === '0000-00-00 00:00:00') {
    $letterDate = $letter->getCreatedAt();
}

$signer_name = '';
if ($letter->getPrimaryRecipient()) {
    $signer = get_userdata($letter->getPrimaryRecipient());
    $signer_name = $signer ? $signer->display_name : 'نامشخص';
}

$priorities = ['low' => 'عادی', 'normal' => 'عادی', 'medium' => 'متوسط', 'high' => 'فوری', 'urgent' => 'بسیار فوری'];
$priorityLabel = $priorities[$letter->getPriority()] ?? 'عادی';
?>

<div class="oa-wrap">
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post(UIHelper::getTitleIcon('📤')); ?></span>
                    مشاهده نامه صادره
                    <span class="oa-badge oa-badge-<?php echo esc_attr($statusClass); ?>" style="font-size: 14px; margin-right: 12px; vertical-align: middle;">
                        <?php echo esc_html($statusLabel); ?>
                    </span>
                </h1>
                <p class="oa-subtitle">
                    شماره: <?php echo esc_html(JalaliDate::convertNumbers($letter->getNumber())); ?> •
                    تاریخ: <?php echo esc_html(JalaliDate::format($letterDate, 'date')); ?> •
                    گیرنده: <?php echo esc_html($letter->getRecipient() ?: '—'); ?>
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <?php if (($letter->getStatus() === 'draft' || $letter->getStatus() === 'rejected') && (current_user_can('oa_edit_letter') || current_user_can('manage_options'))) { ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-outgoing&action=edit&id=' . $letter->getId())); ?>" class="oa-btn oa-btn-primary">
                    ✏️ ویرایش
                </a>
                <?php } ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-outgoing')); ?>" class="oa-btn oa-btn-outline">
                    ← بازگشت
                </a>
                <button class="oa-btn oa-btn-outline" onclick="window.print()">
                    🖨️ چاپ
                </button>
            </div>
        </div>
    </div>

    <div class="oa-view-container">
        <div class="oa-card">
            <div style="padding: 32px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 2px solid var(--oa-gray-200);">
                    <div>
                        <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 4px;">شماره نامه</div>
                        <strong style="font-size: 18px; color: var(--oa-primary);"><?php echo esc_html(JalaliDate::convertNumbers($letter->getNumber())); ?></strong>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 4px;">تاریخ</div>
                        <strong><?php echo esc_html(JalaliDate::format($letterDate, 'date')); ?></strong>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 4px;">گیرنده</div>
                        <strong><?php echo esc_html($letter->getRecipient() ?: '—'); ?></strong>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 4px;">امضا کننده</div>
                        <strong><?php echo esc_html($signer_name); ?></strong>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 4px;">اولویت</div>
                        <strong><?php echo esc_html($priorityLabel); ?></strong>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 8px;">موضوع</div>
                    <h2 style="font-size: 22px; font-weight: 700; color: var(--oa-gray-900); margin: 0;">
                        <?php echo esc_html($letter->getSubject()); ?>
                    </h2>
                </div>

                <div style="background: var(--oa-gray-50); padding: 24px; border-radius: 12px; margin-bottom: 24px;">
                    <div style="font-size: 12px; color: var(--oa-gray-500); margin-bottom: 12px;">متن نامه</div>
                    <div class="oa-letter-content" style="font-size: 16px; line-height: 1.8; color: var(--oa-gray-800);">
                        <?php echo wp_kses_post(wpautop($letter->getContent() ?: '—')); ?>
                    </div>
                </div>

                <?php if (!empty($letter->getNotes())) { ?>
                <div style="padding: 16px; background: #fef3c7; border-radius: 8px; border-right: 4px solid #f59e0b;">
                    <div style="font-size: 12px; color: #92400e; margin-bottom: 8px;">📝 یادداشت‌های داخلی</div>
                    <div><?php echo wp_kses_post(nl2br(esc_html($letter->getNotes()))); ?></div>
                </div>
                <?php } ?>

                <?php if (!empty($attachments)) { ?>
                <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid var(--oa-gray-200);">
                    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">📎 پیوست‌ها</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <?php
                        $upload_dir = wp_upload_dir();
                        foreach ($attachments as $att) {
                            $path = $att->file_path ?? '';
                            $name = $att->file_name ?? basename($path);
                            $size = isset($att->file_size) ? size_format($att->file_size) : '';
                            $url = $path && strpos($path, $upload_dir['basedir']) === 0
                                ? str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $path)
                                : $path;
                            ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="oa-btn oa-btn-outline" style="padding: 8px 16px;">
                            📄 <?php echo esc_html($name); ?><?php echo $size ? ' (' . esc_html($size) . ')' : ''; ?>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
