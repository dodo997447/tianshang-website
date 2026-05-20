
/**
 * 天上人間 · 青羚報音  archive 標題美化
 * ----------------------------------------------------
 * 1. 「青羚報音」分類 + 4 個子分類進入時，自動加裝飾標題
 *
 * 使用方式：
 * 1. Snippets → Add New
 * 2. Title：「天上人間－青羚報音」
 * 3. 把這整段（含 <?php 開頭）貼進去
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 * ----------------------------------------------------
 */

// 青羚報音分類英文對照
function ts_news_get_category_meta( $slug ) {
    $map = [
        'news'     => [ 'en' => 'NEWS',         'desc' => '人間動態．即時通報' ],
        'announce' => [ 'en' => 'ANNOUNCEMENT', 'desc' => '重要事項．即時公告' ],
        'update'   => [ 'en' => 'UPDATE',       'desc' => '系統更新．功能調整' ],
        'rule'     => [ 'en' => 'RULES',        'desc' => '會員規範．守則條款' ],
        'event'    => [ 'en' => 'EVENT',        'desc' => '城南聚會．線下相遇' ],
    ];
    return isset( $map[ $slug ] ) ? $map[ $slug ] : [ 'en' => '', 'desc' => '' ];
}

// 在 Astra archive 標題前加 banner
add_action( 'astra_before_archive_title', 'ts_news_archive_banner', 10 );

function ts_news_archive_banner() {
    if ( ! is_category() ) return;

    $term = get_queried_object();
    if ( ! $term ) return;

    $slug = $term->slug;
    $valid_slugs = [ 'news', 'announce', 'update', 'rule', 'event' ];
    if ( ! in_array( $slug, $valid_slugs ) ) return;

    static $rendered = false;
    if ( $rendered ) return;
    $rendered = true;

    $meta = ts_news_get_category_meta( $slug );
    $count = $term->count;

    echo '<div class="ts-news-banner">';
    if ( $meta['en'] ) {
        echo '<div class="ts-news-en">◆ ' . esc_html( $meta['en'] ) . ' ◆</div>';
    }
    echo '<h1 class="ts-news-name ts-gradient-shift">' . esc_html( $term->name ) . '</h1>';
    if ( $meta['desc'] ) {
        echo '<p class="ts-news-desc">' . esc_html( $meta['desc'] ) . '</p>';
    }
    echo '<div class="ts-news-meta-bar">';
    echo '<span class="ts-news-count">' . intval( $count ) . ' 則消息</span>';
    echo '</div>';
    echo '<div class="ts-news-divider"></div>';
    echo '</div>';
}

// 隱藏 Astra 預設標題
add_action( 'wp_head', 'ts_news_hide_default_title', 99 );
function ts_news_hide_default_title() {
    if ( ! is_category() ) return;
    $term = get_queried_object();
    if ( ! $term ) return;
    if ( ! in_array( $term->slug, [ 'news', 'announce', 'update', 'rule', 'event' ] ) ) return;
    echo '<style>
        body.category .ast-archive-title,
        body.category .page-title {
            display: none !important;
        }
    </style>';
}