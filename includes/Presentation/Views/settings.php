<?php
/**
 * Settings View - Beautiful Tabs Design
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Tabs/display only; forms have nonce in controller.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\UIHelper;

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
$allowed_tabs = [ 'general', 'upload', 'numbering', 'categories', 'workflow', 'advanced' ];
if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
    $active_tab = 'general';
}
$settings_message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : '';
?>

<div class="oa-wrap">
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '⚙️' ) ); ?></span>
                    تنظیمات سیستم
                </h1>
                <p class="oa-subtitle">
                    پیکربندی و تنظیمات دبیرخانه اتوماسیون
                </p>
            </div>
        </div>
    </div>

    <?php if ( $settings_message === 'success' ): ?>
    <div class="oa-card oa-mb-4" style="background: #d4edda; border-right: 4px solid #28a745;">
        <div style="padding: 16px;">
            <strong style="color: #155724;">✅ تنظیمات با موفقیت ذخیره شد.</strong>
        </div>
    </div>
    <?php endif; ?>

    <!-- Beautiful Tabs -->
    <div class="oa-card oa-mb-4">
        <div style="padding: 24px; border-bottom: 1px solid var(--oa-gray-200);">
            <div style="display: flex; gap: 8px;">
                <a href="?page=persian-oa-settings&tab=general" class="<?php echo esc_attr( $active_tab == 'general' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    🎛️ عمومی
                </a>
                <a href="?page=persian-oa-settings&tab=upload" class="<?php echo esc_attr( $active_tab == 'upload' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    📎 آپلود فایل
                </a>
                <a href="?page=persian-oa-settings&tab=numbering" class="<?php echo esc_attr( $active_tab == 'numbering' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    🔢 شماره‌گذاری
                </a>
                <a href="?page=persian-oa-settings&tab=categories" class="<?php echo esc_attr( $active_tab == 'categories' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    📂 دسته‌بندی‌ها
                </a>
                <a href="?page=persian-oa-settings&tab=workflow" class="<?php echo esc_attr( $active_tab == 'workflow' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    🔄 گردش کار
                </a>
                <a href="?page=persian-oa-settings&tab=advanced" class="<?php echo esc_attr( $active_tab == 'advanced' ? 'oa-btn oa-btn-primary' : 'oa-btn oa-btn-outline' ); ?>" style="padding: 12px 24px;">
                    ⚡ پیشرفته
                </a>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="oa-card">
        <div style="padding: 40px;">
            <?php if ($active_tab == 'general') { ?>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 24px; color: var(--oa-gray-900);">
                    🎛️ تنظیمات عمومی
                </h2>
                
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="oa_save_general_settings">
                    <?php wp_nonce_field('oa_general_settings', 'oa_general_nonce'); ?>
                    
                    <div style="display: grid; gap: 24px; max-width: 600px;">
                        <div>
                            <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                                آیکون عنوان صفحات
                            </label>
                            <?php 
                            $icon_attachment_id = get_option('oa_title_icon_attachment_id', 0);
                            $icon_url = $icon_attachment_id ? wp_get_attachment_image_url($icon_attachment_id, 'thumbnail') : '';
                            ?>
                            <div style="display: flex; gap: 16px; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <input type="file" name="oa_title_icon" id="oa_title_icon" accept="image/*" style="display: none;" onchange="handleIconPreview(this)">
                                    <label for="oa_title_icon" class="oa-btn oa-btn-outline" style="cursor: pointer; display: inline-block; margin-bottom: 12px;">
                                        📷 انتخاب آیکون
                                    </label>
                                    <div id="oa_icon_preview" style="margin-top: 12px;">
                                        <?php if ($icon_url): ?>
                                            <div style="position: relative; display: inline-block;">
                                                <img src="<?php echo esc_url($icon_url); ?>" alt="آیکون فعلی" style="width: 56px; height: 56px; object-fit: contain; border-radius: var(--oa-radius-lg); border: 2px solid var(--oa-gray-200); padding: 4px; background: white;">
                                                <button type="button" onclick="removeIcon()" style="position: absolute; top: -8px; right: -8px; background: var(--oa-danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; line-height: 1;">×</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="oa_title_icon_remove" id="oa_title_icon_remove" value="0">
                                    <p style="font-size: 13px; color: var(--oa-gray-500); margin-top: 8px;">
                                        این آیکون در هدر تمام صفحات پلاگین نمایش داده می‌شود. فرمت‌های مجاز: JPG, PNG, SVG, GIF
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                                نام سازمان
                            </label>
                            <input type="text" class="oa-input" value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" placeholder="نام سازمان خود را وارد کنید">
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                                زبان پیش‌فرض
                            </label>
                            <select class="oa-select">
                                <option>فارسی</option>
                                <option>English</option>
                            </select>
                        </div>
                        
                        <div>
                            <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                <input type="checkbox" checked style="width: 20px; height: 20px;">
                                <span style="font-size: 15px; font-weight: 600; color: var(--oa-gray-700);">
                                    فعال‌سازی اعلان‌های ایمیل
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="oa-btn oa-btn-primary oa-btn-lg">
                            💾 ذخیره تغییرات
                        </button>
                    </div>
                </form>
                
// Script moved to assets/js/admin.js
                
            <?php } elseif ($active_tab == 'upload') { ?>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 24px; color: var(--oa-gray-900);">
                    📎 تنظیمات آپلود فایل
                </h2>
                
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="oa_save_upload_settings">
                    <?php wp_nonce_field('oa_upload_settings', 'oa_upload_nonce'); ?>
                    
                    <div style="display: grid; gap: 24px; max-width: 600px;">
                        <div>
                            <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                                حداکثر حجم فایل مجاز (مگابایت)
                            </label>
                            <input type="number" name="persian_oa_max_upload_size" class="oa-input" 
                                   value="<?php echo esc_attr(get_option('persian_oa_max_upload_size', 10)); ?>" 
                                   min="1" max="100" step="1" placeholder="10">
                            <p style="font-size: 13px; color: var(--oa-gray-500); margin-top: 8px;">
                                حداکثر حجم فایل‌هایی که کاربران می‌توانند آپلود کنند (1 تا 100 مگابایت)
                            </p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 16px; color: var(--oa-gray-700);">
                                فرمت‌های مجاز
                            </label>
                            
                            <div style="display: grid; gap: 12px;">
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="persian_oa_allowed_types[]" value="pdf" 
                                           <?php echo esc_attr( in_array( 'pdf', get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] ), true ) ? 'checked' : '' ); ?>
                                           style="width: 20px; height: 20px;">
                                    <span style="font-size: 15px; color: var(--oa-gray-700);">
                                        PDF (.pdf)
                                    </span>
                                </label>
                                
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="persian_oa_allowed_types[]" value="doc,docx" 
                                           <?php $allowed = get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] ); echo esc_attr( ( in_array( 'doc', $allowed, true ) || in_array( 'docx', $allowed, true ) ) ? 'checked' : '' ); ?>
                                           style="width: 20px; height: 20px;">
                                    <span style="font-size: 15px; color: var(--oa-gray-700);">
                                        Microsoft Word (.doc, .docx)
                                    </span>
                                </label>
                                
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="persian_oa_allowed_types[]" value="xls,xlsx" 
                                           <?php $allowed = get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] ); echo esc_attr( ( in_array( 'xls', $allowed, true ) || in_array( 'xlsx', $allowed, true ) ) ? 'checked' : '' ); ?>
                                           style="width: 20px; height: 20px;">
                                    <span style="font-size: 15px; color: var(--oa-gray-700);">
                                        Microsoft Excel (.xls, .xlsx)
                                    </span>
                                </label>
                                
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="persian_oa_allowed_types[]" value="jpg,jpeg,png" 
                                           <?php $allowed = get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] ); echo esc_attr( ( in_array( 'jpg', $allowed, true ) || in_array( 'jpeg', $allowed, true ) || in_array( 'png', $allowed, true ) ) ? 'checked' : '' ); ?>
                                           style="width: 20px; height: 20px;">
                                    <span style="font-size: 15px; color: var(--oa-gray-700);">
                                        تصاویر (.jpg, .jpeg, .png)
                                    </span>
                                </label>
                                
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="persian_oa_allowed_types[]" value="zip" 
                                           <?php echo esc_attr( in_array( 'zip', get_option( 'persian_oa_allowed_types', [ 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'xls', 'xlsx' ] ), true ) ? 'checked' : '' ); ?>
                                           style="width: 20px; height: 20px;">
                                    <span style="font-size: 15px; color: var(--oa-gray-700);">
                                        فایل فشرده (.zip)
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <div style="padding: 16px; background: #e8f4fd; border-right: 4px solid #0073aa; border-radius: 4px;">
                            <strong style="color: #0073aa;">💡 نکته:</strong>
                            <p style="margin: 8px 0 0 0; color: #0073aa; font-size: 14px;">
                                این تنظیمات برای تمام فرم‌های آپلود فایل در سیستم اعمال می‌شود. حجم مجاز توسط سرور شما نیز محدود می‌شود.
                            </p>
                        </div>
                        
                        <button type="submit" class="oa-btn oa-btn-primary oa-btn-lg">
                            💾 ذخیره تغییرات
                        </button>
                    </div>
                </form>
                
            <?php } elseif ($active_tab == 'numbering') { ?>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 24px; color: var(--oa-gray-900);">
                    🔢 تنظیمات شماره‌گذاری
                </h2>
                
                <div style="display: grid; gap: 24px; max-width: 600px;">
                    <div>
                        <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                            فرمت شماره نامه‌های وارده
                        </label>
                        <input type="text" class="oa-input" value="{year}/{month}/{number}" placeholder="مثال: {year}/{month}/{number}">
                        <p style="font-size: 13px; color: var(--oa-gray-500); margin-top: 8px;">
                            متغیرها: {year}, {month}, {day}, {number}
                        </p>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: var(--oa-gray-700);">
                            فرمت شماره نامه‌های صادره
                        </label>
                        <input type="text" class="oa-input" value="{year}/{month}/{number}" placeholder="مثال: OUT-{year}-{number}">
                    </div>
                    
                    <button class="oa-btn oa-btn-primary oa-btn-lg">
                        💾 ذخیره تغییرات
                    </button>
                </div>
                
                
            <?php } elseif ($active_tab == 'categories') { ?>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 24px; color: var(--oa-gray-900);">
                    📂 مدیریت دسته‌بندی‌ها
                </h2>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="oa_save_category_settings">
                    <?php wp_nonce_field('oa_category_settings', 'oa_category_nonce'); ?>

                    <div class="oa-card oa-mb-4" style="padding: 24px;">
                         <p style="margin-bottom: 20px; color: var(--oa-gray-600);">
                            در این بخش می‌توانید دسته‌بندی‌های نامه‌های وارده را مدیریت کنید.
                        </p>

                        <?php
                        $defaults = \OfficeAutomation\Common\Constants::LETTER_TYPES;
                        $categories = get_option('persian_oa_incoming_categories', $defaults);
                        if (empty($categories)) {
                            $categories = $defaults;
                        }
                        ?>

                        <div id="oa-categories-container" style="display: grid; gap: 12px; max-width: 600px;">
                            <?php 
                            $i = 0;
                            foreach ($categories as $key => $label): 
                            ?>
                            <div class="oa-category-row" style="display: flex; gap: 12px; align-items: center;">
                                <input type="hidden" name="categories[<?php echo esc_attr( (string) $i ); ?>][key]" value="<?php echo esc_attr( $key ); ?>">
                                <input type="text" name="categories[<?php echo esc_attr( (string) $i ); ?>][label]" class="oa-input" value="<?php echo esc_attr($label); ?>" placeholder="عنوان دسته‌بندی" required>
                                <button type="button" class="oa-btn oa-btn-danger oa-btn-sm" onclick="this.parentElement.remove()" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                                    حذف
                                </button>
                            </div>
                            <?php 
                            $i++;
                            endforeach; 
                            ?>
                        </div>

                        <button type="button" class="oa-btn oa-btn-outline oa-mt-4" onclick="addCategory()">
                            ➕ افزودن دسته‌بندی جدید
                        </button>
                    </div>

                    <button type="submit" class="oa-btn oa-btn-primary oa-btn-lg">
                        💾 ذخیره تغییرات
                    </button>
                </form>

// Script moved to assets/js/admin.js

            <?php } elseif ($active_tab == 'workflow') { 
$workflows = get_option('persian_oa_workflow_definitions', []);
$general_settings = get_option('persian_oa_workflow_general_settings', ['allow_self_approval' => 0, 'holiday_counting' => 'stop']);
                global $wp_roles;
                $roles = $wp_roles->roles;
            ?>
                <!-- Modal Styles -->
                <style>
                    .oa-modal {
                        display: none;
                        position: fixed;
                        z-index: 10000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        overflow: auto;
                        background-color: rgba(0,0,0,0.5);
                        backdrop-filter: blur(4px);
                        animation: fadeIn 0.3s;
                    }
                    .oa-modal-content {
                        background-color: #fefefe;
                        margin: 5% auto;
                        padding: 0;
                        border: 1px solid #888;
                        width: 90%;
                        max-width: 800px;
                        border-radius: var(--oa-radius-xl);
                        box-shadow: var(--oa-shadow-2xl);
                        animation: slideDown 0.3s;
                    }
                    .oa-modal-header {
                        padding: 20px 30px;
                        border-bottom: 1px solid var(--oa-gray-200);
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        background: var(--oa-gray-50);
                    }
                    .oa-modal-title {
                        margin: 0;
                        font-size: 18px;
                        font-weight: 700;
                        color: var(--oa-gray-900);
                    }
                    .oa-modal-close {
                        color: var(--oa-gray-500);
                        font-size: 28px;
                        font-weight: bold;
                        border: none;
                        background: none;
                        cursor: pointer;
                        line-height: 1;
                    }
                    .oa-modal-close:hover {
                        color: var(--oa-danger);
                    }
                    .oa-modal-body {
                        padding: 30px;
                        max-height: 70vh;
                        overflow-y: auto;
                    }
                    .oa-modal-footer {
                        padding: 20px 30px;
                        border-top: 1px solid var(--oa-gray-200);
                        display: flex;
                        justify-content: flex-end;
                        gap: 12px;
                        background: var(--oa-gray-50);
                    }
                    .step-item {
                        background: white;
                        border: 1px solid var(--oa-gray-300);
                        border-radius: var(--oa-radius-md);
                        padding: 15px;
                        margin-bottom: 10px;
                        display: flex;
                        gap: 15px;
                        align-items: center;
                        transition: all 0.2s;
                    }
                    .step-item:hover {
                        border-color: var(--oa-primary);
                        box-shadow: var(--oa-shadow-sm);
                    }
                    .step-handle {
                        cursor: move;
                        color: var(--oa-gray-400);
                        font-size: 20px;
                    }
                    .step-content {
                        flex: 1;
                        display: grid;
                        grid-template-columns: 1fr 1fr 1fr;
                        gap: 10px;
                    }
                </style>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2 style="font-size: 24px; font-weight: 700; margin: 0; color: var(--oa-gray-900);">
                        🔄 تنظیمات گردش کار
                    </h2>
                    <button type="button" class="oa-btn oa-btn-primary" onclick="openWorkflowModal()">
                        ➕ افزودن فرآیند جدید
                    </button>
                </div>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="workflow-form">
                    <input type="hidden" name="action" value="oa_save_workflow_settings">
                    <input type="hidden" name="oa_workflow_definitions" id="oa_workflow_definitions_input">
                    <?php wp_nonce_field('oa_workflow_settings', 'oa_workflow_nonce'); ?>

                    <!-- General Workflow Settings -->
                    <div class="oa-card oa-mb-4">
                        <div style="padding: 20px; border-bottom: 1px solid var(--oa-gray-200);">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 600;">⚙️ تنظیمات عمومی</h3>
                        </div>
                        <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                            <div>
                                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                    <input type="checkbox" name="oa_wf_allow_self_approval" value="1" <?php checked($general_settings['allow_self_approval'], 1); ?> style="width: 20px; height: 20px;">
                                    <span style="font-size: 14px; color: var(--oa-gray-700);">
                                        اجازه تایید درخواست توسط خود درخواست‌کننده (در صورتی که تاییدکننده باشد)
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                                    محاسبه مهلت در روزهای تعطیل
                                </label>
                                <select name="oa_wf_holiday_counting" class="oa-select">
                                    <option value="stop" <?php selected($general_settings['holiday_counting'], 'stop'); ?>>توقف شمارش (پیشنهادی)</option>
                                    <option value="count" <?php selected($general_settings['holiday_counting'], 'count'); ?>>شمارش عادی</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Workflows List -->
                    <div class="oa-card">
                        <div style="padding: 20px; border-bottom: 1px solid var(--oa-gray-200);">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 600;">📋 فرآیندهای فعال</h3>
                        </div>
                        <div style="padding: 0;">
                            <?php if (empty($workflows)): ?>
                                <div style="padding: 40px; text-align: center; color: var(--oa-gray-500);">
                                    <p>هنوز هیچ گردش کاری تعریف نشده است.</p>
                                </div>
                            <?php else: ?>
                                <table class="wp-list-table widefat fixed striped" style="border: none;">
                                    <thead>
                                        <tr>
                                            <th style="padding: 15px;">نام فرآیند</th>
                                            <th style="padding: 15px;">شناسه</th>
                                            <th style="padding: 15px;">تعداد مراحل</th>
                                            <th style="padding: 15px;">مهلت (ساعت)</th>
                                            <th style="padding: 15px;">وضعیت</th>
                                            <th style="padding: 15px; text-align: left;">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="workflow-list-body">
                                        <?php foreach ($workflows as $wf): ?>
                                            <tr data-id="<?php echo esc_attr($wf['id']); ?>">
                                                <td style="padding: 15px;"><strong><?php echo esc_html($wf['name']); ?></strong></td>
                                                <td style="padding: 15px;"><code><?php echo esc_html($wf['id']); ?></code></td>
                                                <td style="padding: 15px;"><?php echo esc_html( (string) count( $wf['steps'] ) ); ?> مرحله</td>
                                                <td style="padding: 15px;"><?php echo esc_html($wf['sla']); ?></td>
                                                <td style="padding: 15px;">
                                                    <?php if ($wf['is_active']): ?>
                                                        <span class="oa-badge oa-badge-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="oa-badge oa-badge-danger">غیرفعال</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 15px; text-align: left;">
                                                    <button type="button" class="button" onclick='editWorkflow(<?php echo wp_json_encode($wf); ?>)'>ویرایش</button>
                                                    <button type="button" class="button button-link-delete" onclick="deleteWorkflow('<?php echo esc_js( (string) $wf['id'] ); ?>')">حذف</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="oa-btn oa-btn-primary oa-btn-lg" onclick="prepareWorkflowData()">
                            💾 ذخیره تمام تنظیمات
                        </button>
                    </div>
                </form>

                <!-- Workflow Modal -->
                <div id="workflowModal" class="oa-modal">
                    <div class="oa-modal-content">
                        <div class="oa-modal-header">
                            <h3 class="oa-modal-title">تعریف گردش کار</h3>
                            <button type="button" class="oa-modal-close" onclick="closeWorkflowModal()">×</button>
                        </div>
                        <div class="oa-modal-body">
                            <form id="workflow-edit-form">
                                <input type="hidden" id="wf_id">
                                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                                    <div>
                                        <label class="oa-label">نام فرآیند</label>
                                        <input type="text" id="wf_name" class="oa-input" placeholder="مثال: درخواست مرخصی">
                                    </div>
                                    <div>
                                        <label class="oa-label">مهلت کل (ساعت)</label>
                                        <input type="number" id="wf_sla" class="oa-input" value="24">
                                    </div>
                                </div>
                                <div style="margin-bottom: 20px;">
                                    <label class="oa-label">توضیحات</label>
                                    <textarea id="wf_description" class="oa-input" rows="2" placeholder="توضیحات این فرآیند..."></textarea>
                                </div>
                                <div style="margin-bottom: 20px;">
                                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="checkbox" id="wf_active" checked style="width: 18px; height: 18px;">
                                        <span style="font-weight: 600;">این فرآیند فعال باشد</span>
                                    </label>
                                </div>

                                <div class="oa-section-title" style="margin: 20px 0 10px; font-weight: 700; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                                    مراحل گردش کار
                                </div>
                                <div id="wf_steps_container" style="background: #f9fafb; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; min-height: 100px;">
                                    <!-- Steps will be added here -->
                                </div>
                                <button type="button" class="oa-btn oa-btn-outline oa-btn-sm" style="margin-top: 15px; width: 100%; justify-content: center;" onclick="addWorkflowStep()">
                                    ➕ افزودن مرحله جدید
                                </button>
                            </form>
                        </div>
                        <div class="oa-modal-footer">
                            <button type="button" class="oa-btn oa-btn-secondary" onclick="closeWorkflowModal()">انصراف</button>
                            <button type="button" class="oa-btn oa-btn-primary" onclick="saveWorkflowToMemory()">ثبت تغییرات</button>
                        </div>
                    </div>
                </div>

// Script moved to assets/js/admin.js

            <?php } else { ?>
                <div style="text-align: center; padding: 60px; color: var(--oa-gray-500);">
                    <div style="font-size: 64px; margin-bottom: 20px;">⚙️</div>
                    <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 12px;">
                        تنظیمات <?php echo esc_html( $active_tab ); ?>
                    </h3>
                    <p>این بخش در نسخه آینده اضافه خواهد شد</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

