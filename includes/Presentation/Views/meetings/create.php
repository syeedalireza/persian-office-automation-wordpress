<?php
/**
 * Create Meeting View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Form has nonce; GET only for default date/redirect.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

$defaultDate = '';
$defaultDateGregorian = '';
$defaultEndDate = '';
$defaultEndDateGregorian = '';
if ( isset( $_GET['date'] ) ) {
    $defaultDate = sanitize_text_field( wp_unslash( $_GET['date'] ) );
    $defaultDateGregorian = JalaliDate::jalaliToGregorianString($defaultDate);
    $defaultDate .= ' 09:00';
    $defaultDateGregorian .= ' 09:00:00';
    $defaultEndDateGregorian = gmdate('Y-m-d H:i:s', strtotime($defaultDateGregorian) + 3600);
    $defaultEndDate = JalaliDate::toJalali($defaultEndDateGregorian, 'Y/m/d') . ' ' . gmdate('H:i', strtotime($defaultEndDateGregorian));
} else {
    $defaultEndDateGregorian = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
    $defaultEndDate = JalaliDate::toJalali($defaultEndDateGregorian, 'Y/m/d') . ' ' . gmdate('H:i', strtotime($defaultEndDateGregorian));
}

$redirectTo = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';
$formErrors = get_transient('persian_oa_meeting_create_errors');
if ($formErrors) {
    delete_transient('persian_oa_meeting_create_errors');
}
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '➕' ) ); ?></span>
                    ثبت جلسه جدید
                </h1>
                <p class="oa-subtitle">برنامه‌ریزی و دعوت از همکاران</p>
            </div>
            <a href="<?php echo esc_url( $redirectTo === 'calendar' ? '?page=persian-oa-calendar' : '?page=persian-oa-meetings' ); ?>" class="oa-btn oa-btn-outline">
                ← بازگشت
            </a>
        </div>
    </div>

    <div class="oa-card">
        <?php if (!empty($formErrors)) : ?>
            <div class="oa-notice oa-notice-error" style="margin: 0 32px 24px; padding: 12px 16px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;">
                <strong>خطا در ثبت جلسه:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    <?php foreach ($formErrors as $err) : ?>
                        <li><?php echo esc_html($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding: 32px;">
            <input type="hidden" name="action" value="persian_oa_create_meeting">
            <?php wp_nonce_field('persian_oa_create_meeting', 'persian_oa_meeting_nonce'); ?>
            
            <?php if($redirectTo): ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirectTo); ?>">
            <?php endif; ?>

            <div class="oa-form-grid">
                <!-- عنوان -->
                <div class="oa-form-group" style="grid-column: span 2;">
                    <label class="oa-label required">عنوان جلسه</label>
                    <input type="text" name="title" class="oa-input" required placeholder="مثال: جلسه بررسی عملکرد ماهانه..." autofocus>
                </div>

                <!-- تاریخ و زمان شروع -->
                <div class="oa-form-group">
                    <label class="oa-label required">تاریخ و زمان شروع</label>
                    <div class="oa-date-time-row">
                        <div class="oa-input-group oa-flex-1">
                            <span class="oa-input-icon dashicons dashicons-calendar-alt"></span>
                            <input type="text" id="meeting-date-jalali" name="meeting_date" class="oa-input jalali-datepicker" 
                                   required readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;"
                                   value="<?php echo esc_attr( $defaultDate ? preg_replace( '/\s+\d{1,2}:\d{2}$/', '', $defaultDate ) : '' ); ?>">
                        </div>
                        <label class="oa-time-label">ساعت شروع</label>
                        <input type="time" id="meeting-time" class="oa-input oa-time-input" value="<?php echo esc_attr( $defaultDateGregorian ? gmdate( 'H:i', strtotime( $defaultDateGregorian ) ) : '09:00' ); ?>" required>
                    </div>
                    <input type="hidden" id="meeting-date-gregorian-date" value="<?php echo esc_attr( $defaultDateGregorian ? gmdate( 'Y-m-d', strtotime( $defaultDateGregorian ) ) : '' ); ?>">
                    <input type="hidden" id="meeting-date-gregorian" name="meeting_date_gregorian" value="<?php echo esc_attr($defaultDateGregorian); ?>">
                </div>

                <!-- تاریخ و زمان پایان -->
                <div class="oa-form-group">
                    <label class="oa-label required">تاریخ و زمان پایان</label>
                    <div class="oa-date-time-row">
                        <div class="oa-input-group oa-flex-1">
                            <span class="oa-input-icon dashicons dashicons-calendar-alt"></span>
                            <input type="text" id="meeting-end-date-jalali" name="end_date" class="oa-input jalali-datepicker" 
                                   required readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;"
                                   value="<?php echo esc_attr( $defaultEndDate ? preg_replace( '/\s+\d{1,2}:\d{2}$/', '', $defaultEndDate ) : '' ); ?>">
                        </div>
                        <label class="oa-time-label">ساعت پایان</label>
                        <input type="time" id="end-time" class="oa-input oa-time-input" value="<?php echo esc_attr( $defaultEndDateGregorian ? gmdate( 'H:i', strtotime( $defaultEndDateGregorian ) ) : '10:00' ); ?>" required>
                    </div>
                    <input type="hidden" id="meeting-end-date-gregorian-date" value="<?php echo esc_attr( $defaultEndDateGregorian ? gmdate( 'Y-m-d', strtotime( $defaultEndDateGregorian ) ) : '' ); ?>">
                    <input type="hidden" id="meeting-end-date-gregorian" name="end_date_gregorian" value="<?php echo esc_attr($defaultEndDateGregorian); ?>">
                </div>

                <!-- مدت زمان (خودکار از شروع و پایان) -->
                <div class="oa-form-group">
                    <label class="oa-label">مدت زمان</label>
                    <div class="oa-duration-display" id="oa-duration-display" style="padding: 10px 14px; background: #f1f5f9; border-radius: 8px; color: #475569; font-weight: 500;">
                        —
                    </div>
                    <input type="hidden" name="duration" id="oa-duration-minutes" value="60">
                </div>

                <!-- مکان -->
                <div class="oa-form-group">
                    <label class="oa-label required">مکان برگزاری</label>
                    <div class="oa-input-group">
                        <span class="oa-input-icon dashicons dashicons-location"></span>
                        <input type="text" name="location" class="oa-input" required placeholder="نام اتاق جلسه یا لینک آنلاین">
                    </div>
                </div>

                <!-- تکرار -->
                <div class="oa-form-group">
                    <label class="oa-label">تکرار جلسه</label>
                    <select name="recurrence" class="oa-select">
                        <option value="none">بدون تکرار</option>
                        <option value="daily">روزانه</option>
                        <option value="weekly">هفتگی</option>
                        <option value="monthly">ماهانه</option>
                    </select>
                </div>

                <!-- رنگ -->
                <div class="oa-form-group" style="grid-column: span 2;">
                    <label class="oa-label">رنگ نمایش در تقویم</label>
                    <div class="oa-color-picker">
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#3b82f6" checked>
                            <span class="swatch" style="background: #3b82f6;"></span>
                            <span class="name">آبی (عادی)</span>
                        </label>
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#ef4444">
                            <span class="swatch" style="background: #ef4444;"></span>
                            <span class="name">قرمز (مهم)</span>
                        </label>
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#10b981">
                            <span class="swatch" style="background: #10b981;"></span>
                            <span class="name">سبز (بازبینی)</span>
                        </label>
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#f59e0b">
                            <span class="swatch" style="background: #f59e0b;"></span>
                            <span class="name">نارنجی (آموزشی)</span>
                        </label>
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#8b5cf6">
                            <span class="swatch" style="background: #8b5cf6;"></span>
                            <span class="name">بنفش (مدیریتی)</span>
                        </label>
                        <label class="oa-color-option">
                            <input type="radio" name="color" value="#6b7280">
                            <span class="swatch" style="background: #6b7280;"></span>
                            <span class="name">سایر</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- شرکت کنندگان -->
            <div class="oa-form-group oa-mt-4">
                <label class="oa-label">شرکت کنندگان (دعوت شدگان)</label>
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; max-height: 200px; overflow-y: auto;">
                    <?php 
                    $users = get_users();
                    foreach ($users as $user): 
                    ?>
                        <label class="oa-checkbox-item">
                            <input type="checkbox" name="participants[]" value="<?php echo esc_attr( (string) $user->ID ); ?>">
                            <span class="avatar">
                                <?php echo get_avatar($user->ID, 24); ?>
                            </span>
                            <span class="name"><?php echo esc_html($user->display_name); ?></span>
                            <span class="email"><?php echo esc_html($user->user_email); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <small class="oa-help-text">افراد مورد نظر را از لیست بالا انتخاب کنید.</small>
            </div>

            <!-- توضیحات -->
            <div class="oa-form-group oa-mt-4">
                <label class="oa-label">توضیحات / دستور جلسه</label>
                <div class="oa-description-toolbar">
                    <button type="button" class="oa-toolbar-btn oa-btn-numbered" id="oa-add-numbered-item" title="افزودن مورد شماره‌دار">
                        <span class="dashicons dashicons-editor-ol"></span>
                        <span>افزودن مورد شماره‌دار</span>
                    </button>
                </div>
                <textarea name="description" id="oa-meeting-description" class="oa-textarea" rows="5" placeholder="شرح دستور جلسه و جزئیات دیگر... یا از دکمه بالا برای لیست شماره‌دار استفاده کنید."></textarea>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 24px;">
                <a href="<?php echo esc_url( $redirectTo === 'calendar' ? admin_url( 'admin.php?page=persian-oa-calendar' ) : admin_url( 'admin.php?page=persian-oa-meetings' ) ); ?>" class="oa-btn oa-btn-outline">
                    انصراف
                </a>
                <button type="submit" class="oa-btn oa-btn-primary" style="min-width: 140px;">
                    💾 ثبت جلسه
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.oa-date-time-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.oa-date-time-row .oa-flex-1 { flex: 1; min-width: 140px; }
.oa-time-label {
    font-size: 13px;
    color: #64748b;
    white-space: nowrap;
}
.oa-time-input {
    width: 100px;
    padding: 10px 12px;
}
.oa-input-group {
    position: relative;
    display: flex;
    align-items: center;
}
.oa-input-icon {
    position: absolute;
    right: 12px;
    color: #94a3b8;
    z-index: 1;
}
.oa-input-group .oa-input {
    padding-right: 36px;
}
.oa-color-picker {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.oa-color-option {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.2s;
}
.oa-color-option:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.oa-color-option input {
    margin: 0;
}
.oa-color-option .swatch {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}
.oa-color-option .name {
    font-size: 13px;
    color: #475569;
}

.oa-checkbox-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.1s;
}
.oa-checkbox-item:last-child {
    border-bottom: none;
}
.oa-checkbox-item:hover {
    background: #f8fafc;
}
.oa-checkbox-item .avatar img {
    border-radius: 50%;
    vertical-align: middle;
}
.oa-checkbox-item .name {
    font-weight: 500;
    color: #334155;
}
.oa-checkbox-item .email {
    font-size: 12px;
    color: #94a3b8;
    margin-right: auto;
}

.oa-description-toolbar {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
    flex-wrap: wrap;
}
.oa-toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.oa-toolbar-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #334155;
}
.oa-toolbar-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
</style>

<script>
jQuery(document).ready(function($) {
    function syncStartDateTime() {
        var datePart = $('#meeting-date-gregorian-date').val();
        var timePart = $('#meeting-time').val() || '09:00';
        if (datePart) $('#meeting-date-gregorian').val(datePart + ' ' + timePart + ':00');
    }
    function syncEndDateTime() {
        var datePart = $('#meeting-end-date-gregorian-date').val();
        var timePart = $('#end-time').val() || '10:00';
        if (datePart) $('#meeting-end-date-gregorian').val(datePart + ' ' + timePart + ':00');
    }
    function parseGregorian(val) {
        if (!val || !val.trim()) return null;
        var d = new Date(val.replace(/-/g, '/'));
        return isNaN(d.getTime()) ? null : d;
    }
    function updateDuration() {
        syncStartDateTime();
        syncEndDateTime();
        var startVal = $('#meeting-date-gregorian').val();
        var endVal = $('#meeting-end-date-gregorian').val();
        var start = parseGregorian(startVal);
        var end = parseGregorian(endVal);
        var $display = $('#oa-duration-display');
        var $minutes = $('#oa-duration-minutes');
        if (!start || !end) {
            $display.text('—');
            $minutes.val('0');
            return;
        }
        var diffMs = end.getTime() - start.getTime();
        var diffMins = Math.round(diffMs / 60000);
        if (diffMins < 0) {
            $display.text('زمان پایان باید بعد از شروع باشد');
            $minutes.val('0');
            return;
        }
        $minutes.val(String(diffMins));
        if (diffMins < 60) {
            $display.text(diffMins + ' دقیقه');
        } else if (diffMins === 60) {
            $display.text('۱ ساعت');
        } else if (diffMins === 90) {
            $display.text('۱ ساعت و نیم');
        } else if (diffMins % 60 === 0) {
            $display.text((diffMins / 60) + ' ساعت');
        } else {
            $display.text(Math.floor(diffMins / 60) + ' ساعت و ' + (diffMins % 60) + ' دقیقه');
        }
    }
    if (typeof SimplePersianDatePicker !== 'undefined') {
        new SimplePersianDatePicker(
            document.getElementById('meeting-date-jalali'),
            document.getElementById('meeting-date-gregorian-date'),
            { defaultToday: false, onSelect: function() { syncStartDateTime(); updateDuration(); } }
        );
        new SimplePersianDatePicker(
            document.getElementById('meeting-end-date-jalali'),
            document.getElementById('meeting-end-date-gregorian-date'),
            { defaultToday: false, onSelect: function() { syncEndDateTime(); updateDuration(); } }
        );
    }
    $('#meeting-time, #end-time').on('change input', updateDuration);
    setInterval(updateDuration, 800);
    updateDuration();

    // شماره‌دار کردن دستور جلسه
    function getNextNumberedIndex(text) {
        if (!text || !text.trim()) return 1;
        var lines = text.split(/\r?\n/);
        var maxNum = 0;
        var re = /^\s*(\d+)[\.\)]\s/;
        for (var i = 0; i < lines.length; i++) {
            var m = lines[i].match(re);
            if (m) {
                var n = parseInt(m[1], 10);
                if (n > maxNum) maxNum = n;
            }
        }
        return maxNum + 1;
    }
    function insertNumberedItem() {
        var ta = document.getElementById('oa-meeting-description');
        if (!ta) return;
        var start = ta.selectionStart, end = ta.selectionEnd;
        var text = ta.value;
        var nextNum = getNextNumberedIndex(text);
        var insertion = (start > 0 && text[start - 1] !== '\n' ? '\n' : '') + nextNum + '. ';
        ta.value = text.slice(0, start) + insertion + text.slice(end);
        ta.selectionStart = ta.selectionEnd = start + insertion.length;
        ta.focus();
    }
    $('#oa-add-numbered-item').on('click', insertNumberedItem);
});
</script>