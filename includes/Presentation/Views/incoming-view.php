<?php
/**
 * Incoming Letter Read-Only View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, PluginCheck.Security.DirectDB.UnescapedDBParameter
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only view; GET params sanitized in controller.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
use OfficeAutomation\Common\Constants;

// Ensure we have a letter object
if (!isset($letter) || !$letter) {
    wp_die('نامه مورد نظر یافت نشد.');
}

// Get helper data
$categories = get_option('oa_incoming_categories', Constants::LETTER_TYPES);
$catLabel = $categories[$letter->getCategory()] ?? $letter->getCategory();

// Status labels
$statusColors = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
    'draft' => 'primary',
    'archived' => 'secondary'
];
$statusLabels = [
    'pending' => 'در انتظار پاسخ',
    'approved' => 'تایید شده',
    'rejected' => 'رد شده',
    'draft' => 'پیش‌نویس',
    'archived' => 'بایگانی شده'
];

$statusClass = $statusColors[$letter->getStatus()] ?? 'primary';
$statusLabel = $statusLabels[$letter->getStatus()] ?? 'نامشخص';

// Fetch workflow history (referrals)
global $wpdb;
$referrals_table = $wpdb->prefix . 'persian_oa_referrals';
$users_table = $wpdb->users;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names from $wpdb->prefix.
$referrals = $wpdb->get_results( $wpdb->prepare(
    "SELECT r.*, u_from.display_name as from_name, u_to.display_name as to_name " .
    "FROM {$referrals_table} r " .
    "LEFT JOIN {$users_table} u_from ON r.from_user = u_from.ID " .
    "LEFT JOIN {$users_table} u_to ON r.to_user = u_to.ID " .
    "WHERE r.correspondence_id = %d ORDER BY r.created_at ASC",
    $letter->getId()
) );
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

// Helper function to get user display name safely
if ( ! function_exists( 'persian_oa_get_user_name' ) ) {
    function persian_oa_get_user_name( $user_id ) {
        $user = get_userdata($user_id);
        return $user ? $user->display_name : 'کاربر حذف شده';
    }
}
    // Fallback for letter date if invalid
    $letterDate = $letter->getLetterDate();
    if (empty($letterDate) || $letterDate === '0000-00-00' || $letterDate === '0000-00-00 00:00:00') {
        $letterDate = $letter->getCreatedAt();
    }
    
    // Logo for letterhead
    $logo_id = get_option('oa_title_icon_attachment_id');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    $org_name = get_bloginfo('name');
?>

<div class="oa-wrap">
    <!-- Header Actions -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '📄' ) ); ?></span>
                    مشاهده نامه وارده
                    <span class="oa-badge oa-badge-<?php echo esc_attr( $statusClass ); ?>" style="font-size: 14px; margin-right: 12px; vertical-align: middle;">
                        <?php echo esc_html( $statusLabel ); ?>
                    </span>
                </h1>
                <p class="oa-subtitle">
                    شماره: <?php echo esc_html( JalaliDate::convertNumbers( $letter->getNumber() ) ); ?> •
                    تاریخ: <?php echo esc_html( JalaliDate::format( $letterDate, 'date' ) ); ?>
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button class="oa-btn oa-btn-outline" id="oa-letterhead-toggle" onclick="toggleLetterheadMode()">
                    📝 سربرگ
                </button>
                <a href="javascript:history.back()" class="oa-btn oa-btn-outline">
                    ← بازگشت
                </a>
                <button class="oa-btn oa-btn-outline" onclick="window.print()">
                    🖨️ چاپ
                </button>
                <?php if (current_user_can('oa_create_referral')): ?>
                <button class="oa-btn oa-btn-primary" onclick="showReferralModal()">
                    ↪️ ارجاع نامه
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="oa-view-container">
        <!-- Letter Content (Paper Style) -->
        <div class="oa-paper" id="oa-paper-content">
            <!-- Letterhead Header (Hidden by default) -->
            <div class="oa-letterhead-header" style="display: none;">
                <div class="oa-lh-right">
                    <?php if ($logo_url): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" class="oa-lh-logo">
                    <?php endif; ?>
                    <div class="oa-lh-org-name"><?php echo esc_html($org_name); ?></div>
                </div>
                <div class="oa-lh-center">
                    <div class="oa-lh-basmala">بسمه تعالی</div>
                </div>
                    <div class="oa-lh-left">
                    <div class="oa-lh-meta-row">
                        <span>شماره:</span>
                        <span><?php echo esc_html( JalaliDate::convertNumbers( $letter->getNumber() ) ); ?></span>
                    </div>
                    <div class="oa-lh-meta-row">
                        <span>تاریخ:</span>
                        <span><?php echo esc_html( JalaliDate::format( $letterDate, 'date' ) ); ?></span>
                    </div>
                    <div class="oa-lh-meta-row">
                        <span>پیوست:</span>
                        <span><?php echo esc_html( ! empty( $attachments ) ? 'دارد' : 'ندارد' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Paper Header -->
            <div class="oa-paper-header">
                <div class="oa-paper-meta-row">
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">شماره نامه:</span>
                        <span class="oa-meta-value"><?php echo esc_html(JalaliDate::convertNumbers($letter->getNumber())); ?></span>
                    </div>
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">تاریخ:</span>
                        <span class="oa-meta-value"><?php echo esc_html( JalaliDate::format( $letterDate, 'date' ) ); ?></span>
                    </div>
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">پیوست:</span>
                        <span class="oa-meta-value"><?php echo esc_html( ! empty( $attachments ) ? 'دارد' : 'ندارد' ); ?></span>
                    </div>
                </div>
                
                <div class="oa-paper-meta-row" style="margin-top: 8px;">
                     <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">فرستنده:</span>
                        <span class="oa-meta-value"><?php echo esc_html($letter->getSender()); ?></span>
                    </div>
                    <?php if ($letter->getReferenceNumber()): ?>
                    <div class="oa-paper-meta-item">
                        <span class="oa-meta-label">عطف به:</span>
                        <span class="oa-meta-value"><?php echo esc_html(JalaliDate::convertNumbers($letter->getReferenceNumber())); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="oa-paper-subject">
                    <span class="oa-meta-label">موضوع:</span>
                    <strong><?php echo esc_html($letter->getSubject()); ?></strong>
                </div>
            </div>

            <!-- Paper Body -->
            <div class="oa-paper-body">
                <?php echo wp_kses_post(wpautop($letter->getContent() ?: $letter->getDescription())); ?>
            </div>

            <!-- Paper Footer (Signatures/Info) -->
            <div class="oa-paper-footer">
                <div class="oa-info-row">
                    <strong>اولویت:</strong>
                    <?php
                        $priorities = array( 'low' => 'عادی', 'medium' => 'متوسط', 'high' => 'زیاد', 'urgent' => 'فوری' );
                        echo esc_html( $priorities[ $letter->getPriority() ] ?? 'عادی' );
                    ?>
                </div>
                <div class="oa-info-row">
                    <strong>محرمانگی:</strong>
                    <?php
                        $confidentiality = array( 'normal' => 'عادی', 'confidential' => 'محرمانه', 'highly_confidential' => 'سری' );
                        echo esc_html( $confidentiality[ $letter->getConfidentiality() ] ?? 'عادی' );
                    ?>
                </div>
            </div>

            <!-- Letterhead Footer (Hidden by default) -->
            <div class="oa-letterhead-footer" style="display: none;">
                <div class="oa-lh-footer-content">
                    <?php echo esc_html(get_bloginfo('description')); ?>
                </div>
            </div>

            <!-- Attachments Section -->
            <?php if (!empty($attachments)): ?>
            <div class="oa-attachments-section">
                <h3>📎 پیوست‌ها</h3>
                <div class="oa-attachment-list">
                    <?php foreach ($attachments as $attachment): ?>
                        <a href="<?php echo esc_url($attachment->file_path); ?>" target="_blank" class="oa-attachment-item">
                            <span class="oa-attachment-icon">📄</span>
                            <span class="oa-attachment-name"><?php echo esc_html($attachment->file_name); ?></span>
                            <span class="oa-attachment-size">(<?php echo esc_html( size_format( $attachment->file_size ) ); ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar / Workflow Info -->
        <div class="oa-sidebar">
            <!-- Workflow Timeline -->
            <div class="oa-card">
                <div class="oa-card-header">
                    <h3>🔄 گردش کار</h3>
                </div>
                <div class="oa-workflow-timeline">
                    <!-- Initial Creation -->
                    <div class="oa-timeline-item">
                        <div class="oa-timeline-marker start"></div>
                        <div class="oa-timeline-content">
                            <div class="oa-timeline-header">
                                <strong>ثبت در سیستم</strong>
                                <span class="oa-timeline-date"><?php echo esc_html( JalaliDate::format( $letter->getCreatedAt(), 'datetime' ) ); ?></span>
                            </div>
                            <div class="oa-timeline-user">
                                توسط: <?php echo esc_html( persian_oa_get_user_name( $letter->getCreatedBy() ) ); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Referrals -->
                    <?php if (!empty($referrals)): ?>
                        <?php foreach ($referrals as $referral): ?>
                        <div class="oa-timeline-item">
                            <div class="oa-timeline-marker"></div>
                            <div class="oa-timeline-content">
                                <div class="oa-timeline-header">
                                    <strong>ارجاع به <?php echo esc_html($referral->to_name); ?></strong>
                                    <span class="oa-timeline-date"><?php echo esc_html( JalaliDate::format( $referral->created_at, 'datetime' ) ); ?></span>
                                </div>
                                <div class="oa-timeline-user">
                                    از طرف: <?php echo esc_html( $referral->from_name ); ?>
                                </div>
                                <?php if ($referral->comments): ?>
                                    <div class="oa-timeline-message">
                                        "<?php echo esc_html($referral->comments); ?>"
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CC Recipients -->
            <?php if (!empty($cc_recipients)): ?>
            <div class="oa-card oa-mt-4">
                <div class="oa-card-header">
                    <h3>👥 رونوشت‌ها</h3>
                </div>
                <div class="oa-card-body">
                    <ul class="oa-list">
                        <?php foreach ($cc_recipients as $uid): ?>
                            <li><?php echo esc_html( persian_oa_get_user_name( $uid ) ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* View Specific Styles */
.oa-view-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-top: 24px;
}

@media (max-width: 1000px) {
    .oa-view-container {
        grid-template-columns: 1fr;
    }
}

/* Paper Style */
.oa-paper {
    background: #fff;
    padding: 60px;
    border-radius: 2px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    min-height: 800px;
    position: relative;
}

.oa-paper-header {
    border-bottom: 2px solid #000;
    padding-bottom: 20px;
    margin-bottom: 40px;
}

.oa-paper-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.oa-paper-meta-item {
    display: flex;
    gap: 8px;
}

.oa-meta-label {
    font-weight: bold;
}

.oa-paper-subject {
    margin-top: 20px;
    font-size: 16px;
}

.oa-paper-body {
    font-size: 16px;
    line-height: 2;
    text-align: justify;
    margin-bottom: 60px;
    white-space: pre-wrap;
}

.oa-paper-footer {
    border-top: 1px solid #eee;
    padding-top: 20px;
    margin-top: auto;
    display: flex;
    gap: 24px;
    font-size: 13px;
    color: #666;
}

/* Attachments */
.oa-attachments-section {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px dashed #ccc;
}

.oa-attachment-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-right: 8px;
    margin-bottom: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}

.oa-attachment-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

/* Timeline */
.oa-workflow-timeline {
    position: relative;
    padding: 20px 0;
}

.oa-workflow-timeline::before {
    content: '';
    position: absolute;
    right: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.oa-timeline-item {
    position: relative;
    padding-right: 40px;
    margin-bottom: 24px;
}

.oa-timeline-marker {
    position: absolute;
    right: 14px;
    top: 6px;
    width: 14px;
    height: 14px;
    background: #fff;
    border: 3px solid var(--oa-primary);
    border-radius: 50%;
    z-index: 2;
}

.oa-timeline-marker.start {
    border-color: var(--oa-success);
}

.oa-timeline-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.oa-timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
    font-size: 13px;
}

.oa-timeline-date {
    font-size: 11px;
    color: #999;
}

.oa-timeline-user {
    font-size: 12px;
    color: #666;
}

.oa-timeline-message {
    margin-top: 8px;
    font-size: 13px;
    font-style: italic;
    color: #555;
    background: #fff;
    padding: 8px;
    border-radius: 4px;
    border-right: 3px solid var(--oa-primary);
}

/* Print Styles */
@media print {
    .oa-header, .oa-sidebar, #adminmenuback, #adminmenuwrap, #wpadminbar, #wpfooter {
        display: none !important;
    }
    
    #wpcontent, #wpbody-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .oa-wrap {
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
    }
    
    .oa-view-container {
        display: block !important;
    }
    
    .oa-paper {
        box-shadow: none !important;
        padding: 0 !important;
        border: none !important;
    }
}
</style>

<!-- Referral Modal -->
<div id="referral-modal" class="oa-modal" style="display: none;">
    <div class="oa-modal-content">
        <span class="oa-close" onclick="closeReferralModal()">&times;</span>
        <h2>ارجاع نامه</h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="oa_save_referral">
            <input type="hidden" name="correspondence_id" value="<?php echo esc_attr( (string) $letter->getId() ); ?>">
            <?php wp_nonce_field('oa_save_referral', 'oa_referral_nonce'); ?>
            
            <div class="oa-form-group">
                <label class="oa-label required">گیرنده</label>
                <select name="to_user" class="oa-select" required>
                    <option value="">انتخاب کنید...</option>
                    <?php 
                    $users = get_users(); // Reuse user list
                    foreach ($users as $user): 
                    ?>
                        <option value="<?php echo esc_attr( (string) $user->ID ); ?>">
                            <?php echo esc_html( $user->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="oa-form-group oa-mt-4">
                <label class="oa-label required">دستور / پیام</label>
                <textarea name="message" class="oa-textarea" rows="4" required placeholder="دستور خود را بنویسید..."></textarea>
            </div>
            
            <div style="margin-top: 20px; text-align: left;">
                <button type="button" class="oa-btn oa-btn-outline" onclick="closeReferralModal()">انصراف</button>
                <button type="submit" class="oa-btn oa-btn-primary">ثبت ارجاع</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReferralModal() {
    document.getElementById('referral-modal').style.display = 'block';
}

function closeReferralModal() {
    document.getElementById('referral-modal').style.display = 'none';
}

// Close modal when clicking outside
    window.onclick = function(event) {
    if (event.target == document.getElementById('referral-modal')) {
        closeReferralModal();
    }
}

function toggleLetterheadMode() {
    const paper = document.getElementById('oa-paper-content');
    const btn = document.getElementById('oa-letterhead-toggle');
    const header = document.querySelector('.oa-letterhead-header');
    
    paper.classList.toggle('oa-letterhead-mode');
    
    if (paper.classList.contains('oa-letterhead-mode')) {
        btn.innerHTML = '📝 حالت عادی';
        btn.classList.add('oa-btn-primary');
        btn.classList.remove('oa-btn-outline');
        header.style.display = 'flex';
    } else {
        btn.innerHTML = '📝 سربرگ';
        btn.classList.remove('oa-btn-primary');
        btn.classList.add('oa-btn-outline');
        header.style.display = 'none';
    }
}
</script>

<style>
/* Letterhead Mode Styles */
.oa-letterhead-mode {
    padding-top: 40px !important;
}

.oa-letterhead-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #000;
}

.oa-lh-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 200px;
}

.oa-lh-logo {
    max-width: 80px;
    max-height: 80px;
    margin-bottom: 10px;
}

.oa-lh-org-name {
    font-weight: bold;
    font-size: 16px;
    text-align: center;
}

.oa-lh-center {
    flex: 1;
    display: flex;
    justify-content: center;
    padding-top: 20px;
}

.oa-lh-basmala {
    font-family: 'Nastaliq', 'IranNastaliq', serif;
    font-size: 24px;
}

.oa-lh-left {
    width: 200px;
    font-size: 14px;
}

.oa-lh-meta-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.oa-letterhead-footer {
    border-top: 2px solid #000;
    margin-top: 60px;
    padding-top: 10px;
    text-align: center;
    font-size: 12px;
    color: #333;
}

/* Hide default meta row in letterhead mode */
.oa-letterhead-mode .oa-paper-header {
    border-bottom: none;
    margin-bottom: 20px;
    padding-bottom: 0;
}

.oa-letterhead-mode .oa-paper-meta-row:first-child {
    display: none;
}

.oa-letterhead-mode .oa-paper-footer {
    display: none;
}

/* Simple Modal Styles */
.oa-modal {
    display: none; 
    position: fixed; 
    z-index: 9999; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.5); 
    backdrop-filter: blur(4px);
}

.oa-modal-content {
    background-color: #fefefe;
    margin: 10% auto; 
    padding: 30px;
    border: 1px solid #888;
    width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.oa-close {
    color: #aaa;
    float: left;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.oa-close:hover,
.oa-close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

