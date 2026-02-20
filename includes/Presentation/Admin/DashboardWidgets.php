<?php
/**
 * WordPress Admin Dashboard Widgets
 * Registers beautiful widgets for the main WP dashboard (index.php)
 *
 * @package OfficeAutomation\Presentation\Admin
 */

namespace OfficeAutomation\Presentation\Admin;

use OfficeAutomation\Application\Services\CartableService;
use OfficeAutomation\Application\Services\ReportService;
use OfficeAutomation\Application\Services\NotificationService;
use OfficeAutomation\Common\JalaliDate;

if (!defined('ABSPATH')) {
    exit;
}

class DashboardWidgets {

    private ReportService $reportService;

    public function __construct() {
        $this->reportService = new ReportService();
        add_action('wp_dashboard_setup', [$this, 'registerWidgets'], 999);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue styles and scripts on dashboard only
     */
    public function enqueueAssets($hook) {
        if ($hook !== 'index.php') {
            return;
        }
        wp_enqueue_style(
            'vazirmatn',
            PERSIAN_OA_ASSETS_URL . 'fonts/vazirmatn/style.css',
            [],
            '33.003'
        );
        wp_enqueue_style(
            'persian-oa-dashboard-widgets',
            PERSIAN_OA_ASSETS_URL . 'css/dashboard-widgets.css',
            ['vazirmatn'],
            PERSIAN_OA_VERSION
        );
        wp_enqueue_script(
            'chartjs',
            PERSIAN_OA_ASSETS_URL . 'js/vendor/chart.umd.min.js',
            [],
            '4.4.0',
            true
        );
    }

    /**
     * Output chart inline script - must run after Chart.js
     */
    private function getChartInlineScript(array $labels, array $incoming, array $outgoing): string {
        return sprintf(
            "document.addEventListener('DOMContentLoaded',function(){var e=document.getElementById('persian_oa_mini_chart');if(!e||typeof Chart==='undefined')return;new Chart(e,%s);});",
            wp_json_encode([
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'وارده',
                            'data' => $incoming,
                            'borderColor' => '#10b981',
                            'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                            'borderWidth' => 2,
                            'tension' => 0.4,
                            'fill' => true,
                        ],
                        [
                            'label' => 'صادره',
                            'data' => $outgoing,
                            'borderColor' => '#f59e0b',
                            'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                            'borderWidth' => 2,
                            'tension' => 0.4,
                            'fill' => true,
                        ],
                    ],
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'plugins' => ['legend' => ['position' => 'bottom']],
                    'scales' => [
                        'y' => ['beginAtZero' => true],
                        'x' => ['grid' => ['display' => false]],
                    ],
                ],
            ])
        );
    }

    /**
     * Register all dashboard widgets
     */
    public function registerWidgets() {
        wp_add_dashboard_widget(
            'persian_oa_cartable_widget',
            '📥 صندوق ورودی اتوماسیون',
            [$this, 'renderCartableWidget'],
            null,
            null,
            'side',
            'high'
        );

        wp_add_dashboard_widget(
            'persian_oa_meetings_widget',
            '📅 جلسات آینده',
            [$this, 'renderMeetingsWidget'],
            null,
            null,
            'side',
            'core'
        );

        wp_add_dashboard_widget(
            'persian_oa_tasks_widget',
            '☑️ وظایف من',
            [$this, 'renderTasksWidget'],
            null,
            null,
            'side',
            'core'
        );

        wp_add_dashboard_widget(
            'persian_oa_notifications_widget',
            '🔔 اعلان‌ها',
            [$this, 'renderNotificationsWidget'],
            null,
            null,
            'side',
            'default'
        );

        wp_add_dashboard_widget(
            'persian_oa_correspondence_stats_widget',
            '📊 آمار مکاتبات',
            [$this, 'renderCorrespondenceStatsWidget'],
            null,
            null,
            'normal',
            'high'
        );

        wp_add_dashboard_widget(
            'persian_oa_mini_chart_widget',
            '📈 روند ماهانه نامه‌ها',
            [$this, 'renderMiniChartWidget'],
            null,
            null,
            'normal',
            'core'
        );

        wp_add_dashboard_widget(
            'persian_oa_quick_actions_widget',
            '⚡ اقدامات سریع',
            [$this, 'renderQuickActionsWidget'],
            null,
            null,
            'side',
            'low'
        );
    }

    /**
     * Cartable / Inbox widget
     */
    public function renderCartableWidget() {
        $userId = get_current_user_id();
        try {
            $stats = CartableService::getStatistics($userId);
        } catch (\Throwable $e) {
            $stats = ['inbox_unread' => 0, 'pending' => 0, 'starred' => 0, 'overdue' => 0];
        }
        $inboxUrl = admin_url('admin.php?page=persian-oa-cartable-inbox');
        ?>
        <div class="oa-dw oa-dw-cartable">
            <div class="oa-dw-stats-row">
                <a href="<?php echo esc_url($inboxUrl); ?>" class="oa-dw-stat oa-dw-stat-inbox">
                    <span class="oa-dw-stat-value"><?php echo esc_html(number_format($stats['inbox_unread'])); ?></span>
                    <span class="oa-dw-stat-label">خوانده نشده</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-cartable-pending')); ?>" class="oa-dw-stat oa-dw-stat-pending">
                    <span class="oa-dw-stat-value"><?php echo esc_html(number_format($stats['pending'])); ?></span>
                    <span class="oa-dw-stat-label">در انتظار</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-cartable-starred')); ?>" class="oa-dw-stat oa-dw-stat-starred">
                    <span class="oa-dw-stat-value"><?php echo esc_html(number_format($stats['starred'])); ?></span>
                    <span class="oa-dw-stat-label">ستاره‌دار</span>
                </a>
            </div>
            <?php if ($stats['overdue'] > 0): ?>
            <div class="oa-dw-alert oa-dw-alert-danger">
                <span class="dashicons dashicons-warning"></span>
                <?php echo esc_html(number_format($stats['overdue'])); ?> نامه سررسید گذشته
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($inboxUrl); ?>" class="oa-dw-link">مشاهده صندوق ورودی →</a>
        </div>
        <?php
    }

    /**
     * Upcoming meetings widget
     */
    public function renderMeetingsWidget() {
        try {
            $meetings = $this->reportService->getUpcomingMeetings(5);
        } catch (\Throwable $e) {
            $meetings = [];
        }
        $meetingsUrl = admin_url('admin.php?page=persian-oa-meetings');
        ?>
        <div class="oa-dw oa-dw-meetings">
            <?php if (empty($meetings)): ?>
                <div class="oa-dw-empty">
                    <span class="oa-dw-empty-icon">📅</span>
                    <p>جلسه‌ای در پیش‌رو ندارید</p>
                </div>
            <?php else: ?>
                <ul class="oa-dw-list">
                    <?php foreach ($meetings as $m): ?>
                    <li class="oa-dw-list-item">
                        <div class="oa-dw-list-item-main">
                            <strong><?php echo esc_html($m['title'] ?? '-'); ?></strong>
                            <span class="oa-dw-meta"><?php echo esc_html($m['formatted_date'] ?? JalaliDate::toJalaliDateTime($m['meeting_date'] ?? '')); ?></span>
                            <?php if (!empty($m['location'])): ?>
                            <span class="oa-dw-location">📍 <?php echo esc_html($m['location']); ?></span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a href="<?php echo esc_url($meetingsUrl); ?>" class="oa-dw-link">همه جلسات →</a>
        </div>
        <?php
    }

    /**
     * My tasks widget
     */
    public function renderTasksWidget() {
        try {
            $taskStats = $this->reportService->getTaskSummary();
        } catch (\Throwable $e) {
            $taskStats = ['todo' => 0, 'in_progress' => 0, 'done' => 0, 'total' => 0];
        }
        $todo = $taskStats['todo'] ?? 0;
        $inProgress = $taskStats['in_progress'] ?? 0;
        $done = $taskStats['done'] ?? 0;
        $total = $taskStats['total'] ?? 0;
        $tasksUrl = admin_url('admin.php?page=persian-oa-tasks');
        ?>
        <div class="oa-dw oa-dw-tasks">
            <div class="oa-dw-task-bars">
                <div class="oa-dw-task-bar-row">
                    <span class="oa-dw-task-bar-label">در انتظار</span>
                    <div class="oa-dw-task-bar-wrap"><div class="oa-dw-task-bar oa-dw-task-bar-todo" style="width: <?php echo esc_attr( (string) ( $total ? min( 100, ( $todo / $total ) * 100 ) : 0 ) ); ?>%"></div></div>
                    <span class="oa-dw-task-bar-num"><?php echo esc_html($todo); ?></span>
                </div>
                <div class="oa-dw-task-bar-row">
                    <span class="oa-dw-task-bar-label">در حال انجام</span>
                    <div class="oa-dw-task-bar-wrap"><div class="oa-dw-task-bar oa-dw-task-bar-progress" style="width: <?php echo esc_attr( (string) ( $total ? min( 100, ( $inProgress / $total ) * 100 ) : 0 ) ); ?>%"></div></div>
                    <span class="oa-dw-task-bar-num"><?php echo esc_html($inProgress); ?></span>
                </div>
                <div class="oa-dw-task-bar-row">
                    <span class="oa-dw-task-bar-label">انجام شده</span>
                    <div class="oa-dw-task-bar-wrap"><div class="oa-dw-task-bar oa-dw-task-bar-done" style="width: <?php echo esc_attr( (string) ( $total ? min( 100, ( $done / $total ) * 100 ) : 0 ) ); ?>%"></div></div>
                    <span class="oa-dw-task-bar-num"><?php echo esc_html($done); ?></span>
                </div>
            </div>
            <a href="<?php echo esc_url($tasksUrl); ?>" class="oa-dw-link">مدیریت وظایف →</a>
        </div>
        <?php
    }

    /**
     * Notifications widget
     */
    public function renderNotificationsWidget() {
        $userId = get_current_user_id();
        try {
            $notifications = NotificationService::getUnread($userId, 5);
            $unreadCount = NotificationService::getUnreadCount($userId);
        } catch (\Throwable $e) {
            $notifications = [];
            $unreadCount = 0;
        }
        $cartableUrl = admin_url('admin.php?page=persian-oa-cartable-inbox');
        ?>
        <div class="oa-dw oa-dw-notifications">
            <?php if (empty($notifications)): ?>
                <div class="oa-dw-empty">
                    <span class="oa-dw-empty-icon">✅</span>
                    <p>اعلان جدیدی ندارید</p>
                </div>
            <?php else: ?>
                <ul class="oa-dw-notif-list">
                    <?php foreach ($notifications as $n): ?>
                    <li class="oa-dw-notif-item">
                        <?php
                        $notifLink = !empty($n->link) ? (strpos($n->link, 'http') === 0 ? $n->link : admin_url($n->link)) : $cartableUrl;
                        ?>
                        <a href="<?php echo esc_url($notifLink); ?>">
                            <strong><?php echo esc_html($n->title ?? ''); ?></strong>
                            <span class="oa-dw-notif-meta"><?php echo esc_html(JalaliDate::timeAgo($n->created_at ?? '')); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($unreadCount > 0): ?>
            <a href="<?php echo esc_url($cartableUrl); ?>" class="oa-dw-link">مشاهده <?php echo esc_html(number_format($unreadCount)); ?> اعلان →</a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Correspondence stats widget
     */
    public function renderCorrespondenceStatsWidget() {
        try {
            $summary = $this->reportService->getDashboardStats();
        } catch (\Throwable $e) {
            $summary = ['total_incoming' => 0, 'total_outgoing' => 0, 'total_internal' => 0];
        }
        $totalIncoming = $summary['total_incoming'] ?? 0;
        $totalOutgoing = $summary['total_outgoing'] ?? 0;
        $totalInternal = $summary['total_internal'] ?? 0;
        $total = $totalIncoming + $totalOutgoing + $totalInternal;
        ?>
        <div class="oa-dw oa-dw-stats">
            <div class="oa-dw-stats-grid">
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-incoming-letters')); ?>" class="oa-dw-stat-card oa-dw-stat-incoming">
                    <span class="oa-dw-stat-card-icon">📥</span>
                    <span class="oa-dw-stat-card-value"><?php echo esc_html(number_format($totalIncoming)); ?></span>
                    <span class="oa-dw-stat-card-label">وارده</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-outgoing')); ?>" class="oa-dw-stat-card oa-dw-stat-outgoing">
                    <span class="oa-dw-stat-card-icon">📤</span>
                    <span class="oa-dw-stat-card-value"><?php echo esc_html(number_format($totalOutgoing)); ?></span>
                    <span class="oa-dw-stat-card-label">صادره</span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-internal')); ?>" class="oa-dw-stat-card oa-dw-stat-internal">
                    <span class="oa-dw-stat-card-icon">📝</span>
                    <span class="oa-dw-stat-card-value"><?php echo esc_html(number_format($totalInternal)); ?></span>
                    <span class="oa-dw-stat-card-label">داخلی</span>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Mini chart widget
     */
    public function renderMiniChartWidget() {
        try {
            $chartData = $this->reportService->getChartData();
        } catch (\Throwable $e) {
            $chartData = ['trends' => ['labels' => [], 'incoming' => [], 'outgoing' => []]];
        }
        $labels = $chartData['trends']['labels'] ?? [];
        $incoming = $chartData['trends']['incoming'] ?? [];
        $outgoing = $chartData['trends']['outgoing'] ?? [];

        wp_add_inline_script(
            'chartjs',
            $this->getChartInlineScript($labels, $incoming, $outgoing)
        );
        ?>
        <div class="oa-dw oa-dw-chart">
            <div class="oa-dw-chart-canvas-wrap">
                <canvas id="persian_oa_mini_chart" height="200"></canvas>
            </div>
        </div>
        <?php
    }

    /**
     * Quick actions widget
     */
    public function renderQuickActionsWidget() {
        ?>
        <div class="oa-dw oa-dw-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-incoming')); ?>" class="oa-dw-action oa-dw-action-primary">
                <span class="dashicons dashicons-email-alt"></span>
                ثبت نامه وارده
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-outgoing')); ?>" class="oa-dw-action oa-dw-action-success">
                <span class="dashicons dashicons-editor-break"></span>
                ثبت نامه صادره
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-tasks&action=new')); ?>" class="oa-dw-action oa-dw-action-info">
                <span class="dashicons dashicons-yes-alt"></span>
                ایجاد وظیفه
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=persian-oa-meetings&action=new')); ?>" class="oa-dw-action oa-dw-action-warning">
                <span class="dashicons dashicons-calendar-alt"></span>
                ثبت جلسه
            </a>
        </div>
        <?php
    }
}
