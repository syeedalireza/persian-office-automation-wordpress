<?php
/**
 * Calendar View
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 * phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only view; GET params sanitized.
 * @package OfficeAutomation
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use OfficeAutomation\Common\JalaliDate;
use OfficeAutomation\Common\UIHelper;

// View Mode
$view_mode_raw = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '';
$viewMode = ( $view_mode_raw === 'agenda' ) ? 'agenda' : 'month';

// Helper for month name
$monthNames = [
    1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
    4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
    7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
    10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
];

?>

<div class="oa-wrap">
    <!-- Header -->
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '📅' ) ); ?></span>
                    تقویم کاری
                </h1>
                <p class="oa-subtitle">
                    مدیریت زمان و برنامه‌ریزی
                </p>
            </div>
            <div class="oa-header-actions">
                <div class="button-group" style="margin-left: 10px;">
                    <a href="<?php echo esc_url( add_query_arg( 'view', 'month' ) ); ?>" class="button <?php echo esc_attr( $viewMode === 'month' ? 'button-primary' : '' ); ?>">ماهانه</a>
                    <a href="<?php echo esc_url( add_query_arg( 'view', 'agenda' ) ); ?>" class="button <?php echo esc_attr( $viewMode === 'agenda' ? 'button-primary' : '' ); ?>">لیست (Agenda)</a>
                </div>
                
                <a href="<?php echo esc_url( $todayLink ); ?>" class="button">امروز</a>
                <div class="button-group">
                    <a href="<?php echo esc_url( $prevLink ); ?>" class="button"><span class="dashicons dashicons-arrow-right-alt2"></span> ماه قبل</a>
                    <span class="button" style="font-weight: bold; min-width: 150px; text-align: center; background: #fff;">
                        <?php echo esc_html( $monthNames[ $month ] . ' ' . JalaliDate::convertNumbers( (string) $year ) ); ?>
                    </span>
                    <a href="<?php echo esc_url( $nextLink ); ?>" class="button">ماه بعد <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($viewMode === 'month'): ?>
        <!-- Month View -->
        <div class="oa-card" style="padding: 0; overflow: hidden;">
            <!-- Days Header -->
            <div class="oa-calendar-grid-header">
                <div>شنبه</div>
                <div>یکشنبه</div>
                <div>دوشنبه</div>
                <div>سه‌شنبه</div>
                <div>چهارشنبه</div>
                <div>پنج‌شنبه</div>
                <div class="friday">جمعه</div>
            </div>
            
            <!-- Calendar Grid -->
            <div class="oa-calendar-grid">
                <?php 
                // Empty cells before start
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="oa-calendar-cell empty"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup, no user input
                }
                
                // Days
                for ($d = 1; $d <= $daysInMonth; $d++): 
                    $dayEvents = isset($events[$d]) ? $events[$d] : [];
                    $isToday = ($year == $currentYear && $month == $currentMonth && $d == $currentDay);
                    $dateString = sprintf('%04d/%02d/%02d', $year, $month, $d);
                    
                    // Determine if Friday (holiday)
                    $isFriday = (($firstDayOfWeek + $d - 1) % 7) == 6;
                ?>
<div class="oa-calendar-cell <?php echo esc_attr( $isToday ? 'today' : '' ); ?> <?php echo esc_attr( $isFriday ? 'friday' : '' ); ?>"
                         onclick="openEventModal('<?php echo esc_attr( $dateString ); ?>')">
                        
                        <div class="oa-cell-header">
                            <span class="oa-day-number"><?php echo esc_html( JalaliDate::convertNumbers( $d ) ); ?></span>
                            <?php if($isToday): ?>
                                <span class="oa-today-badge">امروز</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="oa-cell-events">
                            <?php foreach ( $dayEvents as $event ) : ?>
                                <a href="<?php echo esc_url( $event['url'] ); ?>" class="oa-event-item" 
                                   style="background-color: <?php echo esc_attr( $event['color'] ); ?>20; color: <?php echo esc_attr( $event['color'] ); ?>; border-right: 2px solid <?php echo esc_attr( $event['color'] ); ?>;"
                                   onclick="event.stopPropagation();">
                                    <span class="time"><?php echo esc_html( JalaliDate::convertNumbers( $event['time'] ) ); ?></span>
                                    <span class="title"><?php echo esc_html($event['title']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="oa-cell-add-overlay">
                            <span class="dashicons dashicons-plus"></span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Agenda View -->
        <div class="oa-card">
            <div class="oa-card-header">
                <h3>لیست رویدادهای <?php echo esc_html( $monthNames[ $month ] ); ?> ماه</h3>
            </div>
            <div style="padding: 0;">
                <?php 
                $hasEvents = false;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    if (isset($events[$d])) {
                        $hasEvents = true;
                        foreach ($events[$d] as $event) {
                            ?>
                            <div style="padding: 16px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 16px;">
                                <div style="min-width: 100px; text-align: center; border-left: 1px solid #eee; padding-left: 16px;">
                                    <div style="font-weight: bold; font-size: 18px; color: #334155;">
                                        <?php echo esc_html( JalaliDate::convertNumbers( $d ) ); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #64748b;">
                                        <?php echo esc_html( $monthNames[ $month ] ); ?>
                                    </div>
                                </div>
                                <div style="flex-grow: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                        <span style="font-size: 12px; padding: 2px 6px; border-radius: 4px; background: <?php echo esc_attr( $event['color'] ); ?>20; color: <?php echo esc_attr( $event['color'] ); ?>;">
                                            <?php echo esc_html( ( $event['type'] === 'meeting' ) ? 'جلسه' : ( ( $event['type'] === 'task' ) ? 'وظیفه' : 'نامه' ) ); ?>
                                        </span>
                                        <span style="color: #64748b; font-size: 12px;">
                                            ساعت <?php echo esc_html( JalaliDate::convertNumbers( $event['time'] ) ); ?>
                                        </span>
                                    </div>
                                    <a href="<?php echo esc_url( $event['url'] ); ?>" style="font-weight: bold; color: #1e293b; text-decoration: none; font-size: 15px;">
                                        <?php echo esc_html( $event['title'] ); ?>
                                    </a>
                                </div>
                                <div>
                                    <a href="<?php echo esc_url( $event['url'] ); ?>" class="button">مشاهده</a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                
                if (!$hasEvents): ?>
                    <div style="padding: 40px; text-align: center; color: #94a3b8;">
                        <span class="dashicons dashicons-calendar-alt" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 16px;"></span>
                        <p>هیچ رویدادی در این ماه یافت نشد.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Add Modal -->
<div id="oa-event-modal" class="oa-modal" style="display: none;">
    <div class="oa-modal-content" style="max-width: 400px;">
        <div class="oa-modal-header">
            <h3>افزودن رویداد جدید</h3>
            <span class="oa-modal-close" onclick="closeEventModal()">&times;</span>
        </div>
        <div class="oa-modal-body">
            <p>برای تاریخ: <strong id="modal-date-display">-</strong></p>
            <div style="display: grid; gap: 10px; margin-top: 20px;">
                <a href="javascript:void(0);" id="btn-create-meeting" class="button button-primary button-large" style="text-align: center;">
                    <span class="dashicons dashicons-groups"></span> ثبت جلسه جدید
                </a>
                <a href="javascript:void(0);" id="btn-create-task" class="button button-secondary button-large" style="text-align: center;">
                    <span class="dashicons dashicons-list-view"></span> ثبت وظیفه جدید
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.oa-calendar-grid-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.oa-calendar-grid-header div {
    padding: 12px;
    text-align: center;
    font-weight: bold;
    color: #64748b;
}
.oa-calendar-grid-header .friday {
    color: #ef4444;
    background: #fef2f2;
}

.oa-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #fff;
    min-height: 600px;
}

.oa-calendar-cell {
    border-right: 1px solid #e2e8f0; /* Right instead of left for RTL */
    border-bottom: 1px solid #e2e8f0;
    min-height: 120px;
    padding: 8px;
    position: relative;
    cursor: pointer;
    transition: background 0.2s;
}
.oa-calendar-cell:hover {
    background: #f8fafc;
}
.oa-calendar-cell:hover .oa-cell-add-overlay {
    opacity: 1;
}

.oa-calendar-cell.friday {
    background: #fef2f2;
}
.oa-calendar-cell.today {
    background: #eff6ff;
}

.oa-cell-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.oa-day-number {
    font-weight: bold;
    font-size: 16px;
    color: #334155;
}
.friday .oa-day-number {
    color: #ef4444;
}

.oa-today-badge {
    font-size: 10px;
    background: #3b82f6;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
}

.oa-cell-events {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.oa-event-item {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    border-radius: 3px;
    font-size: 11px;
    text-decoration: none;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    transition: transform 0.1s;
}
.oa-event-item:hover {
    transform: translateX(-2px);
    filter: brightness(0.95);
}
.oa-event-item .time {
    opacity: 0.8;
    font-size: 10px;
}
.oa-event-item .title {
    font-weight: 500;
}

.oa-cell-add-overlay {
    position: absolute;
    bottom: 8px;
    left: 8px;
    width: 24px;
    height: 24px;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.oa-cell-add-overlay .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

/* Modal Styles */
.oa-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.oa-modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    animation: slideDown 0.3s ease;
}
@keyframes slideDown {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.oa-modal-header {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.oa-modal-body {
    padding: 24px;
}
.oa-modal-close {
    cursor: pointer;
    font-size: 24px;
    color: #94a3b8;
}

/* RTL Grid Fixes */
.oa-calendar-grid-header div:last-child {
    border-left: 1px solid #e2e8f0;
}
.oa-calendar-cell:nth-child(7n) {
    border-left: 1px solid #e2e8f0;
}
</style>

<script>
function openEventModal(date) {
    document.getElementById('oa-event-modal').style.display = 'flex';
    document.getElementById('modal-date-display').innerText = date;
    
    // Set links with date param (esc_js + esc_url used for admin_url output).
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output passed through esc_js( esc_url( admin_url() ) ).
    var meetingLink = '<?php echo esc_js( esc_url( admin_url( 'admin.php?page=persian-oa-meetings&action=new' ) ) ); ?>&date=' + date + '&redirect_to=calendar';
    document.getElementById('btn-create-meeting').href = meetingLink;
    
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output passed through esc_js( esc_url( admin_url() ) ).
    var taskLink = '<?php echo esc_js( esc_url( admin_url( 'admin.php?page=persian-oa-tasks&action=new' ) ) ); ?>&deadline=' + date + '&redirect_to=calendar';
    document.getElementById('btn-create-task').href = taskLink;
}

function closeEventModal() {
    document.getElementById('oa-event-modal').style.display = 'none';
}

// Close on outside click
window.onclick = function(event) {
    var modal = document.getElementById('oa-event-modal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
