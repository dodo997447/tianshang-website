add_action('woocommerce_before_shop_loop', function() {
    if (!is_shop()) return;
    echo '<div class="ts-shop-hero">';
    echo do_shortcode('[ts_city_count]');
    echo '</div>';
}, 5);

add_action('woocommerce_before_main_content', function() {
    if (!is_shop()) return;
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    add_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
}, 1);

add_filter('body_class', function($classes) {
    if (is_shop()) {
        $classes = array_diff($classes, ['ast-no-sidebar', 'ast-full-width']);
        $classes[] = 'ast-left-sidebar';
    }
    return $classes;
});

add_action('wp_head', function() {
    if (!is_shop()) return;
    echo '<style>
    .ts-shop-hero {
        width: 100%;
        background: linear-gradient(180deg, #0d0520, #1a0835);
        padding: 12px 16px;
        box-sizing: border-box;
        margin-bottom: 0;
    }
    .ts-shop-hero .ts-city-grid {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
        grid-template-columns: unset !important;
    }
    .ts-shop-hero .ts-city-item {
        flex: 0 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        background: rgba(255,255,255,0.05) !important;
        border: 1px solid rgba(255,100,150,0.2) !important;
        border-radius: 6px !important;
        padding: 4px 10px !important;
        text-decoration: none !important;
        cursor: pointer !important;
    }
    .ts-shop-hero .ts-city-item:hover,
    .ts-shop-hero .ts-city-item.active {
        background: rgba(255,100,150,0.2) !important;
        border-color: #ff6496 !important;
    }
    .ts-shop-hero .ts-city-name {
        color: #fff !important;
        font-size: .8rem !important;
        white-space: nowrap !important;
    }
    .ts-shop-hero .ts-city-count {
        color: #ff6496 !important;
        font-size: .7rem !important;
    }
    .ts-city-banner-dynamic {
        width: 100%;
        height: 260px;
        background-size: cover;
        background-position: center;
        position: relative;
        margin-bottom: 16px;
    }
    .ts-city-banner-dynamic .ts-city-banner-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(10,1,24,0.3), rgba(10,1,24,0.85));
    }
    .ts-city-banner-dynamic .ts-city-banner-content {
        position: absolute;
        bottom: 30px;
        left: 40px;
        z-index: 1;
    }
    .ts-city-banner-dynamic .ts-city-banner-title {
        color: #fff !important;
        font-size: 42px !important;
        letter-spacing: 10px !important;
        margin: 0 !important;
        text-shadow: 0 0 20px rgba(255,138,178,0.6) !important;
        font-family: "Noto Serif TC", serif !important;
    }
    .ts-city-banner-dynamic .ts-city-banner-desc {
        color: rgba(255,255,255,0.7) !important;
        font-size: 13px !important;
        letter-spacing: 3px !important;
        margin: 6px 0 0 !important;
    }
    .woocommerce ul.products li.product {
        border-radius: 10px !important;
        overflow: hidden !important;
        transition: transform .2s !important;
        padding: 0 0 12px 0 !important;
    }
    .woocommerce ul.products li.product:hover {
        transform: translateY(-3px) !important;
    }
    .woocommerce ul.products li.product a img {
        width: 100% !important;
        height: 340px !important;
        object-fit: cover !important;
        object-position: center 20% !important;
        display: block !important;
        margin: 0 !important;
    }
    .woocommerce ul.products li.product .woocommerce-loop-product__title {
        font-size: 15px !important;
        padding: 8px 12px 4px !important;
        color: #fff !important;
    }
    .woocommerce ul.products li.product .button { display: none !important; }
    .woocommerce ul.products li.product .price { display: none !important; }
    .ts-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 0 12px 8px;
    }
    .ts-card-tag {
        background: rgba(255,255,255,0.08);
        color: rgba(255,255,255,0.65);
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 10px;
    }
    body.ast-left-sidebar .ast-container {
        display: flex !important;
        flex-direction: row !important;
        align-items: flex-start !important;
        max-width: 1200px !important;
        margin: 0 auto !important;
    }
    body.ast-left-sidebar #secondary {
        order: 1 !important;
        width: 200px !important;
        min-width: 200px !important;
        flex-shrink: 0 !important;
    }
    body.ast-left-sidebar #primary {
        order: 2 !important;
        flex: 1 !important;
        min-width: 0 !important;
    }
    </style>';
});

add_action('woocommerce_after_shop_loop_item_title', function() {
    global $product;
    $id = $product->get_id();
    $age = get_field('age', $id);
    $nat = get_field('nationality', $id);
    $cup = get_field('cup_size', $id);
    echo '<div class="ts-card-meta">';
    if ($nat)  echo '<span class="ts-card-tag">' . esc_html($nat) . '</span>';
    if ($age)  echo '<span class="ts-card-tag">' . esc_html($age) . '歲</span>';
    if ($cup)  echo '<span class="ts-card-tag">' . esc_html($cup) . '</span>';
    echo '</div>';
}, 5);

add_action('wp_footer', function() {
    if (!is_shop() && strpos($_SERVER['REQUEST_URI'], 'swoof') === false) return;
    $city_images = [
        'taipei-city'     => wp_get_attachment_image_url(292, 'full'),
        'new-taipei-city' => wp_get_attachment_image_url(293, 'full'),
        'taoyuan-city'    => wp_get_attachment_image_url(294, 'full'),
        'taichung-city'   => wp_get_attachment_image_url(291, 'full'),
        'tainan-city'     => wp_get_attachment_image_url(296, 'full'),
        'kaohsiung-city'  => wp_get_attachment_image_url(298, 'full'),
        'hsinchu-city'    => wp_get_attachment_image_url(299, 'full'),
        'keelung-city'    => wp_get_attachment_image_url(300, 'full'),
        'miaoli-county'   => wp_get_attachment_image_url(301, 'full'),
        'changhua-county' => wp_get_attachment_image_url(302, 'full'),
        'yunlin-county'   => wp_get_attachment_image_url(303, 'full'),
        'chiayi-city'     => wp_get_attachment_image_url(304, 'full'),
        'pingtung-county' => wp_get_attachment_image_url(305, 'full'),
        'yilan-county'    => wp_get_attachment_image_url(306, 'full'),
        'hualien-county'  => wp_get_attachment_image_url(307, 'full'),
        'taitung-county'  => wp_get_attachment_image_url(308, 'full'),
        'penghu-county'   => wp_get_attachment_image_url(309, 'full'),
        'nantou-county'   => wp_get_attachment_image_url(295, 'full'),
    ];
    $city_names = [
        'taipei-city' => '台北市', 'new-taipei-city' => '新北市',
        'taoyuan-city' => '桃園市', 'taichung-city' => '台中市',
        'tainan-city' => '台南市', 'kaohsiung-city' => '高雄市',
        'hsinchu-city' => '新竹市', 'keelung-city' => '基隆市',
        'miaoli-county' => '苗栗縣', 'changhua-county' => '彰化縣',
        'yunlin-county' => '雲林縣', 'chiayi-city' => '嘉義市',
        'pingtung-county' => '屏東縣', 'yilan-county' => '宜蘭縣',
        'hualien-county' => '花蓮縣', 'taitung-county' => '台東縣',
        'penghu-county' => '澎湖縣', 'nantou-county' => '南投縣',
    ];
    ?>
    <script>
    jQuery(document).ready(function($) {
        var cityImages = <?php echo json_encode($city_images); ?>;
        var cityNames = <?php echo json_encode($city_names); ?>;

        // 插入 banner 區塊
        var $banner = $('<div class="ts-city-banner-dynamic" style="display:none;"><div class="ts-city-banner-overlay"></div><div class="ts-city-banner-content"><h1 class="ts-city-banner-title"></h1><p class="ts-city-banner-desc"></p></div></div>');
        $('.ts-shop-hero').before($banner);

        // 頁面載入時偵測 swoof URL 自動顯示 banner
        var swoofMatch = window.location.href.match(/product_cat-([a-z-]+)/);
        if (swoofMatch) {
            var currentSlug = swoofMatch[1];
            if (cityImages[currentSlug]) {
                $banner.css('background-image', 'url(' + cityImages[currentSlug] + ')').show();
                $banner.find('.ts-city-banner-title').text(cityNames[currentSlug] || '');
                $('.ts-city-item[href="/shop/?product_cat=' + currentSlug + '"]').addClass('active');
            }
        }

        // 城市格子點擊
        $(document).on('click', '.ts-city-item', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var slug = href.replace('/shop/?product_cat=', '');

            if (cityImages[slug]) {
                $banner.css('background-image', 'url(' + cityImages[slug] + ')').show();
                $banner.find('.ts-city-banner-title').text(cityNames[slug] || '');
            }

            $('.ts-city-item').removeClass('active');
            $(this).addClass('active');

            window.location.href = '/shop/swoof/product_cat-' + slug + '/';
        });
    });
    </script>
    <?php
});