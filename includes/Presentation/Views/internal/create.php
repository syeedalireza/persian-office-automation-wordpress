<?php
/**
 * Internal Letters - Create View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\UIHelper;

$users = get_users(['fields' => ['ID', 'display_name'], 'orderby' => 'display_name']);
$list_url = esc_url(admin_url('admin.php?page=persian-oa-internal'));
?>

<div class="oa-wrap oa-internal-create">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post(UIHelper::getTitleIcon('✉️')); ?></span>
                    نامه جدید
                </h1>
                <p class="oa-subtitle">
                    <a href="<?php echo esc_url( $list_url ); ?>" class="oa-breadcrumb-link">مکاتبات داخلی</a>
                    <span class="oa-breadcrumb-sep">/</span>
                    ارسال نامه به همکار
                </p>
            </div>
            <a href="<?php echo esc_url( $list_url ); ?>" class="oa-btn oa-btn-outline">
                ← بازگشت به لیست
            </a>
        </div>
    </div>

    <div class="oa-card oa-form-card">
        <div class="oa-form-card-body">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="oa-form oa-letter-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="persian_oa_create_internal_letter">
                <?php wp_nonce_field('persian_oa_create_internal_letter_nonce', '_wpnonce'); ?>

                <!-- Section: گیرنده و تنظیمات -->
                <div class="oa-form-section">
                    <h3 class="oa-form-section-title">
                        <span class="dashicons dashicons-admin-users"></span>
                        گیرنده و تنظیمات
                    </h3>
                    <div class="oa-form-grid oa-form-grid-2">
                        <div class="oa-form-group">
                            <label class="oa-label required" for="recipient_id">گیرنده</label>
                            <select name="recipient_id" id="recipient_id" class="oa-input oa-select" required>
                                <option value="">— انتخاب گیرنده —</option>
                                <?php foreach ($users as $user) : ?>
                                    <?php if ((int) $user->ID !== get_current_user_id()) : ?>
                                        <option value="<?php echo esc_attr((string) $user->ID); ?>">
                                            <?php echo esc_html($user->display_name); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="oa-form-group">
                            <label class="oa-label" for="priority">اولویت</label>
                            <select name="priority" id="priority" class="oa-input oa-select">
                                <option value="normal">عادی</option>
                                <option value="high">فوری</option>
                                <option value="urgent">آنی</option>
                            </select>
                        </div>
                        <div class="oa-form-group">
                            <label class="oa-label" for="confidentiality">سطح محرمانگی</label>
                            <select name="confidentiality" id="confidentiality" class="oa-input oa-select">
                                <option value="normal">عادی</option>
                                <option value="confidential">محرمانه</option>
                                <option value="highly_confidential">خیلی محرمانه</option>
                            </select>
                        </div>
                    </div>
                    <div class="oa-form-group" style="margin-top: 20px;">
                        <label class="oa-label" for="cc_recipients">رونوشت به (CC)</label>
                        <select name="cc_recipients[]" id="cc_recipients" class="oa-input oa-select" multiple size="4">
                            <?php foreach ($users as $user) : ?>
                                <?php if ((int) $user->ID !== get_current_user_id()) : ?>
                                    <option value="<?php echo esc_attr((string) $user->ID); ?>">
                                        <?php echo esc_html($user->display_name); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <p class="oa-field-hint">برای انتخاب چند نفر، Ctrl (ویندوز) یا ⌘ (مک) را نگه دارید.</p>
                    </div>
                </div>

                <!-- Section: موضوع -->
                <div class="oa-form-section">
                    <h3 class="oa-form-section-title">
                        <span class="dashicons dashicons-edit-large"></span>
                        موضوع نامه
                    </h3>
                    <div class="oa-form-group">
                        <label class="oa-label required" for="subject">موضوع</label>
                        <input type="text" name="subject" id="subject" class="oa-input" required
                               placeholder="موضوع نامه را به صورت خلاصه وارد کنید..."
                               autofocus>
                    </div>
                </div>

                <!-- Section: متن نامه - CKEditor 5 -->
                <div class="oa-form-section oa-form-section-editor">
                    <h3 class="oa-form-section-title">
                        <span class="dashicons dashicons-editor-alignleft"></span>
                        متن نامه
                    </h3>
                    <div class="oa-form-group">
                        <label class="oa-label required" for="oa-internal-letter-content">متن نامه</label>
                        <div class="oa-ckeditor-wrapper">
                            <textarea name="content" id="oa-internal-letter-content" class="oa-textarea oa-editor-source" rows="14" placeholder="متن نامه را اینجا بنویسید..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: پیوست -->
                <div class="oa-form-section">
                    <h3 class="oa-form-section-title">
                        <span class="dashicons dashicons-paperclip"></span>
                        پیوست فایل
                    </h3>
                    <div class="oa-form-group">
                        <label class="oa-label" for="attachments">فایل‌های پیوست</label>
                        <div class="oa-file-upload">
                            <input type="file" name="attachments[]" id="attachments" class="oa-file-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.xls,.xlsx">
                            <label for="attachments" class="oa-file-label">
                                📎 انتخاب فایل‌ها
                                <span class="oa-file-hint">PDF, Word, Excel, تصویر, ZIP (حداکثر <?php echo esc_html((string) get_option('persian_oa_max_upload_size', 10)); ?>MB)</span>
                            </label>
                        </div>
                        <div id="oa-internal-file-list" class="oa-file-list"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="oa-form-actions oa-form-actions-footer">
                    <button type="submit" name="submit_action" value="send" class="oa-btn oa-btn-primary oa-btn-lg">
                        <span class="dashicons dashicons-email-alt"></span>
                        ارسال نامه
                    </button>
                    <button type="submit" name="submit_action" value="draft" class="oa-btn oa-btn-outline oa-btn-lg">
                        <span class="dashicons dashicons-saved"></span>
                        ذخیره پیش‌نویس
                    </button>
                    <a href="<?php echo esc_url( $list_url ); ?>" class="oa-btn oa-btn-outline">
                        انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function() {
    var fileInput = document.getElementById('attachments');
    var fileList = document.getElementById('oa-internal-file-list');
    if (fileInput && fileList) {
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            var files = Array.prototype.slice.call(this.files);
            files.forEach(function(f) {
                var span = document.createElement('span');
                span.className = 'oa-file-item';
                span.textContent = '\uD83D\uDCC4 ' + f.name + ' (' + (f.size < 1024 ? f.size + ' B' : (f.size < 1024*1024 ? (f.size/1024).toFixed(1) + ' KB' : (f.size/1024/1024).toFixed(1) + ' MB')) + ')';
                fileList.appendChild(span);
            });
        });
    }
})();
</script>
<style>
/* Page-specific: Internal Create - بهبود ظاهر بدون تغییر رفتار */
.oa-internal-create .oa-breadcrumb-link {
    color: var(--oa-gray-500);
    text-decoration: none;
    transition: color 0.2s;
}
.oa-internal-create .oa-breadcrumb-link:hover {
    color: var(--oa-primary);
}
.oa-internal-create .oa-breadcrumb-sep {
    color: var(--oa-gray-400);
    margin: 0 8px;
}
.oa-form-card { margin-bottom: 24px; }
.oa-form-card-body { padding: 32px; }
@media (max-width: 768px) {
    .oa-form-card-body { padding: 20px; }
}
.oa-form-section {
    margin-bottom: 32px;
    padding-bottom: 28px;
    border-bottom: 1px solid var(--oa-gray-200);
}
.oa-form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.oa-form-section-editor { margin-bottom: 24px; }
.oa-form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 700;
    color: var(--oa-gray-800);
    margin: 0 0 20px 0;
}
.oa-form-section-title .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: var(--oa-primary);
}
.oa-form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .oa-form-grid-2 { grid-template-columns: 1fr; }
}
.oa-file-upload { margin-top: 8px; }
.oa-file-input { display: none; }
.oa-file-label {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    padding: 16px 24px;
    border: 2px dashed var(--oa-gray-300);
    border-radius: var(--oa-radius-md);
    background: var(--oa-gray-50);
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}
.oa-file-label:hover { border-color: var(--oa-primary); background: #f0fdf4; }
.oa-file-hint { font-size: 12px; color: var(--oa-gray-600); margin-top: 6px; }
.oa-file-list { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
.oa-file-list .oa-file-item {
    font-size: 13px; color: var(--oa-gray-700);
    padding: 6px 10px; background: var(--oa-gray-100); border-radius: 6px;
}
.oa-select-multiple { min-height: 100px; }
.oa-field-hint {
    margin: 8px 0 0;
    font-size: 12px;
    color: var(--oa-gray-500);
}
.oa-field-hint kbd {
    padding: 2px 6px;
    font-size: 11px;
    background: var(--oa-gray-100);
    border-radius: 4px;
    border: 1px solid var(--oa-gray-300);
}
.oa-form-actions-footer {
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid var(--oa-gray-200);
    flex-wrap: wrap;
}
.oa-form-actions-footer .oa-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}
/* CKEditor 5 wrapper - ظاهر یکپارچه با تم پلاگین */
.oa-ckeditor-wrapper { margin-top: 8px; }
.oa-ckeditor-wrapper .ck.ck-editor { direction: rtl; }
.oa-ckeditor-wrapper .ck.ck-editor__main > .ck-editor__editable {
    min-height: 320px;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
    font-size: 15px;
    line-height: 1.8;
    border-radius: var(--oa-radius-md);
}
.oa-ckeditor-wrapper .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
    border-color: var(--oa-gray-300);
}
.oa-ckeditor-wrapper .ck.ck-editor__main > .ck-editor__editable:focus {
    border-color: var(--oa-primary);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
.oa-ckeditor-wrapper .ck.ck-toolbar {
    border-radius: var(--oa-radius-md) var(--oa-radius-md) 0 0;
    border-color: var(--oa-gray-300);
    background: var(--oa-gray-50);
}
.oa-ckeditor-wrapper .ck.ck-toolbar .ck-toolbar__separator { background: var(--oa-gray-300); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('oa-internal-letter-content');
    if (!el || typeof ClassicEditor === 'undefined') return;
    ClassicEditor.create(el, {
        language: 'fa',
        placeholder: 'متن نامه را اینجا بنویسید...',
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ],
        heading: {
            options: [
                { model: 'paragraph', title: 'پاراگراف', class: 'ck-heading_paragraph' },
                { model: 'heading2', view: 'h2', title: 'عنوان ۲', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'عنوان ۳', class: 'ck-heading_heading3' }
            ]
        }
    }).then(function(editor) {
        editor.model.document.on('change:data', function() {
            el.value = editor.getData();
        });
    }).catch(function(err) {
        console.error('CKEditor internal letter:', err);
    });
});
</script>
