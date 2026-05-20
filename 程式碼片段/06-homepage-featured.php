
/**
 * 天上人間 · 首頁「水色雲間精選」動態 Shortcode
 * ----------------------------------------------------
 * 在首頁 Elementor 中用 [ts_latest_matches] 即可顯示
 * 自動抓最新 3 筆「水色雲間」資料，會用真實封面圖 + ACF 資料
 *
 * 使用方式：
 * 1. Snippets → Add New
 * 2. Title：「天上人間－首頁精選 Shortcode」
 * 3. 把這整段（含 <?php 開頭）貼進去
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 *
 * 然後在首頁編輯處：
 * 6. Elementor 編輯首頁 → 找到原本的 HTML widget「水色雲間精選」那一塊
 * 7. 把整段 HTML 程式碼刪掉
 * 8. 替換成這一行：[ts_latest_matches]
 * 9. 儲存
 * ----------------------------------------------------
 */

if ( ! function_exists( 'ts_latest_matches_shortcode' ) ) {
    function ts_latest_matches_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'count' => 3,
        ], $atts );

        $query = new WP_Query( [
            'post_type'      => 'matches',
            'posts_per_page' => intval( $atts['count'] ),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ] );

        if ( ! $query->have_posts() ) {
            return '<div class="ts-no-matches">尚無媒合資料</div>';
        }

        $output = '';

        // 區塊標題
        $output .= '<section class="ts-section ts-matches-section">';
        $output .= '<div class="ts-matches-head">';
        $output .= '<div>';
        $output .= '<div class="ts-section-eyebrow ts-c-pink">◆ LATEST ◆</div>';
        $output .= '<div class="ts-matches-title ts-gradient-shift">水  色  雲  間</div>';
        $output .= '</div>';
        $output .= '<a href="' . esc_url( get_post_type_archive_link( 'matches' ) ) . '" class="ts-card-more">查看全部 →</a>';
        $output .= '</div>';

        // 卡片 grid
        $output .= '<div class="ts-three-column">';

        $i = 0;
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();

            // 取得 ACF 欄位
            $nickname   = get_field( 'nickname', $post_id );
            $age        = get_field( 'age', $post_id );
            $district   = get_field( 'district', $post_id );
            $height     = get_field( 'height', $post_id );
            $occupation = get_field( 'occupation', $post_id );
            $tags       = get_field( 'tags', $post_id );

            // 取得地區
            $regions = get_the_terms( $post_id, 'region' );
            $region_name = '';
            if ( $regions && ! is_wp_error( $regions ) ) {
                $region_name = $regions[0]->name;
            }

            // 取得封面圖（優先用特色圖片）
            $thumb_url = get_the_post_thumbnail_url( $post_id, 'large' );

            // 卡片本體
            $output .= '<a href="' . esc_url( get_permalink() ) . '" class="ts-match-card-link">';
            $output .= '<div class="ts-match-card">';

            // 封面區塊
            $output .= '<div class="ts-match-cover ts-cover-' . ( ($i % 3) + 1 ) . '"';
            if ( $thumb_url ) {
                $output .= ' style="background-image: url(' . esc_url( $thumb_url ) . '); background-size: cover; background-position: center;"';
            }
            $output .= '>';
            $output .= '<div class="ts-laser-line ts-laser-delay-' . ( $i % 3 ) . '"></div>';
            $output .= '</div>';

            // 內文區塊
            $output .= '<div class="ts-match-body">';

            // 暱稱 + 年齡
            $output .= '<div class="ts-match-name-row">';
            $output .= '<span class="ts-match-name">' . esc_html( $nickname ?: get_the_title() ) . '</span>';
            if ( $age ) {
                $output .= '<span class="ts-match-age">' . esc_html( $age ) . '</span>';
            }
            $output .= '</div>';

            // 資訊條（城市 · 職業 · 身高）
            $info_parts = [];
            if ( $region_name ) $info_parts[] = $region_name;
            if ( $occupation ) $info_parts[] = $occupation;
            if ( $height ) $info_parts[] = $height;
            if ( $info_parts ) {
                $output .= '<div class="ts-match-info">' . esc_html( implode( ' · ', $info_parts ) ) . '</div>';
            }

            // 興趣標籤（只取前 2 個避免擠）
            if ( $tags ) {
                $tags_arr = preg_split( '/[,，、]/u', $tags );
                $tags_arr = array_filter( array_map( 'trim', $tags_arr ) );
                $tags_arr = array_slice( $tags_arr, 0, 2 );
                $colors = ['ts-pill-pink', 'ts-pill-purple'];
                $output .= '<div class="ts-match-tags">';
                $j = 0;
                foreach ( $tags_arr as $tag ) {
                    $output .= '<span class="ts-pill ' . $colors[ $j % 2 ] . '">' . esc_html( $tag ) . '</span>';
                    $j++;
                }
                $output .= '</div>';
            }

            $output .= '</div>'; // ts-match-body
            $output .= '</div>'; // ts-match-card
            $output .= '</a>';

            $i++;
        }

        wp_reset_postdata();

        $output .= '</div>'; // ts-three-column
        $output .= '</section>';

        return $output;
    }
    add_shortcode( 'ts_latest_matches', 'ts_latest_matches_shortcode' );
}

