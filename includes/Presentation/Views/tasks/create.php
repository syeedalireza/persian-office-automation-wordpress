<?php
/**
 * Create Task View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Form has nonce; GET only for default deadline/redirect.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
use OfficeAutomation\Infrastructure\Repository\TaskRepository;

$taskRepo = new TaskRepository();
// Get tasks for parent dropdown (simplified)
$allTasks = $taskRepo->findAll(100);

$defaultDate = '';
$defaultDateGregorian = '';
if ( isset( $_GET['deadline'] ) ) {
    $defaultDate = sanitize_text_field( wp_unslash( $_GET['deadline'] ) );
    $defaultDateGregorian = JalaliDate::jalaliToGregorianString( $defaultDate );
}
$redirectTo = isset( $_GET['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) : '';
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '➕' ) ); ?></span>
                    تعریف وظیفه جدید
                </h1>
                <p class="oa-subtitle">تخصیص کار و مدیریت مهلت‌ها</p>
            </div>
            <a href="<?php echo esc_url( $redirectTo === 'calendar' ? admin_url( 'admin.php?page=persian-oa-calendar' ) : admin_url( 'admin.php?page=persian-oa-tasks' ) ); ?>" class="oa-btn oa-btn-outline">
                ← بازگشت
            </a>
        </div>
    </div>

    <div class="oa-card">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding: 32px;">
            <input type="hidden" name="action" value="oa_create_task">
            <?php wp_nonce_field('oa_create_task', 'oa_task_nonce'); ?>
            
            <?php if($redirectTo): ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirectTo); ?>">
            <?php endif; ?>

            <div class="oa-form-grid">
                <!-- عنوان -->
                <div class="oa-form-group" style="grid-column: span 2;">
                    <label class="oa-label required">عنوان وظیفه</label>
                    <input type="text" name="title" class="oa-input" required placeholder="عنوان کار را دقیق وارد کنید..." autofocus>
                </div>

                <!-- مسئول -->
                <div class="oa-form-group">
                    <label class="oa-label required">مسئول انجام</label>
                    <select name="assigned_to" class="oa-select" required>
                        <option value="">انتخاب مسئول انجام...</option>
                        <?php 
                        $users = get_users();
                        $current_user_id = get_current_user_id();
                        foreach ($users as $user): 
                        ?>
                            <option value="<?php echo esc_attr( (string) $user->ID ); ?>" <?php selected( $user->ID, $current_user_id ); ?>>
                                <?php echo esc_html($user->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- وظیفه والد -->
                <div class="oa-form-group">
                    <label class="oa-label">زیرمجموعه وظیفه (والد)</label>
                    <select name="parent_id" class="oa-select">
                        <option value="">بدون والد</option>
                        <?php foreach ($allTasks as $t): ?>
                            <option value="<?php echo esc_attr( (string) $t->getId() ); ?>"><?php echo esc_html( $t->getTitle() ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- تاریخ شروع -->
                <div class="oa-form-group">
                    <label class="oa-label">تاریخ شروع</label>
                    <div class="oa-input-group">
                        <span class="oa-input-icon dashicons dashicons-calendar-alt"></span>
                        <input type="text" id="start-date-jalali" name="start_date" class="oa-input jalali-datepicker" 
                               readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;">
                    </div>
                    <input type="hidden" id="start-date-gregorian" name="start_date_gregorian">
                </div>

                <!-- مهلت -->
                <div class="oa-form-group">
                    <label class="oa-label">مهلت انجام (ددلاین)</label>
                    <div class="oa-input-group">
                        <span class="oa-input-icon dashicons dashicons-calendar-alt"></span>
                        <input type="text" id="deadline-jalali" name="deadline" class="oa-input jalali-datepicker" 
                               readonly placeholder="انتخاب تاریخ" style="cursor: pointer; background-color: #ffffff;"
                               value="<?php echo esc_attr($defaultDate); ?>">
                    </div>
                    <input type="hidden" id="deadline-gregorian" name="deadline_gregorian" value="<?php echo esc_attr($defaultDateGregorian); ?>">
                </div>

                <!-- تکرار -->
                <div class="oa-form-group">
                    <label class="oa-label">تکرار</label>
                    <label class="oa-checkbox-label">
                        <input type="checkbox" name="is_recurring" value="1" id="oa-recurring-check">
                        این وظیفه تکرار شونده است
                    </label>
                    <select name="recurrence_pattern" id="oa-recurring-pattern" class="oa-select" style="margin-top: 10px; display: none;">
                        <option value="daily">روزانه</option>
                        <option value="weekly">هفتگی</option>
                        <option value="monthly">ماهانه</option>
                    </select>
                </div>

                <!-- تخمین زمان -->
                <div class="oa-form-group">
                    <label class="oa-label">تخمین زمان (ساعت)</label>
                    <input type="number" name="estimated_time" class="oa-input" min="0" step="0.5" placeholder="مثلاً 2">
                </div>

                <!-- دسته‌بندی -->
                <div class="oa-form-group">
                    <label class="oa-label">دسته بندی / پروژه</label>
                    <input type="text" name="category" class="oa-input" list="category-suggestions" placeholder="نام پروژه یا دسته">
                    <datalist id="category-suggestions">
                        <option value="عمومی">
                        <option value="توسعه نرم‌افزار">
                        <option value="اداری">
                        <option value="پشتیبانی">
                        <option value="مارکتینگ">
                    </datalist>
                </div>

                <!-- اولویت -->
                <div class="oa-form-group">
                    <label class="oa-label">اولویت</label>
                    <div class="oa-priority-selector">
                        <label class="priority-option low">
                            <input type="radio" name="priority" value="low">
                            <span class="badge">کم</span>
                        </label>
                        <label class="priority-option medium">
                            <input type="radio" name="priority" value="medium" checked>
                            <span class="badge">متوسط</span>
                        </label>
                        <label class="priority-option high">
                            <input type="radio" name="priority" value="high">
                            <span class="badge">زیاد</span>
                        </label>
                        <label class="priority-option urgent">
                            <input type="radio" name="priority" value="urgent">
                            <span class="badge">فوری</span>
                        </label>
                    </div>
                </div>

                <!-- وضعیت اولیه -->
                <div class="oa-form-group">
                    <label class="oa-label">وضعیت اولیه</label>
                    <select name="status" class="oa-select">
                        <option value="todo" selected>برای انجام</option>
                        <option value="in_progress">در حال انجام</option>
                        <option value="review">در حال بررسی</option>
                        <option value="completed">تکمیل شده</option>
                    </select>
                </div>
            </div>

            <!-- توضیحات -->
            <div class="oa-form-group oa-mt-4">
                <label class="oa-label">توضیحات تکمیلی</label>
                <textarea name="description" class="oa-textarea" rows="5" placeholder="شرح کامل کار، نیازمندی‌ها و جزئیات..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 24px;">
                <a href="<?php echo esc_url( $redirectTo === 'calendar' ? admin_url( 'admin.php?page=persian-oa-calendar' ) : admin_url( 'admin.php?page=persian-oa-tasks' ) ); ?>" class="oa-btn oa-btn-outline">
                    انصراف
                </a>
                <button type="submit" class="oa-btn oa-btn-primary" style="min-width: 140px;">
                    💾 ذخیره وظیفه
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Enqueue inline script for date pickers and recurring pattern
wp_add_inline_script('oa-tasks', '
jQuery(document).ready(function($) {
    if (typeof SimplePersianDatePicker !== "undefined") {
        new SimplePersianDatePicker(
            document.getElementById("deadline-jalali"),
            document.getElementById("deadline-gregorian"),
            { defaultToday: false }
        );
        new SimplePersianDatePicker(
            document.getElementById("start-date-jalali"),
            document.getElementById("start-date-gregorian"),
            { defaultToday: false }
        );
    }

    $("#oa-recurring-check").change(function() {
        if($(this).is(":checked")) {
            $("#oa-recurring-pattern").slideDown();
        } else {
            $("#oa-recurring-pattern").slideUp();
        }
    });
});
');
?>

// Task-specific script moved to assets/js/tasks.js
