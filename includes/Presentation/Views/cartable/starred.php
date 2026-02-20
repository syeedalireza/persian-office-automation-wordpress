<?php
/**
 * Cartable - Starred Items View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;
?>

<div class="oa-wrap">
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '⭐' ) ); ?></span>
                    ستاره‌دار
                </h1>
                <p class="oa-subtitle">
                    نامه‌های مهم و ستاره‌دار شده
                </p>
            </div>
        </div>
    </div>

    <div class="oa-card">
        <div style="padding: 0;">
            <?php if (empty($items)): ?>
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">⭐</div>
                    <h3 style="margin: 0 0 10px 0; color: var(--oa-gray-700);">هیچ نامه ستاره‌داری ندارید</h3>
                    <p style="color: var(--oa-gray-500);">می‌توانید نامه‌های مهم را با کلیک روی ستاره علامت‌گذاری کنید</p>
                </div>
            <?php else: ?>
                <div class="oa-table-wrapper">
                    <table class="oa-table">
                        <thead>
                            <tr>
                                <th>شماره</th>
                                <th>موضوع</th>
                                <th>فرستنده</th>
                                <th>تاریخ ستاره‌دار</th>
                                <th>یادداشت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><strong style="color: var(--oa-primary);">#<?php echo esc_html( $item->number ); ?></strong></td>
                                    <td><strong>⭐ <?php echo esc_html( $item->subject ); ?></strong></td>
                                    <td><?php echo esc_html( $item->sender ); ?></td>
                                    <td><?php echo esc_html( JalaliDate::format( $item->starred_at, 'datetime' ) ); ?></td>
                                    <td><?php echo $item->note ? esc_html( $item->note ) : esc_html( '-' ); ?></td>
                                    <td>
                                        <?php
                                        $viewPage = ( ! empty( $item->type ) && $item->type === 'outgoing' ) ? 'persian-oa-outgoing' : ( ( ! empty( $item->type ) && $item->type === 'internal' ) ? 'persian-oa-internal' : 'persian-oa-incoming-letters' );
                                        $viewUrl = admin_url( 'admin.php?page=' . $viewPage . '&action=view&id=' . absint( $item->id ) );
                                        ?>
                                        <a href="<?php echo esc_url( $viewUrl ); ?>" class="oa-btn oa-btn-outline" style="padding: 6px 12px; font-size: 13px;">👁️ مشاهده</a>
                                        <button class="oa-btn oa-btn-danger" style="padding: 6px 12px; font-size: 13px;"
                                                onclick="removeStar(<?php echo esc_attr( (string) absint( $item->id ) ); ?>)">
                                            ❌ حذف ستاره
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function removeStar(id) {
    if (!confirm('آیا مطمئن هستید؟')) return;
    
    jQuery.post(ajaxurl, {
        action: 'oa_toggle_star',
        nonce: '<?php echo esc_js( wp_create_nonce( 'oa_cartable_nonce' ) ); ?>',
        correspondence_id: id
    }, function(response) {
        if (response.success) {
            location.reload();
        }
    });
}
</script>

