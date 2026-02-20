<?php
/**
 * Internal Letters - View Single
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only view; letter from controller.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

if ( ! $letter ) {
    echo '<div class="oa-wrap"><div class="oa-alert oa-alert-error">' . esc_html( 'نامه یافت نشد.' ) . '</div></div>';
    return;
}

$sender = get_userdata($letter->getCreatedBy());
$primary = get_userdata($letter->getPrimaryRecipient());
$recipient_names = [];
if ($primary) {
    $recipient_names[] = $primary->display_name;
}
global $wpdb;
$table_cc = $wpdb->prefix . 'persian_oa_cc_recipients';
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table from prefix.
$cc_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT user_id FROM $table_cc WHERE correspondence_id = %d",
    $letter->getId()
));
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
$cc_ids = $cc_ids ?: [];
$cc_names = [];
foreach ($cc_ids as $uid) {
    $u = get_userdata((int) $uid);
    if ($u) {
        $recipient_names[] = $u->display_name;
        $cc_names[] = $u->display_name;
    }
}
$recipient_label = count($recipient_names) > 1 ? 'گیرندگان' : 'گیرنده';
$recipient_display = $recipient_names ? implode('، ', array_map('esc_html', $recipient_names)) : 'نامشخص';
$confidentiality_labels = ['normal' => 'عادی', 'confidential' => 'محرمانه', 'highly_confidential' => 'خیلی محرمانه'];
$confidentiality_text = $confidentiality_labels[$letter->getConfidentiality()] ?? 'عادی';
if (!isset($attachments)) {
    $attachments = [];
}

$status_labels = ['draft' => 'پیش‌نویس', 'sent' => 'ارسال شده', 'pending' => 'در انتظار'];
$status_class = $letter->getStatus() === 'draft' ? 'warning' : 'success';
$status_label = $status_labels[$letter->getStatus()] ?? 'نامشخص';
?>

<div class="oa-wrap oa-internal-view">
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '✉️' ) ); ?></span>
                    <a href="?page=persian-oa-internal" class="oa-title-link">مکاتبات داخلی</a>
                    <span class="oa-breadcrumb-sep">/</span>
                    <span>مشاهده نامه</span>
                    <?php if ($letter->getStatus() === 'draft'): ?>
                        <span class="oa-badge oa-badge-<?php echo esc_attr( $status_class ); ?> oa-badge-sm">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="oa-subtitle">
                    <?php echo esc_html( $letter->getSubject() ); ?>
                </p>
            </div>
            <div class="oa-header-actions">
                <button class="oa-btn oa-btn-outline" onclick="window.print()">🖨️ چاپ</button>
                <a href="javascript:history.back()" class="oa-btn oa-btn-outline">← بازگشت</a>
            </div>
        </div>
    </div>

    <div class="oa-view-container">
        <!-- Letter Content (Paper Style) -->
        <div class="oa-paper" id="oa-internal-paper">
            <div class="oa-paper-header">
                <div class="oa-paper-meta-row">
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">شماره:</span>
                        <span class="oa-meta-value"><?php echo esc_html( JalaliDate::convertNumbers( $letter->getNumber() ) ); ?></span>
                    </div>
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">تاریخ:</span>
                        <span class="oa-meta-value"><?php echo esc_html( JalaliDate::format( $letter->getCreatedAt() ) ); ?></span>
                    </div>
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">محرمانگی:</span>
                        <span class="oa-meta-value"><?php echo esc_html( $confidentiality_text ); ?></span>
                    </div>
                </div>
                <div class="oa-paper-subject">
                    <span class="oa-meta-label">موضوع:</span>
                    <strong><?php echo esc_html( $letter->getSubject() ); ?></strong>
                </div>
            </div>

            <div class="oa-paper-body">
                <?php echo wp_kses_post( $letter->getContent() ); ?>
            </div>

            <?php if (!empty($attachments)): ?>
                <div class="oa-attachments-section">
                    <h3 class="oa-attachments-title">📎 پیوست‌ها</h3>
                    <div class="oa-attachment-list">
                        <?php
                        $upload_dir = wp_upload_dir();
                        foreach ($attachments as $att):
                            $att_url = isset($att->file_path) ? $att->file_path : '#';
                            if ($att_url !== '#' && !empty($upload_dir['basedir']) && strpos($att_url, $upload_dir['basedir']) === 0) {
                                $att_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $att_url);
                            }
                        ?>
                            <a href="<?php echo esc_url($att_url); ?>" target="_blank" rel="noopener" class="oa-attachment-item">
                                <span class="oa-attachment-icon">📄</span>
                                <span class="oa-attachment-name"><?php echo esc_html( isset($att->file_name) ? $att->file_name : '' ); ?></span>
                                <?php if (!empty($att->file_size)): ?>
                                    <span class="oa-attachment-size"><?php echo esc_html( size_format((int) $att->file_size) ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $current_user_id = get_current_user_id();
            $is_cc = $letter->getId() && in_array($current_user_id, array_map('intval', $cc_ids), true);
            if (($letter->getPrimaryRecipient() == $current_user_id || $is_cc)):
            ?>
                <div class="oa-paper-actions">
                    <button class="oa-btn oa-btn-primary" onclick="alert('قابلیت پاسخ به زودی فعال می‌شود.')">↩️ پاسخ به نامه</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="oa-sidebar">
            <div class="oa-card oa-participants-card">
                <div class="oa-card-header">
                    <h3>👤 شرکت‌کنندگان</h3>
                </div>
                <div class="oa-card-body">
                    <div class="oa-participant-row">
                        <span class="oa-participant-label">فرستنده</span>
                        <span class="oa-participant-value"><?php echo $sender ? esc_html( $sender->display_name ) : esc_html( 'نامشخص' ); ?></span>
                    </div>
                    <div class="oa-participant-row">
                        <span class="oa-participant-label"><?php echo esc_html( $recipient_label ); ?></span>
                        <span class="oa-participant-value"><?php echo esc_html( $recipient_display ); ?></span>
                    </div>
                    <?php if (!empty($cc_names)): ?>
                        <div class="oa-participant-row">
                            <span class="oa-participant-label">رونوشت (CC)</span>
                            <span class="oa-participant-value"><?php echo esc_html( implode('، ', $cc_names) ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="oa-card oa-meta-card">
                <div class="oa-card-header">
                    <h3>📋 اطلاعات نامه</h3>
                </div>
                <div class="oa-card-body">
                    <div class="oa-meta-list">
                        <div class="oa-meta-list-item">
                            <span>شماره نامه</span>
                            <strong><?php echo esc_html( JalaliDate::convertNumbers( $letter->getNumber() ) ); ?></strong>
                        </div>
                        <div class="oa-meta-list-item">
                            <span>تاریخ ارسال</span>
                            <strong><?php echo esc_html( JalaliDate::format( $letter->getCreatedAt(), 'datetime' ) ); ?></strong>
                        </div>
                        <div class="oa-meta-list-item">
                            <span>سطح محرمانگی</span>
                            <strong><?php echo esc_html( $confidentiality_text ); ?></strong>
                        </div>
                        <div class="oa-meta-list-item">
                            <span>وضعیت</span>
                            <span class="oa-badge oa-badge-<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
                        </div>
                        <?php if (!empty($attachments)): ?>
                        <div class="oa-meta-list-item">
                            <span>تعداد پیوست</span>
                            <strong><?php echo esc_html( (string) count( $attachments ) ); ?> فایل</strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Internal View - Paper & Sidebar Layout */
.oa-internal-view .oa-title-link {
    color: inherit;
    text-decoration: none;
    transition: opacity 0.2s;
}
.oa-internal-view .oa-title-link:hover {
    opacity: 0.8;
}
.oa-internal-view .oa-breadcrumb-sep {
    color: var(--oa-gray-400);
    margin: 0 12px;
}
.oa-internal-view .oa-badge-sm { font-size: 13px; padding: 6px 12px; margin-right: 12px; }
.oa-internal-view .oa-header-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.oa-internal-view .oa-view-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-top: 24px;
}
@media (max-width: 1000px) {
    .oa-internal-view .oa-view-container { grid-template-columns: 1fr; }
}

/* Paper Style */
.oa-internal-view .oa-paper {
    background: #fff;
    padding: 48px;
    border-radius: var(--oa-radius-lg);
    box-shadow: var(--oa-shadow-lg);
    border: 1px solid var(--oa-gray-200);
    min-height: 500px;
}
.oa-internal-view .oa-paper-header {
    border-bottom: 2px solid var(--oa-gray-800);
    padding-bottom: 20px;
    margin-bottom: 32px;
}
.oa-internal-view .oa-paper-meta-row {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 14px;
}
.oa-internal-view .oa-paper-meta-item {
    display: flex;
    gap: 8px;
}
.oa-internal-view .oa-meta-label { font-weight: 600; color: var(--oa-gray-600); }
.oa-internal-view .oa-meta-value { color: var(--oa-gray-900); }
.oa-internal-view .oa-paper-subject {
    margin-top: 20px;
    font-size: 18px;
}
.oa-internal-view .oa-paper-subject .oa-meta-label { margin-left: 8px; }
.oa-internal-view .oa-paper-body {
    font-size: 16px;
    line-height: 2;
    text-align: justify;
    color: var(--oa-gray-800);
    min-height: 150px;
}
.oa-internal-view .oa-paper-body p { margin-bottom: 1em; }
.oa-internal-view .oa-paper-body p:last-child { margin-bottom: 0; }

/* Attachments */
.oa-internal-view .oa-attachments-section {
    margin-top: 36px;
    padding-top: 24px;
    border-top: 1px dashed var(--oa-gray-300);
}
.oa-internal-view .oa-attachments-title {
    margin: 0 0 16px 0;
    font-size: 16px;
    color: var(--oa-gray-700);
}
.oa-internal-view .oa-attachment-list {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.oa-internal-view .oa-attachment-item {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: linear-gradient(135deg, var(--oa-gray-50) 0%, #fff 100%);
    border: 1px solid var(--oa-gray-200);
    border-radius: var(--oa-radius-md);
    text-decoration: none;
    color: var(--oa-gray-800);
    font-size: 14px;
    transition: var(--oa-transition);
}
.oa-internal-view .oa-attachment-item:hover {
    background: linear-gradient(135deg, var(--oa-primary-light) 0%, #fff 100%);
    border-color: var(--oa-primary);
    transform: translateY(-2px);
    box-shadow: var(--oa-shadow-md);
}
.oa-internal-view .oa-attachment-icon { font-size: 18px; }
.oa-internal-view .oa-attachment-name { font-weight: 600; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.oa-internal-view .oa-attachment-size { font-size: 12px; color: var(--oa-gray-500); }

/* Paper Actions */
.oa-internal-view .oa-paper-actions {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--oa-gray-200);
}

/* Sidebar Cards */
.oa-internal-view .oa-participants-card,
.oa-internal-view .oa-meta-card { margin-bottom: 24px; }
.oa-internal-view .oa-participant-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 0;
    border-bottom: 1px solid var(--oa-gray-100);
}
.oa-internal-view .oa-participant-row:last-child { border-bottom: none; }
.oa-internal-view .oa-participant-label {
    font-size: 12px;
    color: var(--oa-gray-500);
    font-weight: 600;
}
.oa-internal-view .oa-participant-value {
    font-size: 14px;
    color: var(--oa-gray-900);
}
.oa-internal-view .oa-meta-list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--oa-gray-100);
    font-size: 14px;
}
.oa-internal-view .oa-meta-list-item:last-child { border-bottom: none; }
.oa-internal-view .oa-meta-list-item span { color: var(--oa-gray-500); }
.oa-internal-view .oa-meta-list-item strong { color: var(--oa-gray-900); }

/* Print */
@media print {
    .oa-internal-view .oa-header,
    .oa-internal-view .oa-sidebar,
    .oa-internal-view .oa-paper-actions,
    #adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter { display: none !important; }
    #wpcontent, #wpbody-content { margin-left: 0 !important; padding: 0 !important; }
    .oa-internal-view { padding: 0 !important; margin: 0 !important; background: white !important; }
    .oa-internal-view .oa-view-container { display: block !important; }
    .oa-internal-view .oa-paper {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>
