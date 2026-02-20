<?php
/**
 * Cartable - Pending Items View
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
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '⏳' ) ); ?></span>
                    در انتظار پاسخ
                </h1>
                <p class="oa-subtitle">
                    <?php echo esc_html( (string) $totalCount ); ?> نامه در انتظار پاسخ
                </p>
            </div>
        </div>
    </div>

    <div class="oa-card">
        <div style="padding: 0;">
            <?php if (empty($items)): ?>
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
                    <h3 style="margin: 0 0 10px 0; color: var(--oa-gray-700);">همه نامه‌ها پاسخ داده شده</h3>
                    <p style="color: var(--oa-gray-500);">هیچ نامه در انتظاری وجود ندارد</p>
                </div>
            <?php else: ?>
                <div class="oa-table-wrapper">
                    <table class="oa-table">
                        <thead>
                            <tr>
                                <th>اولویت</th>
                                <th>شماره</th>
                                <th>موضوع</th>
                                <th>فرستنده</th>
                                <th>مهلت</th>
                                <th>باقی‌مانده</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $priorityIcons = [
                                    'low' => '🟢',
                                    'medium' => '🟡',
                                    'high' => '🟠',
                                    'urgent' => '🔴'
                                ];
                                
                                $daysRemaining = $item->days_remaining ?? 0;
                                $urgencyClass = $daysRemaining < 0 ? 'danger' : ($daysRemaining <= 2 ? 'warning' : 'success');
                            ?>
                                <tr>
                                    <td style="font-size: 24px; text-align: center;">
                                        <?php echo esc_html( $priorityIcons[ $item->priority ] ?? '⚪' ); ?>
                                    </td>
                                    <td><strong style="color: var(--oa-primary);">#<?php echo esc_html( $item->number ); ?></strong></td>
                                    <td><strong><?php echo esc_html( $item->subject ); ?></strong></td>
                                    <td><?php echo esc_html( $item->sender ); ?></td>
                                    <td><?php echo $item->deadline ? esc_html( JalaliDate::format( $item->deadline, 'date' ) ) : esc_html( '-' ); ?></td>
                                    <td>
                                        <span class="oa-badge oa-badge-<?php echo esc_attr( $urgencyClass ); ?>">
                                            <?php
                                            if ( $daysRemaining < 0 ) {
                                                echo esc_html( abs( $daysRemaining ) . ' روز تاخیر' );
                                            } elseif ( $daysRemaining == 0 ) {
                                                echo esc_html( 'امروز' );
                                            } else {
                                                echo esc_html( $daysRemaining . ' روز' );
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $viewPage = ( ! empty( $item->type ) && $item->type === 'outgoing' ) ? 'persian-oa-outgoing' : ( ( ! empty( $item->type ) && $item->type === 'internal' ) ? 'persian-oa-internal' : 'persian-oa-incoming-letters' );
                                        $viewUrl = admin_url( 'admin.php?page=' . $viewPage . '&action=view&id=' . absint( $item->id ) );
                                        ?>
                                        <a href="<?php echo esc_url( $viewUrl ); ?>" class="oa-btn oa-btn-primary" style="padding: 6px 16px; font-size: 13px;">پاسخ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                if ( ! empty( $totalPages ) && $totalPages > 1 ) :
                    $baseUrl = admin_url( 'admin.php?page=persian-oa-cartable-pending' );
                ?>
                <div class="oa-pagination" style="padding: 16px 20px; border-top: 1px solid var(--oa-gray-200); display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                    <?php if ( $page > 1 ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page - 1, $baseUrl ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 6px 14px;">← قبلی</a>
                    <?php endif; ?>
                    <span style="align-self: center; color: var(--oa-gray-600); font-size: 14px;">
                        صفحه <?php echo esc_html( (string) $page ); ?> از <?php echo esc_html( (string) $totalPages ); ?>
                    </span>
                    <?php if ( $page < $totalPages ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'paged', $page + 1, $baseUrl ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 6px 14px;">بعدی →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

