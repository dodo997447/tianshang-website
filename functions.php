<?php
/**
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '4.13.1' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );
define( 'ASTRA_THEME_ORG_VERSION', file_exists( ASTRA_THEME_DIR . 'inc/w-org-version.php' ) );

/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '4.12.0' );

/**
 * Load in-house compatibility.
 */
if ( ASTRA_THEME_ORG_VERSION ) {
	require_once ASTRA_THEME_DIR . 'inc/w-org-version.php';
}

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

define( 'ASTRA_WEBSITE_BASE_URL', 'https://wpastra.com' );

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';

/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/lib/docs/class-astra-docs-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-wp-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-command-palette.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/dark-mode.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

// Enable NPS Survey only if the starter templates version is < 4.3.7 or > 4.4.4 to prevent fatal error.
if ( ! defined( 'ASTRA_SITES_VER' ) || version_compare( ASTRA_SITES_VER, '4.3.7', '<' ) || version_compare( ASTRA_SITES_VER, '4.4.4', '>' ) ) {
	// NPS Survey Integration
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-notice.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/class-astra-nps-survey.php';
}

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-memory-limit-notice.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

/* Setup API */
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-learn.php';
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-api-init.php';

if ( is_admin() ) {
	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'admin/class-astra-admin-loader.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-bsf-admin-notices.php';
}

/**
 * BSF Analytics.
 */
require_once ASTRA_THEME_DIR . 'admin/class-astra-bsf-analytics.php';

/**
 * Metabox additions.
 */
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-elementor-editor-settings.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/posts-structures/class-astra-post-structures.php';
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/surecart/class-astra-surecart.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-starter-content.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-buddypress.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/scroll-to-top/class-astra-scroll-to-top.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymous functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Abilities API integration.
 */
require_once ASTRA_THEME_DIR . 'inc/abilities/bootstrap.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';

// ============================================================
// 水色雲間：自動從 ACF 欄位產生商品簡短說明
// ============================================================
add_filter('woocommerce_short_description', 'tianshang_auto_short_desc', 10, 1);
function tianshang_auto_short_desc($content) {
    if (!is_product()) return $content;
    
    $nationality = get_field('nationality');
    $age         = get_field('age');
    $height      = get_field('height');
    $weight      = get_field('weight');
    $cup         = get_field('cup_size');
    $type_label  = get_field('service_type');
    $services    = get_field('services');
    $extra       = get_field('extra_price');
    $discount    = get_field('discount');
 
    $html = '<div class="woo-info-grid">';
    if ($nationality) $html .= '<span class="woo-tag">' . esc_html($nationality) . '</span>';
    if ($age)         $html .= '<span class="woo-tag">' . esc_html($age) . '歲</span>';
    if ($height)      $html .= '<span class="woo-tag">' . esc_html($height) . 'cm</span>';
    if ($weight)      $html .= '<span class="woo-tag">' . esc_html($weight) . 'kg</span>';
    if ($cup)         $html .= '<span class="woo-tag">' . esc_html($cup) . '罩杯</span>';
    if ($type_label)  $html .= '<span class="woo-tag">' . esc_html($type_label) . '</span>';
    $html .= '</div>';
 
    $html .= '<div class="woo-service">';
    if ($services) $html .= '<p>可配合：' . esc_html($services) . '</p>';
    if ($extra)    $html .= '<p>加價購：' . esc_html($extra) . '</p>';
    if ($discount) $html .= '<p>優惠方案：' . esc_html($discount) . '</p>';
    $html .= '</div>';
 
    return $html;
}
 
// 天上人間－手機選單（含選擇地區 18縣市）
add_action('wp_body_open', 'ts_mobile_menu_output');
function ts_mobile_menu_output() { 
    $cities = [
        ['name' => '台北市', 'slug' => 'taipei-city'],
        ['name' => '新北市', 'slug' => 'new-taipei-city'],
        ['name' => '桃園市', 'slug' => 'taoyuan-city'],
        ['name' => '台中市', 'slug' => 'taichung-city'],
        ['name' => '台南市', 'slug' => 'tainan-city'],
        ['name' => '高雄市', 'slug' => 'kaohsiung-city'],
        ['name' => '基隆市', 'slug' => 'keelung-city'],
        ['name' => '新竹市', 'slug' => 'hsinchu-city'],
        ['name' => '苗栗縣', 'slug' => 'miaoli-county'],
        ['name' => '彰化縣', 'slug' => 'changhua-county'],
        ['name' => '南投縣', 'slug' => 'nantou-county'],
        ['name' => '雲林縣', 'slug' => 'yunlin-county'],
        ['name' => '嘉義市', 'slug' => 'chiayi-city'],
        ['name' => '屏東縣', 'slug' => 'pingtung-county'],
        ['name' => '宜蘭縣', 'slug' => 'yilan-county'],
        ['name' => '花蓮縣', 'slug' => 'hualien-county'],
        ['name' => '台東縣', 'slug' => 'taitung-county'],
        ['name' => '澎湖縣', 'slug' => 'penghu-county'],
    ];
?>
<div id="ts-mob-overlay"></div>
<div id="ts-mob-menu">
    <div id="ts-mob-tabs">
        <button class="ts-mob-tab ts-mob-tab-active" data-tab="menu">選單</button>
        <button class="ts-mob-tab" data-tab="region">選擇地區</button>
    </div>
    <div id="ts-mob-tab-menu" class="ts-mob-tab-content ts-mob-tab-content-active">
        <ul>
            <li><a href="/">首頁</a></li>
            <li><a href="/shop/">水色雲間</a></li>
            <li><a href="/waiso/">水色外送</a></li>
            <li><a href="/dingdian/">水色定點</a></li>
            <li><a href="/category/taoyuan/">桃源手記</a></li>
            <li><a href="/category/news/">青羚報音</a></li>
            <li><a href="/about/">紙鳶寄情</a></li>
        </ul>
    </div>
    <div id="ts-mob-tab-region" class="ts-mob-tab-content">
        <ul class="ts-mob-city-list">
            <?php foreach ($cities as $city): ?>
            <li>
                <a href="/product-category/<?php echo esc_attr($city['slug']); ?>/">
                    <span><?php echo esc_html($city['name']); ?></span>
                    <span class="ts-mob-city-arrow">›</span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var menu    = document.getElementById('ts-mob-menu');
    var overlay = document.getElementById('ts-mob-overlay');

    function openMenu(){ overlay.style.display='block'; menu.style.display='block'; }
    function closeMenu(){ overlay.style.display='none'; menu.style.display='none'; }

    var toggleBtn = document.querySelector('button.ast-mobile-menu-trigger-minimal');
    if(toggleBtn){
        toggleBtn.addEventListener('click', function(e){
            e.stopPropagation();
            if(menu.style.display === 'block'){ closeMenu(); } else { openMenu(); }
        });
    }
    overlay.addEventListener('click', closeMenu);

    // Tab 切換
    document.querySelectorAll('.ts-mob-tab').forEach(function(tab){
        tab.addEventListener('click', function(){
            document.querySelectorAll('.ts-mob-tab').forEach(function(t){ t.classList.remove('ts-mob-tab-active'); });
            document.querySelectorAll('.ts-mob-tab-content').forEach(function(c){ c.classList.remove('ts-mob-tab-content-active'); });
            this.classList.add('ts-mob-tab-active');
            document.getElementById('ts-mob-tab-' + this.dataset.tab).classList.add('ts-mob-tab-content-active');
        });
    });
});
</script>
<style>
#ts-mob-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 99998;
}
#ts-mob-menu {
    display: none;
    position: fixed;
    top: 60px; left: 0; right: 0;
    background: #0A0118;
    z-index: 99999;
    border-bottom: 1px solid rgba(255,138,178,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    max-height: 60vh;    /* 加這行，只佔60%視窗高度 */
    overflow-y: auto;    /* 確保這行存在 */
}
#ts-mob-tabs {
    display: flex;
    border-bottom: 0.5px solid rgba(255,138,178,0.2);
    position: sticky;
    top: 0;
    background: #0A0118;
    z-index: 2;
}
.ts-mob-tab {
    flex: 1;
    padding: 14px 0;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: rgba(224,207,255,0.5);
    font-size: 14px;
    letter-spacing: 3px;
    cursor: pointer;
    font-family: 'Noto Serif TC', serif;
}
.ts-mob-tab-active {
    color: #FF8AB2 !important;
    border-bottom-color: #FF8AB2 !important;
}
.ts-mob-tab-content { display: none; }
.ts-mob-tab-content-active { display: block; }

/* 選單列表 */
#ts-mob-tab-menu ul { list-style: none; padding: 0; margin: 0; }
#ts-mob-tab-menu ul li { border-bottom: 0.5px solid rgba(200,162,255,0.1); }
#ts-mob-tab-menu ul li a {
    display: block;
    padding: 16px 24px;
    color: #E0CFFF !important;
    font-size: 16px;
    letter-spacing: 3px;
    text-decoration: none;
    font-family: 'Noto Serif TC', serif;
}
#ts-mob-tab-menu ul li a:hover { color: #FF8AB2 !important; }

/* 地區列表 */
.ts-mob-city-list { list-style: none; padding: 0; margin: 0; }
.ts-mob-city-list li { border-bottom: 0.5px solid rgba(200,162,255,0.1); }
.ts-mob-city-list li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 24px;  /* 從 16px 改成 10px */
    color: #E0CFFF !important;
    font-size: 14px;     /* 從 15px 改成 14px */
    letter-spacing: 2px;
    text-decoration: none;
    font-family: 'Noto Sans TC', sans-serif;
    transition: all 0.2s;
}
.ts-mob-city-list li a:hover {
    color: #FF8AB2 !important;
    background: rgba(255,138,178,0.05);
}
.ts-mob-city-arrow {
    color: rgba(200,162,255,0.4);
    font-size: 20px;
}
</style>
<?php }
// ============================================================
// 水色雲間－手機篩選按鈕
// ============================================================
add_action('wp_footer', 'ts_mobile_filter_btn');
function ts_mobile_filter_btn() {
    if (!is_shop()) return; ?>
<button id="ts-filter-btn">⚙ 篩選</button>
<button id="ts-filter-close-btn">✕ 關閉篩選</button>
<div id="ts-filter-overlay"></div>
<script>
(function(){
    var btn = document.getElementById('ts-filter-btn');
    var closeBtn = document.getElementById('ts-filter-close-btn');
    var overlay = document.getElementById('ts-filter-overlay');
    var sidebar = document.getElementById('secondary');
    if(!btn || !sidebar) return;
 
    // 把篩選按鈕移到商品區上方
    var shopLoop = document.querySelector('.woocommerce-ordering, .woocommerce-result-count');
    if(shopLoop && shopLoop.parentNode){
        shopLoop.parentNode.insertBefore(btn, shopLoop);
    }
 
    function openFilter(){
        var container = sidebar.closest('.ast-container') || document.querySelector('.ast-container');
        sidebar.classList.add('ts-open');
        overlay.style.display = 'block';
        closeBtn.style.display = 'block';
        btn.style.display = 'none';
        if(container){
            container.style.removeProperty('flex-direction');
            container.style.removeProperty('display');
        }
    }
 
    function closeFilter(){
        sidebar.classList.remove('ts-open');
        overlay.style.display = 'none';
        closeBtn.style.display = 'none';
        btn.style.display = 'block';
    }
 
    btn.addEventListener('click', openFilter);
    closeBtn.addEventListener('click', closeFilter);
    overlay.addEventListener('click', closeFilter);
})();
</script>
<style>
@media (max-width: 921px) {
    #ts-filter-btn {
        display: block;
        width: 100%;
        background: #2D1B4E;
        color: #FF8AB2;
        border: 1px solid #FF8AB2;
        border-radius: 8px;
        padding: 12px 20px;
        font-size: 15px;
        letter-spacing: 4px;
        cursor: pointer;
        font-family: 'Noto Serif TC', serif;
        margin-bottom: 16px;
        text-align: center;
        box-sizing: border-box;
        position: relative;
        z-index: 9996;
    }
    #ts-filter-close-btn {
        display: none;
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #2D1B4E;
        color: #FF8AB2;
        border: 1px solid #FF8AB2;
        border-radius: 24px;
        padding: 12px 32px;
        font-size: 15px;
        letter-spacing: 4px;
        cursor: pointer;
        font-family: 'Noto Serif TC', serif;
        z-index: 99999;
        white-space: nowrap;
    }
    #ts-filter-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.3);
        z-index: 9990;
    }
}
@media (min-width: 922px) {
    #ts-filter-btn,
    #ts-filter-close-btn,
    #ts-filter-overlay { display: none !important; }
}
</style>
<?php }