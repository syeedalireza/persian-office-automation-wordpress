<?php
/**
 * Meeting List View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list; GET params sanitized.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

$listMessage = isset($_GET['message']) ? sanitize_text_field(wp_unslash($_GET['message'])) : '';
$pagination = $pagination ?? ['total' => 0, 'total_pages' => 1, 'current_page' => 1, 'per_page' => 15];
$total = (int) $pagination['total'];
$totalPages = (int) $pagination['total_pages'];
$currentPage = (int) $pagination['current_page'];
$perPage = (int) $pagination['per_page'];
$from = $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
$to = min($currentPage * $perPage, $total);

$listMessages = [
    'created' => ['type' => 'success', 'text' => 'جلسه با موفقیت ثبت شد.'],
    'updated' => ['type' => 'success', 'text' => 'جلسه با موفقیت به‌روزرسانی شد.'],
    'deleted' => ['type' => 'success', 'text' => 'جلسه با موفقیت حذف شد.'],
];
?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '📅' ) ); ?></span>
                    مدیریت جلسات
                </h1>
                <p class="oa-subtitle">
                    <?php
                    if ( $total > 0 ) {
                        printf(
                            /* translators: 1: from, 2: to, 3: total */
                            esc_html__( 'نمایش %1$d تا %2$d از %3$d جلسه', 'persian-office-automation' ),
                            $from,
                            $to,
                            $total
                        );
                    } else {
                        esc_html_e( 'هیچ جلسه‌ای ثبت نشده', 'persian-office-automation' );
                    }
                    ?>
                    •
                    <?php echo esc_html( JalaliDate::now( 'l، j F Y' ) ); ?>
                </p>
            </div>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-meetings&action=new' ) ); ?>" class="oa-btn oa-btn-primary">
                ➕ ثبت جلسه جدید
            </a>
        </div>
    </div>

    <?php if ( $listMessage && isset( $listMessages[ $listMessage ] ) ) : ?>
        <div class="oa-notice oa-notice-<?php echo esc_attr( $listMessages[ $listMessage ]['type'] ); ?>" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;">
            <?php echo esc_html( $listMessages[ $listMessage ]['text'] ); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($meetings)) { ?>
        <div style="display: grid; gap: 20px;">
            <?php
                $statusColors = [
                    'scheduled' => 'primary',
                    'held' => 'success',
                    'cancelled' => 'danger'
                ];
                $statusLabels = [
                    'scheduled' => 'برنامه‌ریزی شده',
                    'held' => 'برگزار شده',
                    'cancelled' => 'لغو شده'
                ];
                $recurrenceLabels = [
                    'none' => 'بدون تکرار',
                    'daily' => 'روزانه',
                    'weekly' => 'هفتگی',
                    'monthly' => 'ماهانه'
                ];
                foreach ($meetings as $meeting) {
                    $meetingParticipants = $participantsByMeeting[$meeting->getId()] ?? [];
                    $cardColor = $meeting->getColor() ?: '#3b82f6';
            ?>
                <div class="oa-card" style="border-right: 4px solid <?php echo esc_attr( $cardColor ); ?>;">
                    <div style="padding: 24px; display: flex; align-items: flex-start; gap: 24px;">
                        <!-- Date Badge -->
                        <div style="background: #eff6ff; border-radius: 12px; padding: 12px; text-align: center; min-width: 100px;">
                            <div style="font-size: 24px; font-weight: 800; color: #3b82f6;">
                                <?php echo esc_html( JalaliDate::format( $meeting->getMeetingDate(), 'd' ) ); ?>
                            </div>
                            <div style="font-size: 13px; color: #1e40af; font-weight: 600;">
                                <?php echo esc_html( JalaliDate::format( $meeting->getMeetingDate(), 'F' ) ); ?>
                            </div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 6px;">
                                <?php
                                $startTime = JalaliDate::format( $meeting->getMeetingDate(), 'time' );
                                $endDate = $meeting->getEndDate();
                                $endTime = $endDate ? JalaliDate::format( $endDate, 'time' ) : '';
                                if ( $endTime ) {
                                    echo esc_html( 'شروع: ' . $startTime . ' • پایان: ' . $endTime );
                                } else {
                                    echo esc_html( 'ساعت شروع: ' . $startTime );
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <h3 style="font-size: 20px; font-weight: 700; color: var(--oa-gray-900); margin: 0;">
                                    <?php echo esc_html( $meeting->getTitle() ); ?>
                                </h3>
                                <span class="oa-badge oa-badge-<?php echo esc_attr( $statusColors[ $meeting->getStatus() ] ?? 'secondary' ); ?>">
                                    <?php echo esc_html( $statusLabels[ $meeting->getStatus() ] ?? $meeting->getStatus() ); ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 16px 24px; color: var(--oa-gray-600); font-size: 14px; margin-bottom: 8px;">
                                <span>📍 مکان: <strong><?php echo esc_html($meeting->getLocation()); ?></strong></span>
                                <span>👤 برگزارکننده: <strong>شما</strong></span>
                                <span title="وضعیت تکرار جلسه">🔄 تکرار: <strong><?php echo esc_html( $recurrenceLabels[ $meeting->getRecurrence() ] ?? $meeting->getRecurrence() ); ?></strong></span>
                            </div>
                            <?php if ( ! empty( $meetingParticipants ) ) : ?>
                                <div style="margin-bottom: 12px;">
                                    <span style="font-size: 13px; color: var(--oa-gray-600);">👥 شرکت‌کنندگان:</span>
                                    <span style="font-size: 14px;">
                                        <?php
                                        $names = array_map( function ( $p ) {
                                            return is_object( $p ) ? ( $p->display_name ?? '' ) : ( $p['display_name'] ?? '' );
                                        }, $meetingParticipants );
                                        $names = array_filter( $names );
                                        echo esc_html( implode( '، ', $names ) );
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <p style="color: var(--oa-gray-600); margin: 0; line-height: 1.6;">
                                <?php echo esc_html( wp_trim_words( $meeting->getDescription(), 30, '...' ) ); ?>
                            </p>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px; min-width: 120px;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=persian-oa-meetings&action=edit&id=' . $meeting->getId() ) ); ?>" class="oa-btn oa-btn-outline" style="font-size: 13px; text-align: center;">
                                ✏️ ویرایش
                            </a>
                            <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=persian_oa_delete_meeting&id=' . $meeting->getId() . '&_wpnonce=' . wp_create_nonce( 'persian_oa_delete_meeting_' . $meeting->getId() ) ) ); ?>" class="oa-btn oa-btn-outline" style="font-size: 13px; text-align: center; color: #b91c1c; border-color: #fecaca;" onclick="return confirm('آیا از حذف این جلسه اطمینان دارید؟');">
                                🗑️ حذف
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if ( $totalPages > 1 ) : ?>
            <div class="oa-pagination" style="margin-top: 24px; display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap;">
                <?php
                $baseUrl = admin_url( 'admin.php' );
                $queryArgs = [ 'page' => 'persian-oa-meetings' ];
                if ( $listMessage ) {
                    $queryArgs['message'] = $listMessage;
                }
                ?>
                <?php if ( $currentPage > 1 ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( array_merge( $queryArgs, [ 'paged' => $currentPage - 1 ] ), $baseUrl ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 8px 16px;">
                        قبلی
                    </a>
                <?php endif; ?>

                <span class="oa-pagination-info" style="font-size: 14px; color: var(--oa-gray-600); padding: 0 12px;">
                    صفحه <?php echo (int) $currentPage; ?> از <?php echo (int) $totalPages; ?>
                </span>

                <?php if ( $currentPage < $totalPages ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( array_merge( $queryArgs, [ 'paged' => $currentPage + 1 ] ), $baseUrl ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 8px 16px;">
                        بعدی
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php } else { ?>
        <div class="oa-card">
            <div style="padding: 80px; text-align: center;">
                <div style="font-size: 72px; margin-bottom: 24px; animation: float 3s ease-in-out infinite;">📅</div>
                <h3 style="font-size: 24px; font-weight: 700; color: var(--oa-gray-900); margin-bottom: 12px;">
                    هیچ جلسه‌ای یافت نشد
                </h3>
                <p style="font-size: 16px; color: var(--oa-gray-600); margin-bottom: 32px;">
                    لیست جلسات شما خالی است.
                </p>
                <a href="?page=persian-oa-meetings&action=new" class="oa-btn oa-btn-primary oa-btn-lg">
                    ➕ ثبت اولین جلسه
                </a>
            </div>
        </div>
    <?php } ?>
</div>


