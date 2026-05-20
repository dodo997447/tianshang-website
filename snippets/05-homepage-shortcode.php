/**
 * 天上人間 · 首頁動態 Shortcode
 */

add_shortcode('ts_products_by_service', function($atts) {
    $atts = shortcode_atts(['type' => '外送茶', 'limit' => 4], $atts);

    $meta_query = [
        'relation' => 'OR',
        ['key' => 'service_type', 'value' => $atts['type'], 'compare' => '='],
        ['key' => 'service_type', 'value' => '外送+定點', 'compare' => '='],
    ];

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => $meta_query,
    ];

    $query = new WP_Query($args);
    if (!$query->have_posts()) return '<p style="color:rgba(255,255,255,0.4);text-align:center;">暫無商品</p>';
    $html = '<div class="ts-product-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $id     = get_the_ID();
        $title  = get_the_title();
        $link   = get_permalink();
        $img    = get_the_post_thumbnail_url($id, 'medium');
        $age    = get_field('age', $id);
        $height = get_field('height', $id);
        $cup    = get_field('cup_size', $id);
        $nat    = get_field('nationality', $id);
        $terms  = get_the_terms($id, 'product_cat');
        $city   = '';
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $t) {
                if ($t->parent == 0 && $t->name !== '未分類') { $city = $t->name; break; }
            }
        }
        $html .= '<a href="' . esc_url($link) . '" class="ts-product-card">';
        if ($img) $html .= '<div class="ts-product-img" style="background-image:url(' . esc_url($img) . ')"></div>';
        $html .= '<div class="ts-product-info">';
        if ($city)   $html .= '<span class="ts-product-city">' . esc_html($city) . '</span>';
        $html .= '<div class="ts-product-name">' . esc_html($title) . '</div>';
        $html .= '<div class="ts-product-tags">';
        if ($nat)    $html .= '<span class="ts-ptag">' . esc_html($nat) . '</span>';
        if ($age)    $html .= '<span class="ts-ptag">' . esc_html($age) . '歲</span>';
        if ($height) $html .= '<span class="ts-ptag">' . esc_html($height) . 'cm</span>';
        if ($cup)    $html .= '<span class="ts-ptag">' . esc_html($cup) . '</span>';
        $html .= '</div></div></a>';
    }
    $html .= '</div>';
    wp_reset_postdata();
    static $css_done = false;
    if (!$css_done) {
        $css_done = true;
        $html .= '<style>
.ts-product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:8px;}
.ts-product-card{display:block;text-decoration:none;background:rgba(255,255,255,0.04);border:1px solid rgba(255,100,150,0.15);border-radius:12px;overflow:hidden;transition:all .2s;}
.ts-product-card:hover{border-color:#ff6496;transform:translateY(-3px);}
.ts-product-img{width:100%;padding-top:130%;background-size:cover;background-position:top center;}
.ts-product-info{padding:12px;}
.ts-product-city{font-size:.75rem;color:#ff6496;letter-spacing:.1em;}
.ts-product-name{font-size:1.05rem;font-weight:600;color:#fff;margin:4px 0 8px;}
.ts-product-tags{display:flex;flex-wrap:wrap;gap:4px;}
.ts-ptag{font-size:.72rem;background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.6);padding:2px 8px;border-radius:10px;}
</style>';
    }
    return $html;
});

add_shortcode('ts_city_count', function($atts) {
    global $wpdb;
    $cities = [
        '台北市' => 'taipei-city',
        '新北市' => 'new-taipei-city',
        '桃園市' => 'taoyuan-city',
        '台中市' => 'taichung-city',
        '台南市' => 'tainan-city',
        '高雄市' => 'kaohsiung-city',
        '新竹市' => 'hsinchu-city',
        '基隆市' => 'keelung-city',
        '苗栗縣' => 'miaoli-county',
        '彰化縣' => 'changhua-county',
        '南投縣' => 'nantou-county',
        '雲林縣' => 'yunlin-county',
        '嘉義市' => 'chiayi-city',
        '屏東縣' => 'pingtung-county',
        '宜蘭縣' => 'yilan-county',
        '花蓮縣' => 'hualien-county',
        '台東縣' => 'taitung-county',
        '澎湖縣' => 'penghu-county',
    ];
    $html = '<div class="ts-city-grid">';
    foreach ($cities as $name => $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        $count = $term ? $term->count : -99;
        $html .= '<a href="/shop/swoof/product_cat-' . esc_attr($slug) . '/" class="ts-city-item">';
        $html .= '<span class="ts-city-name">' . esc_html($name) . '</span>';
        $html .= '<span class="ts-city-count">' . $count . ' 位</span>';
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
});

add_shortcode('ts_latest_news', function($atts) {
    $atts = shortcode_atts(['limit' => 4], $atts);
    $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'category_name'  => 'news',
    ];
    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '<div class="ts-news-item"><div class="ts-news-meta"><span class="ts-tag ts-tag-pink">公告</span><span class="ts-news-date">2026.04.22</span></div><div class="ts-news-title">五月新會友見面茶會報名開放</div></div>';
    }
    $html = '';
    while ($query->have_posts()) {
        $query->the_post();
        $html .= '<div class="ts-news-item">';
        $html .= '<div class="ts-news-meta"><span class="ts-tag ts-tag-pink">最新</span><span class="ts-news-date">' . get_the_date('Y.m.d') . '</span></div>';
        $html .= '<div class="ts-news-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
        $html .= '</div>';
    }
    wp_reset_postdata();
    return $html;
});