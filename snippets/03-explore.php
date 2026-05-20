
/**
 * 天上人間 · 踏雲尋境 · 地區索引 + 城市 archive 美化
 * ----------------------------------------------------
 * 1. 註冊 [ts_region_index] shortcode
 *    - 列出 6 個城市卡片
 *    - 用 ACF city_cover 圖、city_en 英文名、city_desc 描述
 *    - 顯示每個城市有幾位媒合對象
 *    - 點卡片連到 /region/taipei/ 之類的 archive
 *
 * 2. 城市 archive 頁的標題自動加裝飾（◆ TAIPEI ◆ + 描述）
 *
 * 使用方式：
 * 1. Snippets → Add New
 * 2. Title：「天上人間－踏雲尋境」
 * 3. 把這整段（含 <?php 開頭）貼進去
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 *
 * 然後到「踏雲尋境」頁面，內容貼一行：[ts_region_index]
 * ----------------------------------------------------
 */

// ============================================================
// Shortcode：[ts_region_index] 踏雲尋境索引
// ============================================================
if ( ! function_exists( 'ts_region_index_shortcode' ) ) {
    function ts_region_index_shortcode( $atts ) {
        // 取得 region taxonomy 所有條目
        $terms = get_terms( [
            'taxonomy'   => 'region',
            'hide_empty' => false,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ] );

        if ( ! $terms || is_wp_error( $terms ) ) {
            return '<div class="ts-no-regions">尚未建立地區資料</div>';
        }

        $output = '<section class="ts-yunyou-section">';

        // 標題區
        $output .= '<div class="ts-yunyou-header">';
        $output .= '<div class="ts-section-eyebrow ts-c-pink">◆ DESTINATIONS ◆</div>';
        $output .= '<h2 class="ts-yunyou-title ts-gradient-shift">踏　雲　尋　境</h2>';
        $output .= '<p class="ts-yunyou-subtitle">山光水色 · 人間有約</p>';
        $output .= '</div>';

        // 卡片網格
        $output .= '<div class="ts-yunyou-grid">';

        $i = 0;
        foreach ( $terms as $term ) {
            $term_id   = $term->term_id;
            $name      = $term->name;
            $slug      = $term->slug;

            // 取得 ACF（用 'region_' 前綴或 term ID）
            $city_en    = get_field( 'city_en', 'region_' . $term_id );
            $city_cover = get_field( 'city_cover', 'region_' . $term_id );
            $city_desc  = get_field( 'city_desc', 'region_' . $term_id );

            // 取得封面圖 URL（處理多種 ACF 格式）
            $cover_url = '';
            if ( is_array( $city_cover ) && isset( $city_cover['url'] ) ) {
                $cover_url = $city_cover['url'];
            } elseif ( is_string( $city_cover ) ) {
                $cover_url = $city_cover;
            } elseif ( is_numeric( $city_cover ) ) {
                $cover_url = wp_get_attachment_image_url( $city_cover, 'large' );
            }

            // 該城市的媒合對象數量
            $count = $term->count;

            // 城市 archive 連結
            $term_link = get_term_link( $term );

            // 卡片 HTML
            $output .= '<a href="' . esc_url( $term_link ) . '" class="ts-yunyou-card-link">';
            $output .= '<div class="ts-yunyou-card ts-yunyou-card-' . ( ($i % 6) + 1 ) . '">';

            // 封面圖
            $output .= '<div class="ts-yunyou-cover"';
            if ( $cover_url ) {
                $output .= ' style="background-image: url(' . esc_url( $cover_url ) . ');"';
            }
            $output .= '>';
            $output .= '<div class="ts-yunyou-overlay"></div>';
            $output .= '<div class="ts-yunyou-cover-content">';
            if ( $city_en ) {
                $output .= '<div class="ts-yunyou-en">' . esc_html( strtoupper( $city_en ) ) . '</div>';
            }
            $output .= '<div class="ts-yunyou-name">' . esc_html( $name ) . '</div>';
            $output .= '</div>';
            $output .= '</div>'; // ts-yunyou-cover

            // 內文
            $output .= '<div class="ts-yunyou-body">';
            if ( $city_desc ) {
                $output .= '<p class="ts-yunyou-desc">' . esc_html( $city_desc ) . '</p>';
            }
            $output .= '<div class="ts-yunyou-footer">';
            $output .= '<span class="ts-yunyou-count">' . intval( $count ) . ' 位會友</span>';
            $output .= '<span class="ts-yunyou-arrow">入山 →</span>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '</div>'; // ts-yunyou-card
            $output .= '</a>';

            $i++;
        }

        $output .= '</div>'; // ts-yunyou-grid
        $output .= '</section>';

        return $output;
    }
    add_shortcode( 'ts_region_index', 'ts_region_index_shortcode' );
}

// ============================================================
// 城市 archive 頁：自動在標題上方加 ACF 描述
// ============================================================
add_action( 'astra_before_archive_title', 'ts_region_archive_top', 10 );

if ( ! function_exists( 'ts_region_archive_top' ) ) {
    function ts_region_archive_top() {
        if ( ! is_tax( 'region' ) ) return;

        $term = get_queried_object();
        if ( ! $term ) return;

        $city_en   = get_field( 'city_en', 'region_' . $term->term_id );
        $city_desc = get_field( 'city_desc', 'region_' . $term->term_id );

        echo '<div class="ts-region-archive-banner">';
        if ( $city_en ) {
            echo '<div class="ts-region-en">◆ ' . esc_html( strtoupper( $city_en ) ) . ' ◆</div>';
        }
        echo '<h1 class="ts-region-name ts-gradient-shift">' . esc_html( $term->name ) . '</h1>';
        if ( $city_desc ) {
            echo '<p class="ts-region-desc">' . esc_html( $city_desc ) . '</p>';
        }
        echo '<div class="ts-region-divider"></div>';
        echo '</div>';
    }
}

// 隱藏 Astra 預設的 archive 標題（因為我們自己畫了）
add_action( 'wp_head', 'ts_region_hide_default_title', 99 );
function ts_region_hide_default_title() {
    if ( ! is_tax( 'region' ) ) return;
    echo '<style>
        body.tax-region .ast-archive-title,
        body.tax-region .page-title,
        body.tax-region .ast-archive-description > .ast-archive-title {
            display: none !important;
        }
    </style>';
}