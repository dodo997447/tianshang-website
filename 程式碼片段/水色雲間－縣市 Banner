add_action('woocommerce_before_main_content', function() {
    if (!is_product_category()) return;
    
    $term = get_queried_object();
    if (!$term) return;
    
    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'full') : '';
    $description = $term->description;
    
    echo '<div class="ts-city-banner"';
    if ($thumb_url) {
        echo ' style="background-image: url(' . esc_url($thumb_url) . ');"';
    }
    echo '>';
    echo '<div class="ts-city-banner-overlay"></div>';
    echo '<div class="ts-city-banner-content">';
    echo '<h1 class="ts-city-banner-title">' . esc_html($term->name) . '</h1>';
    if ($description) {
        echo '<p class="ts-city-banner-desc">' . esc_html($description) . '</p>';
    }
    echo '</div>';
    echo '</div>';
}, 5);

add_action('wp_head', function() {
    if (!is_product_category()) return;
    echo '<style>
    .ts-city-banner {
        width: 100%;
        height: 300px;
        background-size: cover;
        background-position: center;
        background-color: #1a0835;
        position: relative;
        margin-bottom: 30px;
    }
    .ts-city-banner-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(10,1,24,0.3), rgba(10,1,24,0.8));
    }
    .ts-city-banner-content {
        position: absolute;
        bottom: 40px;
        left: 40px;
        z-index: 1;
    }
    .ts-city-banner-title {
        color: #fff !important;
        font-size: 48px !important;
        letter-spacing: 12px !important;
        margin: 0 0 8px !important;
        text-shadow: 0 0 20px rgba(255,138,178,0.6) !important;
        font-family: "Noto Serif TC", serif !important;
    }
    .ts-city-banner-desc {
        color: rgba(255,255,255,0.7) !important;
        font-size: 14px !important;
        letter-spacing: 4px !important;
        margin: 0 !important;
    }
    </style>';
});