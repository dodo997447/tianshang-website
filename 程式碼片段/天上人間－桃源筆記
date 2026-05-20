
/**
 * 天上人間 · 桃源手記 archive 標題美化
 * ----------------------------------------------------
 * 1. 「桃源手記」分類 + 4 個子分類進入時，自動加裝飾標題
 * 2. 顯示英文副標 + 文章數量
 *
 * 使用方式：
 * 1. Snippets → Add New
 * 2. Title：「天上人間－桃手筆記」
 * 3. 把這整段（含 <?php 開頭）貼進去
 * 4. Run snippet everywhere
 * 5. Save Changes and Activate
 * ----------------------------------------------------
 */

// 桃源手記分類英文對照
function ts_taoyuan_get_category_meta( $slug ) {
    $map = [
        'taoyuan'   => [ 'en' => 'PEACH NOTES',     'desc' => '入山者的筆記，山中的見聞與引路' ],
        'guide'     => [ 'en' => 'GUIDE',           'desc' => '初入此地，先讀這些短文' ],
        'stories'   => [ 'en' => 'STORIES',         'desc' => '人們在這方山林留下的故事' ],
        'etiquette' => [ 'en' => 'ETIQUETTE',       'desc' => '禮儀，是讓相遇更舒服的小事' ],
        'faq'       => [ 'en' => 'FAQ',             'desc' => '會友最常問的那些問題' ],
    ];
    return isset( $map[ $slug ] ) ? $map[ $slug ] : [ 'en' => '', 'desc' => '' ];
}

// 在 Astra archive 標題前面加我們自己的 banner
add_action( 'astra_before_archive_title', 'ts_taoyuan_archive_banner', 10 );

function ts_taoyuan_archive_banner() {
    // 只針對「桃源手記」分類及其子分類
    if ( ! is_category() ) return;

    $term = get_queried_object();
    if ( ! $term ) return;

    // 檢查是否為桃源手記或其子分類
    $slug = $term->slug;
    $valid_slugs = [ 'taoyuan', 'guide', 'stories', 'etiquette', 'faq' ];
    if ( ! in_array( $slug, $valid_slugs ) ) return;

    static $rendered = false;
    if ( $rendered ) return;
    $rendered = true;

    $meta = ts_taoyuan_get_category_meta( $slug );
    $count = $term->count;

    echo '<div class="ts-taoyuan-banner">';
    if ( $meta['en'] ) {
        echo '<div class="ts-taoyuan-en">◆ ' . esc_html( $meta['en'] ) . ' ◆</div>';
    }
    echo '<h1 class="ts-taoyuan-name ts-gradient-shift">' . esc_html( $term->name ) . '</h1>';
    if ( $meta['desc'] ) {
        echo '<p class="ts-taoyuan-desc">' . esc_html( $meta['desc'] ) . '</p>';
    }
    echo '<div class="ts-taoyuan-meta">';
    echo '<span class="ts-taoyuan-count">' . intval( $count ) . ' 篇札記</span>';
    echo '</div>';
    echo '<div class="ts-taoyuan-divider"></div>';
    echo '</div>';
}

// 隱藏 Astra 預設標題
add_action( 'wp_head', 'ts_taoyuan_hide_default_title', 99 );
function ts_taoyuan_hide_default_title() {
    if ( ! is_category() ) return;
    $term = get_queried_object();
    if ( ! $term ) return;
    $slug = $term->slug;
    if ( ! in_array( $slug, [ 'taoyuan', 'guide', 'stories', 'etiquette', 'faq' ] ) ) return;
    echo '<style>
        body.category .ast-archive-title,
        body.category .page-title {
            display: none !important;
        }
    </style>';
}