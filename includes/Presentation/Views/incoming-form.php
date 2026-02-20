<?php
/**
 * Incoming Letter Form View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.DateTime.RestrictedFunctions.date_date
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
use OfficeAutomation\Common\Constants;

$is_edit = isset($letter) && $letter;
$page_title = $is_edit ? 'ویرایش نامه وارده' : 'ثبت نامه وارده جدید';
$page_icon = $is_edit ? '✏️' : '➕';
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( esc_html( $page_icon ) ) ); ?></span>
                    <?php echo esc_html( $page_title ); ?>
                </h1>
                <p class="oa-subtitle">
                    تاریخ: <?php echo esc_html( JalaliDate::now( 'l، j F Y' ) ); ?>
                </p>
            </div>
            <a href="?page=persian-oa-incoming-letters" class="oa-btn oa-btn-outline">
                ← بازگشت به لیست
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="oa-card oa-mb-4" style="background: #fee; border-right: 4px solid #c33;">
        <div style="padding: 20px;">
            <h3 style="color: #c33; margin: 0 0 12px 0;">❌ خطاهای فرم:</h3>
            <ul style="margin: 0; padding-right: 20px; color: #c33;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="oa-letter-form">
        <input type="hidden" name="action" value="persian_oa_save_incoming_letter">
        <?php wp_nonce_field( 'persian_oa_save_incoming_letter', 'persian_oa_incoming_nonce' ); ?>
        <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($letter->getId()); ?>">
        <?php endif; ?>

        <!-- Tabs -->
        <div class="oa-tabs-container">
            <div class="oa-tabs">
                <button type="button" class="oa-tab active" data-tab="basic">
                    📋 اطلاعات اولیه
                </button>
                <button type="button" class="oa-tab" data-tab="content">
                    📝 محتوا و توضیحات
                </button>
                <button type="button" class="oa-tab" data-tab="workflow">
                    🔄 گردش کار
                </button>
                <button type="button" class="oa-tab" data-tab="archive">
                    📁 بایگانی
                </button>
            </div>
        </div>

        <!-- Tab 1: Basic Information -->
        <div class="oa-card oa-tab-content active" data-tab-content="basic">
            <div style="padding: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 24px 0; padding-bottom: 16px; border-bottom: 2px solid var(--oa-gray-200);">
                    📋 اطلاعات اولیه نامه
                </h3>

                <div class="oa-form-grid">
                    <!-- شماره نامه -->
                    <div class="oa-form-group">
                        <label class="oa-label<?php echo esc_attr( $is_edit ? ' required' : '' ); ?>">شماره نامه وارده</label>
                        <input type="text" name="number" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr( JalaliDate::convertNumbers( $letter->getNumber() ) ) : esc_attr( JalaliDate::convertNumbers( $next_number ) ); ?>" 
                               <?php echo esc_attr( $is_edit ? 'required' : 'readonly' ); ?> placeholder="<?php echo esc_attr( $is_edit ? 'مثال: IN-1403/0001' : '' ); ?>">
                        <small class="oa-help-text"><?php echo esc_html( $is_edit ? 'شماره یکتای نامه در سیستم' : 'شماره به‌صورت خودکار تولید می‌شود' ); ?></small>
                    </div>

                    <!-- شماره نامه مرجع -->
                    <div class="oa-form-group">
                        <label class="oa-label">شماره نامه مرجع</label>
                        <input type="text" name="reference_number" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr(JalaliDate::convertNumbers($letter->getReferenceNumber())) : ''; ?>" 
                               placeholder="شماره نامه از سازمان مبدا">
                        <small class="oa-help-text">شماره اصلی نامه از سازمان فرستنده</small>
                    </div>

                    <!-- تاریخ نامه -->
                    <div class="oa-form-group">
                        <label class="oa-label required">تاریخ نامه</label>
                        <input type="text" id="letter-date-jalali" name="letter_date" class="oa-input jalali-datepicker" 
                               value="<?php echo esc_attr( $is_edit && $letter->getLetterDate() ? JalaliDate::format( $letter->getLetterDate(), 'date' ) : '' ); ?>" 
                               required readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;">
                        <input type="hidden" id="letter-date-gregorian" name="letter_date_gregorian" 
                               value="<?php echo esc_attr( $is_edit && $letter->getLetterDate() ? gmdate( 'Y-m-d', strtotime( $letter->getLetterDate() ) ) : '' ); ?>">
                        <small class="oa-help-text">تاریخ درج شده روی نامه</small>
                    </div>

                    <!-- تاریخ دریافت -->
                    <div class="oa-form-group">
                        <label class="oa-label">تاریخ دریافت</label>
                        <input type="text" id="received-date-jalali" name="received_at" class="oa-input jalali-datepicker" 
                               value="<?php echo esc_attr( $is_edit && $letter->getReceivedAt() ? JalaliDate::format( $letter->getReceivedAt(), 'date' ) : JalaliDate::now( 'Y/m/d' ) ); ?>" 
                               readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;">
                        <input type="hidden" id="received-date-gregorian" name="received_at_gregorian" 
                               value="<?php echo esc_attr( $is_edit && $letter->getReceivedAt() ? gmdate( 'Y-m-d', strtotime( $letter->getReceivedAt() ) ) : gmdate( 'Y-m-d' ) ); ?>">
                        <small class="oa-help-text">تاریخ دریافت نامه در دبیرخانه</small>
                    </div>
                </div>

                <!-- موضوع -->
                <div class="oa-form-group oa-mt-4">
                    <label class="oa-label required">موضوع نامه</label>
                    <input type="text" name="subject" class="oa-input" 
                           value="<?php echo $is_edit ? esc_attr($letter->getSubject()) : ''; ?>" 
                           required placeholder="خلاصه موضوع نامه را وارد کنید">
                    <small class="oa-help-text">حداقل 5 کاراکتر</small>
                </div>

                <div class="oa-form-grid oa-mt-4">
                    <!-- فرستنده -->
                    <div class="oa-form-group">
                        <label class="oa-label required">نام فرستنده / سازمان</label>
                        <input type="text" name="sender" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getSender()) : ''; ?>" 
                               required placeholder="نام شخص یا سازمان فرستنده">
                    </div>

                    <!-- واحد فرستنده -->
                    <div class="oa-form-group">
                        <label class="oa-label">واحد / بخش فرستنده</label>
                        <input type="text" name="sender_department" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getSenderDepartment()) : ''; ?>" 
                               placeholder="نام واحد یا بخش">
                    </div>

                    <!-- تلفن فرستنده -->
                    <div class="oa-form-group">
                        <label class="oa-label">شماره تماس</label>
                        <input type="text" name="sender_phone" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getSenderPhone()) : ''; ?>" 
                               placeholder="021-12345678">
                    </div>

                    <!-- ایمیل فرستنده -->
                    <div class="oa-form-group">
                        <label class="oa-label">ایمیل</label>
                        <input type="email" name="sender_email" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getSenderEmail()) : ''; ?>" 
                               placeholder="email@example.com">
                    </div>
                </div>

                <div class="oa-form-grid oa-mt-4">
                    <!-- دسته‌بندی -->
                    <div class="oa-form-group">
                        <label class="oa-label">نوع نامه</label>
                        <select name="category" class="oa-select" id="letter-type-select">
                            <option value="">انتخاب کنید</option>
                            <?php 
                            // Use settings if available, otherwise use defaults
                            $categories = get_option('persian_oa_incoming_categories', Constants::LETTER_TYPES);
                            
                            foreach ($categories as $key => $label): 
                                $selected = ($is_edit && $letter->getCategory() === (string)$key) ? 'selected' : '';
                            ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- اولویت -->
                    <div class="oa-form-group">
                        <label class="oa-label required">اولویت</label>
                        <select name="priority" class="oa-select" required>
                            <option value="low" <?php echo esc_attr( ( $is_edit && $letter->getPriority() === 'low' ) ? 'selected' : '' ); ?>>🟢 کم</option>
                            <option value="medium" <?php echo esc_attr( ( ! $is_edit || $letter->getPriority() === 'medium' ) ? 'selected' : '' ); ?>>🟡 متوسط</option>
                            <option value="high" <?php echo esc_attr( ( $is_edit && $letter->getPriority() === 'high' ) ? 'selected' : '' ); ?>>🟠 زیاد</option>
                            <option value="urgent" <?php echo esc_attr( ( $is_edit && $letter->getPriority() === 'urgent' ) ? 'selected' : '' ); ?>>🔴 فوری</option>
                        </select>
                    </div>

                    <!-- محرمانگی -->
                    <div class="oa-form-group">
                        <label class="oa-label">سطح محرمانگی</label>
                        <select name="confidentiality" class="oa-select">
                            <option value="normal" <?php echo esc_attr( ( ! $is_edit || $letter->getConfidentiality() === 'normal' ) ? 'selected' : '' ); ?>>عادی</option>
                            <option value="confidential" <?php echo esc_attr( ( $is_edit && $letter->getConfidentiality() === 'confidential' ) ? 'selected' : '' ); ?>>محرمانه</option>
                            <option value="highly_confidential" <?php echo esc_attr( ( $is_edit && $letter->getConfidentiality() === 'highly_confidential' ) ? 'selected' : '' ); ?>>خیلی محرمانه</option>
                        </select>
                    </div>

                    <!-- وضعیت -->
                    <div class="oa-form-group">
                        <label class="oa-label required">وضعیت</label>
                        <select name="status" class="oa-select" required>
                            <option value="draft" <?php echo esc_attr( ( ! $is_edit || $letter->getStatus() === 'draft' ) ? 'selected' : '' ); ?>>📝 پیش‌نویس</option>
                            <option value="pending" <?php echo esc_attr( ( $is_edit && $letter->getStatus() === 'pending' ) ? 'selected' : '' ); ?>>⏳ در انتظار</option>
                            <option value="approved" <?php echo esc_attr( ( $is_edit && $letter->getStatus() === 'approved' ) ? 'selected' : '' ); ?>>✅ تایید شده</option>
                            <option value="rejected" <?php echo esc_attr( ( $is_edit && $letter->getStatus() === 'rejected' ) ? 'selected' : '' ); ?>>❌ رد شده</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Content -->
        <div class="oa-card oa-tab-content" data-tab-content="content">
            <div style="padding: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 24px 0; padding-bottom: 16px; border-bottom: 2px solid var(--oa-gray-200);">
                    📝 محتوا و پیوست‌ها
                </h3>

                <!-- خلاصه -->
                <div class="oa-form-group">
                    <label class="oa-label">خلاصه / توضیح مختصر</label>
                    <?php 
                    $description = $is_edit ? $letter->getDescription() : '';
                    ?>
                    <textarea name="description" id="editor-description" class="oa-textarea" style="visibility:hidden; height:0;"><?php echo esc_textarea($description); ?></textarea>
                    <small class="oa-help-text">توضیح کوتاه برای نمایش در لیست نامه‌ها</small>
                </div>

                <!-- محتوای کامل -->
                <div class="oa-form-group oa-mt-4">
                    <label class="oa-label">متن کامل نامه</label>
                    <?php 
                    $content = $is_edit ? $letter->getContent() : '';
                    ?>
                    <textarea name="content" id="editor-content" class="oa-textarea" style="visibility:hidden; height:0;"><?php echo esc_textarea($content); ?></textarea>
                    <small class="oa-help-text">متن کامل و تفصیلی نامه</small>
                </div>

                <!-- پیوست‌ها -->
                <div class="oa-form-group oa-mt-4">
                    <label class="oa-label">پیوست فایل</label>
                    <div class="oa-file-upload">
                        <input type="file" name="attachments[]" id="attachments" class="oa-file-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.xls,.xlsx">
                        <label for="attachments" class="oa-file-label">
                            📎 انتخاب فایل‌ها
                            <span style="font-size: 13px; color: var(--oa-gray-600);">PDF, Word, Excel, تصویر, ZIP (حداکثر <?php echo esc_html(get_option('persian_oa_max_upload_size', 10)); ?>MB)</span>
                        </label>
                    </div>
                    <div id="file-list" class="oa-file-list"></div>
                    
                    <?php if ($is_edit && !empty($attachments)): ?>
                        <div class="oa-existing-files oa-mt-3">
                            <strong>فایل‌های موجود:</strong>
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="oa-file-item">
                                    📄 <?php echo esc_html($attachment->file_name); ?>
                                    <span class="oa-file-size">(<?php echo esc_html( size_format( $attachment->file_size ) ); ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- تگ‌ها و کلمات کلیدی -->
                <div class="oa-form-grid oa-mt-4">
                    <div class="oa-form-group">
                        <label class="oa-label">تگ‌ها</label>
                        <input type="text" name="tags" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getTags()) : ''; ?>" 
                               placeholder="تگ1، تگ2، تگ3">
                        <small class="oa-help-text">با کاما از هم جدا کنید</small>
                    </div>

                    <div class="oa-form-group">
                        <label class="oa-label">کلمات کلیدی</label>
                        <input type="text" name="keywords" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getKeywords()) : ''; ?>" 
                               placeholder="کلیدواژه1، کلیدواژه2">
                        <small class="oa-help-text">برای جستجوی بهتر</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Workflow -->
        <div class="oa-card oa-tab-content" data-tab-content="workflow">
            <div style="padding: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 24px 0; padding-bottom: 16px; border-bottom: 2px solid var(--oa-gray-200);">
                    🔄 گردش کار و ارجاع
                </h3>

                <div class="oa-form-grid">
                    <!-- گیرنده اصلی -->
                    <div class="oa-form-group">
                        <label class="oa-label">گیرنده اصلی</label>
                        <select name="primary_recipient" class="oa-select">
                            <option value="">انتخاب کنید</option>
                            <?php foreach ($users as $user): ?>
<option value="<?php echo esc_attr( (string) $user->ID ); ?>"
                                        <?php echo esc_attr( ( $is_edit && (int) $letter->getPrimaryRecipient() === (int) $user->ID ) ? 'selected' : '' ); ?>>
                                    <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="oa-help-text">مسئول اصلی پاسخگویی به نامه</small>
                    </div>

                    <!-- مهلت پاسخ -->
                    <div class="oa-form-group">
                        <label class="oa-label">مهلت پاسخگویی</label>
                        <input type="text" id="deadline-jalali" name="deadline" class="oa-input jalali-datepicker" 
                               value="<?php echo esc_attr( $is_edit && $letter->getDeadline() ? JalaliDate::format( $letter->getDeadline(), 'date' ) : '' ); ?>" 
                               readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;">
                        <input type="hidden" id="deadline-gregorian" name="deadline_gregorian" 
                               value="<?php echo esc_attr( $is_edit && $letter->getDeadline() ? gmdate( 'Y-m-d', strtotime( $letter->getDeadline() ) ) : '' ); ?>">
                        <small class="oa-help-text">تاریخ پایان مهلت پاسخ</small>
                    </div>
                </div>

                <!-- رونوشت به -->
                <div class="oa-form-group oa-mt-4">
                    <label class="oa-label">رونوشت به (CC)</label>
                    <select name="cc_recipients[]" class="oa-select" multiple size="6">
                        <?php foreach ($users as $user): ?>
<option value="<?php echo esc_attr( (string) $user->ID ); ?>"
                                    <?php echo esc_attr( ( $is_edit && in_array( (int) $user->ID, $cc_recipients, true ) ) ? 'selected' : '' ); ?>>
                                <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="oa-help-text">برای انتخاب چند نفر، Ctrl را نگه دارید</small>
                </div>

                <!-- دستورالعمل -->
                <div class="oa-form-group oa-mt-4">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="oa-label" style="margin-bottom: 0;">دستورالعمل مدیر</label>
                        <select id="referral-action-select" class="oa-select" style="width: auto; min-width: 200px; padding: 4px 8px; font-size: 13px;">
                            <option value="">انتخاب دستور سریع...</option>
                            <?php foreach (Constants::REFERRAL_ACTIONS as $key => $label): ?>
                                <option value="<?php echo esc_attr($label); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <textarea name="instruction" id="instruction-text" class="oa-textarea" rows="4" placeholder="دستورالعمل‌ها و توضیحات برای گیرنده..."><?php echo $is_edit ? esc_textarea($letter->getInstruction()) : ''; ?></textarea>
                    <small class="oa-help-text">راهنمایی برای نحوه رسیدگی به نامه</small>
                </div>

                <!-- یادداشت‌های داخلی -->
                <div class="oa-form-group oa-mt-4">
                    <label class="oa-label">یادداشت‌های داخلی</label>
                    <textarea name="notes" class="oa-textarea" rows="3" placeholder="یادداشت‌های خصوصی (فقط برای مدیران قابل مشاهده)"><?php echo $is_edit ? esc_textarea($letter->getNotes()) : ''; ?></textarea>
                    <small class="oa-help-text">این یادداشت‌ها فقط برای استفاده داخلی است</small>
                </div>
            </div>
        </div>

        <!-- Tab 4: Archive -->
        <div class="oa-card oa-tab-content" data-tab-content="archive">
            <div style="padding: 32px;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--oa-gray-900); margin: 0 0 24px 0; padding-bottom: 16px; border-bottom: 2px solid var(--oa-gray-200);">
                    📁 اطلاعات بایگانی
                </h3>

                <div class="oa-form-grid">
                    <!-- کد بایگانی -->
                    <div class="oa-form-group">
                        <label class="oa-label">کد بایگانی</label>
                        <input type="text" name="archive_code" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getArchiveCode()) : ''; ?>" 
                               placeholder="مثال: ARC-1403-001">
                        <small class="oa-help-text">کد یکتای بایگانی</small>
                    </div>

                    <!-- محل بایگانی -->
                    <div class="oa-form-group">
                        <label class="oa-label">محل بایگانی فیزیکی</label>
                        <input type="text" name="physical_location" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getPhysicalLocation()) : ''; ?>" 
                               placeholder="مثال: انبار شماره 2">
                        <small class="oa-help-text">محل نگهداری نسخه فیزیکی</small>
                    </div>

                    <!-- قفسه / فولدر -->
                    <div class="oa-form-group">
                        <label class="oa-label">قفسه / فولدر</label>
                        <input type="text" name="shelf_folder" class="oa-input" 
                               value="<?php echo $is_edit ? esc_attr($letter->getShelfFolder()) : ''; ?>" 
                               placeholder="مثال: قفسه A، ردیف 3">
                        <small class="oa-help-text">مشخصات دقیق محل نگهداری</small>
                    </div>
                </div>

                <div class="oa-info-box oa-mt-4">
                    <strong>💡 نکته:</strong> اطلاعات بایگانی برای پیگیری و یافتن نسخه فیزیکی نامه استفاده می‌شود.
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="oa-card">
            <div style="padding: 24px; display: flex; gap: 12px; justify-content: flex-end; background: var(--oa-gray-50); border-top: 2px solid var(--oa-gray-200);">
                <a href="?page=persian-oa-incoming-letters" class="oa-btn oa-btn-outline oa-btn-lg">
                    ❌ انصراف
                </a>
                <button type="submit" name="status" value="draft" class="oa-btn oa-btn-outline oa-btn-lg">
                    💾 ذخیره پیش‌نویس
                </button>
                <button type="submit" name="status" value="pending" class="oa-btn oa-btn-primary oa-btn-lg">
                    ✅ <?php echo esc_html( $is_edit ? 'ویرایش نامه' : 'ثبت نهایی' ); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('✅ Form loaded');
    console.log('✅ SimplePersianDatePicker:', typeof SimplePersianDatePicker !== 'undefined');
    
    // Letter Type Selection
    $('#letter-type-select').on('change', function() {
        var type = $(this).val();
        var text = $(this).find('option:selected').text();
        var subjectInput = $('input[name="subject"]');
        
        if (type && !subjectInput.val()) {
            subjectInput.val(text);
        }
    });

    // Referral Action Selection
    $('#referral-action-select').on('change', function() {
        var action = $(this).val();
        var textarea = $('#instruction-text');
        var currentVal = textarea.val();
        
        if (action) {
            if (currentVal) {
                textarea.val(currentVal + '\n' + action);
            } else {
                textarea.val(action);
            }
            // Reset select
            $(this).val('');
        }
    });
    
    // Tab switching
    $('.oa-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.oa-tab').removeClass('active');
        $(this).addClass('active');
        $('.oa-tab-content').removeClass('active');
        $('.oa-tab-content[data-tab-content="' + tab + '"]').addClass('active');
    });
    
    // File upload preview
    $('#attachments').on('change', function() {
        var files = this.files;
        var fileList = $('#file-list');
        fileList.html('');
        
        var maxSizeMB = <?php echo esc_js(get_option('persian_oa_max_upload_size', 10)); ?>;
        var maxSizeBytes = maxSizeMB * 1024 * 1024;
        var allowedTypes = <?php echo wp_json_encode(get_option('persian_oa_allowed_types', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx'])); ?>;
        var hasError = false;
        
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var size = (file.size / 1024 / 1024).toFixed(2);
            var ext = file.name.split('.').pop().toLowerCase();
            
            if (file.size > maxSizeBytes) {
                fileList.append('<div class="oa-file-item" style="color: #c33; border: 1px solid #c33;">❌ ' + file.name + ' <span class="oa-file-size">(' + size + ' MB - بیش از حد مجاز)</span></div>');
                hasError = true;
            } else if (allowedTypes.indexOf(ext) === -1) {
                fileList.append('<div class="oa-file-item" style="color: #c33; border: 1px solid #c33;">❌ ' + file.name + ' <span class="oa-file-size">(فرمت مجاز نیست)</span></div>');
                hasError = true;
            } else {
                fileList.append('<div class="oa-file-item">📄 ' + file.name + ' <span class="oa-file-size">(' + size + ' MB)</span></div>');
            }
        }
        
        $('button[type="submit"]').prop('disabled', hasError);
        if (hasError) {
            fileList.prepend('<div style="padding:12px;background:#fee;border:1px solid #c33;border-radius:4px;margin-bottom:12px;color:#c33;">⚠️ برخی فایل‌ها نامعتبر هستند.</div>');
        }
    });
    
    // Initialize Date Pickers
    if (typeof SimplePersianDatePicker !== 'undefined') {
        console.log('🚀 Initializing date pickers...');
        
        // تاریخ نامه
        new SimplePersianDatePicker(
            document.getElementById('letter-date-jalali'),
            document.getElementById('letter-date-gregorian'),
            {
                defaultToday: true,
                onSelect: function(jalali, gregorian) {
                    console.log('✅ Letter date:', jalali);
                }
            }
        );
        
        // تاریخ دریافت
        new SimplePersianDatePicker(
            document.getElementById('received-date-jalali'),
            document.getElementById('received-date-gregorian'),
            {
                defaultToday: true,
                onSelect: function(jalali, gregorian) {
                    console.log('✅ Received date:', jalali);
                }
            }
        );
        
        // مهلت پاسخ
        new SimplePersianDatePicker(
            document.getElementById('deadline-jalali'),
            document.getElementById('deadline-gregorian'),
            {
                defaultToday: false,
                onSelect: function(jalali, gregorian) {
                    console.log('✅ Deadline:', jalali);
                }
            }
        );
        
        console.log('🎉 All date pickers initialized!');
    } else {
        console.error('❌ SimplePersianDatePicker not loaded');
    }

    // Initialize CKEditor 5
    if (typeof ClassicEditor !== 'undefined') {
        const editorConfig = {
            language: 'fa',
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ],
            heading: {
                options: [
                    { model: 'paragraph', title: 'پاراگراف', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'تیتر ۱', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'تیتر ۲', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'تیتر ۳', class: 'ck-heading_heading3' }
                ]
            }
        };

        ['editor-description', 'editor-content'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                ClassicEditor
                    .create(element, editorConfig)
                    .then(editor => {
                        console.log('✅ CKEditor initialized for ' + id);
                        editor.model.document.on('change:data', () => {
                            element.value = editor.getData();
                        });
                    })
                    .catch(error => {
                        console.error('❌ CKEditor error:', error);
                    });
            }
        });
    } else {
        console.error('❌ ClassicEditor is not defined');
    }
});
</script>







