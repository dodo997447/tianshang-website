add_action('wp_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        var districtToCity = {
            '信義區': '台北市', '中山區': '台北市', '大安區': '台北市',
            '松山區': '台北市', '內湖區': '台北市', '士林區': '台北市',
            '北投區': '台北市', '南港區': '台北市', '文山區': '台北市',
            '萬華區': '台北市', '中正區': '台北市', '大同區': '台北市',
            '太平區': '台中市', '北屯區': '台中市', '西屯區': '台中市',
            '南屯區': '台中市', '豐原區': '台中市', '大里區': '台中市',
            '鼓山區': '高雄市', '三民區': '高雄市', '苓雅區': '高雄市',
            '前金區': '高雄市', '新興區': '高雄市', '前鎮區': '高雄市',
            '鳳山區': '高雄市', '左營區': '高雄市', '楠梓區': '高雄市',
            '小港區': '高雄市'
        };

        // 列表頁卡片：區 → 縣市
        jQuery('.ast-woo-product-category').each(function() {
            var $el = jQuery(this);
            var text = $el.text().trim();
            if (districtToCity[text]) {
                $el.text(districtToCity[text]);
            }
        });

        // 內頁：讓縣市排在區前面（交換順序）
        var $catLinks = jQuery('.posted_in a, .product_meta .posted_in a');
        if ($catLinks.length >= 2) {
            var items = [];
            $catLinks.each(function() {
                items.push(jQuery(this).prop('outerHTML'));
            });
            // 縣市排前面，區排後面
            items.sort(function(a, b) {
                var aText = jQuery(a).text().trim();
                var bText = jQuery(b).text().trim();
                var aIsCity = aText.includes('市') || aText.includes('縣');
                var bIsCity = bText.includes('市') || bText.includes('縣');
                if (aIsCity && !bIsCity) return -1;
                if (!aIsCity && bIsCity) return 1;
                return 0;
            });
            var $container = jQuery('.posted_in');
            var label = $container.contents().filter(function() {
                return this.nodeType === 3;
            }).first().text();
            $container.html(label + items.join(', '));
        }
    });
    </script>
    <?php
});