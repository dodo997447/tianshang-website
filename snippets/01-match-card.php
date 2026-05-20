
/**
 * 天上人間 · 最新媒合 ACF 顯示補丁 v3
 * ----------------------------------------------------
 * v3 修正：自動把資料中的 "\n" 字串轉成真實換行，
 *           解決自我介紹裡看到 \n 文字的問題。
 *
 * 使用方式：
 * 1. 進 Snippets → All Snippets
 * 2. 編輯「天上人間－媒合卡片 v2」這筆
 * 3. 把全部內容刪除
 * 4. 把這份檔案整段（含 <?php 開頭）貼進去
 * 5. Save Changes
 *
 * 或者：
 * 1. 把 v2 停用
 * 2. 新增 snippet「天上人間－媒合卡片 v3」
 * 3. 貼這份
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 * ----------------------------------------------------
 */

// ============================================================
// 工具函數：清理文字（把 \n 字串轉成真實換行 / 空格）
// ============================================================
if ( ! function_exists( 'ts_clean_intro_text' ) ) {
    /**
     * @param string $text 原始文字
     * @param string $mode 'br' = 換真實換行（給內頁用），'space' = 換空格（給節錄用）
     */
    function ts_clean_intro_text( $text, $mode = 'br' ) {
        if ( empty( $text ) ) return '';

        // 把各種寫法的「\n 字串」全部統一處理：
        // \\n（雙反斜線+n）、\n（單反斜線+n）、實際的換行符
        $patterns = [
            '\\\\n',   // \\n
            '\\n',     // \n
        ];

        if ( $mode === 'space' ) {
            // 節錄模式：換成空格（節錄不需要分行）
            $text = preg_replace( '/\\\\n|\\\n/', ' ', $text );
            $text = preg_replace( '/\s+/', ' ', $text ); // 多空格合一
        } else {
            // 內頁模式：換成真實換行
            $text = preg_replace( '/\\\\n|\\\n/', "\n", $text );
        }

        return trim( $text );
    }
}

// ============================================================
// 工具函數：把興趣標籤字串拆成標籤元素
// ============================================================
if ( ! function_exists( 'ts_render_tags' ) ) {
    function ts_render_tags( $tags_string ) {
        if ( empty( $tags_string ) ) return '';
        $tags = preg_split( '/[,，、]/u', $tags_string );
        $tags = array_filter( array_map( 'trim', $tags ) );
        $colors = ['ts-pill-pink', 'ts-pill-purple'];
        $output = '<div class="ts-match-tags">';
        $i = 0;
        foreach ( $tags as $tag ) {
            $color_class = $colors[ $i % 2 ];
            $output .= '<span class="ts-pill ' . $color_class . '">' . esc_html( $tag ) . '</span>';
            $i++;
        }
        $output .= '</div>';
        return $output;
    }
}

// ============================================================
// 工具函數：取得媒合對象的地區資訊
// ============================================================
if ( ! function_exists( 'ts_get_region_label' ) ) {
    function ts_get_region_label( $post_id ) {
        $terms = get_the_terms( $post_id, 'region' );
        if ( ! $terms || is_wp_error( $terms ) ) return '';
        $names = wp_list_pluck( $terms, 'name' );
        return implode( ' · ', $names );
    }
}

// ============================================================
// 工具函數：渲染媒合卡片補充資訊（列表頁用）
// ============================================================
if ( ! function_exists( 'ts_render_match_card_extra' ) ) {
    function ts_render_match_card_extra( $post_id ) {
        $age        = get_field( 'age', $post_id );
        $district   = get_field( 'district', $post_id );
        $height     = get_field( 'height', $post_id );
        $occupation = get_field( 'occupation', $post_id );
        $intro      = get_field( 'introduction', $post_id );
        $tags       = get_field( 'tags', $post_id );
        $region     = ts_get_region_label( $post_id );

        $output = '<div class="ts-match-info-block">';

        // 詳細資料條
        $output .= '<div class="ts-match-detail-row">';
        if ( $age ) $output .= '<span class="ts-match-age-label">' . esc_html( $age ) . '歲</span>';
        if ( $region ) $output .= '<span class="ts-match-region">' . esc_html( $region ) . '</span>';
        if ( $district ) $output .= '<span class="ts-match-district">' . esc_html( $district ) . '</span>';
        if ( $occupation ) $output .= '<span class="ts-match-job">' . esc_html( $occupation ) . '</span>';
        if ( $height ) $output .= '<span class="ts-match-height">' . esc_html( $height ) . '</span>';
        $output .= '</div>';

        // 自我介紹節錄（清掉 \n 並截短）
        if ( $intro ) {
            $intro_clean = ts_clean_intro_text( $intro, 'space' );
            $short = mb_substr( $intro_clean, 0, 50 );
            if ( mb_strlen( $intro_clean ) > 50 ) $short .= '…';
            $output .= '<div class="ts-match-intro-excerpt">' . esc_html( $short ) . '</div>';
        }

        // 興趣標籤
        if ( $tags ) {
            $output .= ts_render_tags( $tags );
        }

        $output .= '</div>';
        return $output;
    }
}

// ============================================================
// 列表頁：用 the_excerpt 把 ACF 補在節錄之後
// ============================================================
add_filter( 'get_the_excerpt', 'ts_append_match_excerpt', 99, 2 );

function ts_append_match_excerpt( $excerpt, $post = null ) {
    if ( ! $post ) $post = get_post();
    if ( ! $post || $post->post_type !== 'matches' ) return $excerpt;
    if ( is_singular() ) return $excerpt;

    $extra = ts_render_match_card_extra( $post->ID );
    return $excerpt . $extra;
}

// ============================================================
// 列表頁 + 內頁：用 the_content 注入 ACF 資料
// ============================================================
add_filter( 'the_content', 'ts_inject_match_card_content', 99 );

function ts_inject_match_card_content( $content ) {
    // 內頁處理
    if ( is_singular( 'matches' ) ) {
        return $content . ts_render_single_match_detail( get_the_ID() );
    }

    // 列表頁處理（archive）
    if ( ( is_post_type_archive( 'matches' ) || is_tax( 'region' ) ) && in_the_loop() ) {
        global $post;
        if ( $post && $post->post_type === 'matches' ) {
            return $content . ts_render_match_card_extra( $post->ID );
        }
    }

    return $content;
}

// ============================================================
// 單篇內頁：渲染詳細區塊
// ============================================================
if ( ! function_exists( 'ts_render_single_match_detail' ) ) {
    function ts_render_single_match_detail( $post_id ) {
        $nickname   = get_field( 'nickname', $post_id );
        $age        = get_field( 'age', $post_id );
        $district   = get_field( 'district', $post_id );
        $height     = get_field( 'height', $post_id );
        $occupation = get_field( 'occupation', $post_id );
        $intro      = get_field( 'introduction', $post_id );
        $tags       = get_field( 'tags', $post_id );
        $gallery    = get_field( 'gallery', $post_id );
        $region     = ts_get_region_label( $post_id );

        $extra = '<div class="ts-single-match-detail">';

        // 主資訊條
        $extra .= '<div class="ts-single-info-bar">';
        if ( $nickname ) {
            $extra .= '<div class="ts-single-name">' . esc_html( $nickname );
            if ( $age ) $extra .= '<span class="ts-single-age"> · ' . esc_html( $age ) . '</span>';
            $extra .= '</div>';
        }
        $loc = '';
        if ( $region ) $loc .= esc_html( $region );
        if ( $district ) $loc .= ( $loc ? ' · ' : '' ) . esc_html( $district );
        if ( $loc ) {
            $extra .= '<div class="ts-single-location">' . $loc . '</div>';
        }
        $extra .= '</div>';

        // 條列資訊
        $extra .= '<div class="ts-single-info-grid">';
        if ( $occupation ) {
            $extra .= '<div class="ts-info-item"><span class="ts-info-label">職業</span><span class="ts-info-value">' . esc_html( $occupation ) . '</span></div>';
        }
        if ( $height ) {
            $extra .= '<div class="ts-info-item"><span class="ts-info-label">身高</span><span class="ts-info-value">' . esc_html( $height ) . '</span></div>';
        }
        if ( $age ) {
            $extra .= '<div class="ts-info-item"><span class="ts-info-label">年齡</span><span class="ts-info-value">' . esc_html( $age ) . '</span></div>';
        }
        if ( $region ) {
            $extra .= '<div class="ts-info-item"><span class="ts-info-label">所在地</span><span class="ts-info-value">' . esc_html( $region ) . '</span></div>';
        }
        $extra .= '</div>';

        // 興趣標籤
        if ( $tags ) {
            $extra .= '<div class="ts-single-tags-wrap">';
            $extra .= '<div class="ts-single-section-title">興趣．關鍵字</div>';
            $extra .= ts_render_tags( $tags );
            $extra .= '</div>';
        }

        // 自我介紹（清掉 \n 並轉換行）
        if ( $intro ) {
            $intro_clean = ts_clean_intro_text( $intro, 'br' );
            $extra .= '<div class="ts-single-intro-wrap">';
            $extra .= '<div class="ts-single-section-title">自我介紹</div>';
            $extra .= '<div class="ts-single-intro-body">' . nl2br( esc_html( $intro_clean ) ) . '</div>';
            $extra .= '</div>';
        }

        // 相簿
        if ( $gallery ) {
            $extra .= '<div class="ts-single-gallery-wrap">';
            $extra .= '<div class="ts-single-section-title">相簿</div>';
            $extra .= '<div class="ts-single-gallery">';

            if ( is_array( $gallery ) && isset( $gallery[0] ) && is_array( $gallery[0] ) ) {
                // ACF Gallery field（陣列）
                foreach ( $gallery as $img ) {
                    if ( isset( $img['url'] ) ) {
                        $alt = isset( $img['alt'] ) ? $img['alt'] : '';
                        $extra .= '<img src="' . esc_url( $img['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />';
                    }
                }
            } elseif ( is_array( $gallery ) && isset( $gallery['url'] ) ) {
                // ACF Image field 物件型（單張）
                $alt = isset( $gallery['alt'] ) ? $gallery['alt'] : '';
                $extra .= '<img src="' . esc_url( $gallery['url'] ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />';
            } elseif ( is_string( $gallery ) ) {
                // ACF Image field URL 型
                $extra .= '<img src="' . esc_url( $gallery ) . '" loading="lazy" />';
            } elseif ( is_numeric( $gallery ) ) {
                // ACF Image field ID 型
                $img_url = wp_get_attachment_image_url( $gallery, 'large' );
                if ( $img_url ) {
                    $extra .= '<img src="' . esc_url( $img_url ) . '" loading="lazy" />';
                }
            }

            $extra .= '</div>';
            $extra .= '</div>';
        }

        $extra .= '</div>'; // ts-single-match-detail
        return $extra;
    }
}
