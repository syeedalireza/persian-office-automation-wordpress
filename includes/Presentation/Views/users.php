<?php
/**
 * Users Management View - Modern Design
 * phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
use OfficeAutomation\Common\UIHelper;

$users = get_users(['number' => 50]);
$total_users = count_users();
?>

<div class="oa-wrap">
    <div class="oa-header">
        <div class="oa-header-content">
            <div>
                <h1 class="oa-title">
                    <span class="oa-title-icon"><?php echo wp_kses_post( UIHelper::getTitleIcon( '👥' ) ); ?></span>
                    مدیریت کاربران
                </h1>
                <p class="oa-subtitle">
                    مجموع <?php echo esc_html( number_format( $total_users['total_users'] ) ); ?> کاربر در سیستم
                </p>
            </div>
            <button class="oa-btn oa-btn-primary" onclick="location.href='<?php echo esc_js( esc_url( admin_url( 'user-new.php' ) ) ); ?>'">
                ➕ کاربر جدید
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="oa-stats-grid">
        <?php foreach ($total_users['avail_roles'] as $role => $count) { 
            $roleNames = [
                'administrator' => ['name' => 'مدیران', 'icon' => '👑', 'gradient' => 'linear-gradient(135deg, #ef4444, #dc2626)'],
                'editor' => ['name' => 'ویرایشگران', 'icon' => '✍️', 'gradient' => 'linear-gradient(135deg, #6366f1, #4f46e5)'],
                'author' => ['name' => 'نویسندگان', 'icon' => '📝', 'gradient' => 'linear-gradient(135deg, #10b981, #059669)'],
                'subscriber' => ['name' => 'کاربران', 'icon' => '👤', 'gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)']
            ];
            $roleConfig = $roleNames[$role] ?? ['name' => $role, 'icon' => '👥', 'gradient' => 'linear-gradient(135deg, #6b7280, #4b5563)'];
        ?>
            <div class="oa-stat-card" style="--stat-gradient: <?php echo esc_attr( $roleConfig['gradient'] ); ?>">
                <div class="oa-stat-icon"><?php echo esc_html( $roleConfig['icon'] ); ?></div>
                <div class="oa-stat-label"><?php echo esc_html( $roleConfig['name'] ); ?></div>
                <div class="oa-stat-value"><?php echo esc_html( number_format( $count ) ); ?></div>
            </div>
        <?php } ?>
    </div>

    <!-- Users Grid -->
    <div style="display: grid; gap: 16px;">
        <?php foreach ($users as $user) { ?>
            <div class="oa-card" style="transition: var(--oa-transition);">
                <div style="padding: 24px; display: flex; align-items: center; gap: 20px;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--oa-primary), var(--oa-primary-dark)); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; box-shadow: var(--oa-shadow-lg);">
                        <?php echo esc_html( mb_substr( $user->display_name, 0, 1 ) ); ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: var(--oa-gray-900);">
                            <?php echo esc_html($user->display_name); ?>
                        </h3>
                        <div style="font-size: 14px; color: var(--oa-gray-600);">
                            📧 <?php echo esc_html($user->user_email); ?> • 
                            👤 <?php echo esc_html($user->user_login); ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <?php
                        $roles = $user->roles;
                        if (!empty($roles)) {
                            $role = $roles[0];
                            $roleLabels = [
                                'administrator' => ['label' => 'مدیر', 'class' => 'danger'],
                                'editor' => ['label' => 'ویرایشگر', 'class' => 'primary'],
                                'author' => ['label' => 'نویسنده', 'class' => 'success'],
                                'subscriber' => ['label' => 'کاربر', 'class' => 'warning']
                            ];
                            $roleConfig = $roleLabels[$role] ?? ['label' => $role, 'class' => 'primary'];
                        ?>
                            <span class="oa-badge oa-badge-<?php echo esc_attr( $roleConfig['class'] ); ?>">
                                <?php echo esc_html( $roleConfig['label'] ); ?>
                            </span>
                        <?php } ?>
                        
                        <a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>" class="oa-btn oa-btn-outline" style="padding: 8px 16px; font-size: 13px;">
                            ✏️ ویرایش
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

