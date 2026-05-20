<?php
/**
 * 水色雲間－自製篩選器 v3
 * 即時篩選、條件累加、重置、確認篩選
 */
add_action('wp_footer', 'ts_custom_filter');
function ts_custom_filter() {
    if (!is_shop()) return;

    $products = wc_get_products([
        'status'  => 'publish',
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
    ]);

    $data = [];
    foreach ($products as $product) {
        $id       = $product->get_id();
        $price    = (float) $product->get_regular_price();
        $nat      = get_field('nationality', $id) ?: '';
        $age      = (int) get_field('age', $id);
        $cup      = get_field('cup_size', $id) ?: '';
        $cup      = str_replace('罩杯', '', $cup);
        $stype    = get_field('service_type', $id) ?: '';
        $services = get_field('services', $id) ?: '';

        $svc_arr = [];
        if ($services) {
            $svc_arr = array_map('trim', preg_split('/[、,，]/u', $services));
            $svc_arr = array_filter($svc_arr);
            $svc_arr = array_values($svc_arr);
        }

        $img  = get_the_post_thumbnail_url($id, 'medium') ?: wc_placeholder_img_src();
        $link = get_permalink($id);

        $data[] = [
            'id'       => $id,
            'name'     => $product->get_name(),
            'price'    => $price,
            'img'      => $img,
            'link'     => $link,
            'nat'      => $nat,
            'age'      => $age,
            'cup'      => $cup,
            'stype'    => $stype,
            'services' => $svc_arr,
        ];
    }

    $all_services = [];
    foreach ($data as $d) {
        foreach ($d['services'] as $s) {
            if ($s && !in_array($s, $all_services)) $all_services[] = $s;
        }
    }
    sort($all_services);

    $all_nat = array_unique(array_filter(array_column($data, 'nat')));
    sort($all_nat);

    $min_price = 1500;
    $max_price = 200000;
    ?>
<div id="ts-filter-overlay-custom"></div>
<div id="ts-filter-panel-custom">
  <div id="ts-filter-panel-header-custom">
    <span>篩選條件</span>
    <span id="ts-filter-active-count"></span>
  </div>
  <div id="ts-filter-panel-body-custom">

    <div class="ts-fs">
      <div class="ts-fs-title">價格範圍</div>
      <div class="ts-price-display">
        <span id="ts-price-min-label">NT$1,500</span>
        <span> — </span>
        <span id="ts-price-max-label">NT$200,000</span>
      </div>
      <div class="ts-range-wrap">
        <input type="range" id="ts-range-min" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $min_price; ?>" step="500">
        <input type="range" id="ts-range-max" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $max_price; ?>" step="500">
        <div class="ts-range-track"><div id="ts-range-fill"></div></div>
      </div>
    </div>

    <div class="ts-fs">
      <div class="ts-fs-title">服務類型</div>
      <div class="ts-chips" data-filter="stype">
        <span class="ts-chip" data-val="外送茶">外送茶</span>
        <span class="ts-chip" data-val="定點茶">定點茶</span>
        <span class="ts-chip" data-val="外送+定點">外送+定點</span>
      </div>
    </div>

    <div class="ts-fs">
      <div class="ts-fs-title">國籍</div>
      <div class="ts-chips" data-filter="nat">
        <?php foreach ($all_nat as $n): ?>
        <span class="ts-chip" data-val="<?php echo esc_attr($n); ?>"><?php echo esc_html($n); ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ts-fs">
      <div class="ts-fs-title">罩杯</div>
      <div class="ts-chips" data-filter="cup">
        <span class="ts-chip" data-val="A">A</span>
        <span class="ts-chip" data-val="B">B</span>
        <span class="ts-chip" data-val="C">C</span>
        <span class="ts-chip" data-val="D">D</span>
        <span class="ts-chip" data-val="E">E</span>
        <span class="ts-chip" data-val="F">F</span>
      </div>
    </div>

    <div class="ts-fs">
      <div class="ts-fs-title">可配合服務</div>
      <div class="ts-chips" data-filter="services">
        <?php foreach ($all_services as $s): ?>
        <span class="ts-chip" data-val="<?php echo esc_attr($s); ?>"><?php echo esc_html($s); ?></span>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- 已選條件列 -->
  <div id="ts-active-tags-wrap">
    <span id="ts-filter-reset" style="display:none">× 重置</span>
    <div id="ts-active-tags"></div>
  </div>

  <div id="ts-filter-result-bar">顯示 <span id="ts-filter-result-count">0</span> 位</div>
</div>

<button id="ts-filter-trigger-btn">⚙ 篩選</button>
<button id="ts-filter-close-custom">✓ 確認篩選</button>

<script>
(function(){
    var PRODUCTS  = <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>;
    var MIN_PRICE = <?php echo $min_price; ?>;
    var MAX_PRICE = <?php echo $max_price; ?>;

    var panel         = document.getElementById('ts-filter-panel-custom');
    var overlay       = document.getElementById('ts-filter-overlay-custom');
    var triggerBtn    = document.getElementById('ts-filter-trigger-btn');
    var closeBtn      = document.getElementById('ts-filter-close-custom');
    var countEl       = document.getElementById('ts-filter-result-count');
    var activeCountEl = document.getElementById('ts-filter-active-count');
    var activeTagsEl  = document.getElementById('ts-active-tags');
    var resetBtn      = document.getElementById('ts-filter-reset');
    var rangeMin      = document.getElementById('ts-range-min');
    var rangeMax      = document.getElementById('ts-range-max');
    var rangeFill     = document.getElementById('ts-range-fill');
    var priceMinLabel = document.getElementById('ts-price-min-label');
    var priceMaxLabel = document.getElementById('ts-price-max-label');

    var filters = { stype: [], nat: [], cup: [], services: [], priceMin: MIN_PRICE, priceMax: MAX_PRICE };

    // 移動篩選按鈕到商品區上方
    var shopLoop = document.querySelector('.woocommerce-ordering, .woocommerce-result-count');
    if (shopLoop && shopLoop.parentNode) {
        shopLoop.parentNode.insertBefore(triggerBtn, shopLoop);
    }

    function openPanel() {
        panel.classList.add('ts-open');
        overlay.style.display = 'block';
        closeBtn.style.display = 'block';
        triggerBtn.style.display = 'none';
        applyFilter();
    }
    function closePanel() {
        panel.classList.remove('ts-open');
        overlay.style.display = 'none';
        closeBtn.style.display = 'none';
        triggerBtn.style.display = 'block';
    }

    triggerBtn.addEventListener('click', openPanel);
    closeBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    // 取得符合條件的商品
    function getFiltered() {
        return PRODUCTS.filter(function(p) {
            if (p.price > 0 && (p.price < filters.priceMin || p.price > filters.priceMax)) return false;
            if (filters.stype.length > 0) {
                var match = filters.stype.some(function(s) { return p.stype && p.stype.indexOf(s) > -1; });
                if (!match) return false;
            }
            if (filters.nat.length > 0 && filters.nat.indexOf(p.nat) === -1) return false;
            if (filters.cup.length > 0 && filters.cup.indexOf(p.cup) === -1) return false;
            if (filters.services.length > 0) {
                var match = filters.services.every(function(s) { return p.services.indexOf(s) > -1; });
                if (!match) return false;
            }
            return true;
        });
    }

    // 即時套用篩選
    function applyFilter() {
        var filtered = getFiltered();
        countEl.textContent = filtered.length;

        var productList = document.querySelector('.woocommerce ul.products');
        if (!productList) return;

        var allItems = productList.querySelectorAll('li.product');
        allItems.forEach(function(item) {
            var found = filtered.some(function(p) {
                return item.className.indexOf('post-' + p.id) > -1;
            });
            item.style.display = found ? '' : 'none';
        });

        var total = filters.stype.length + filters.nat.length + filters.cup.length + filters.services.length;
        var priceChanged = filters.priceMin > MIN_PRICE || filters.priceMax < MAX_PRICE;
        if (priceChanged) total++;
        activeCountEl.textContent = total > 0 ? total + ' 項已選' : '';
    }

    // Chip 點擊
    document.querySelectorAll('.ts-chips .ts-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            var filterKey = this.closest('.ts-chips').dataset.filter;
            var val = this.dataset.val;
            var idx = filters[filterKey].indexOf(val);
            if (idx > -1) {
                filters[filterKey].splice(idx, 1);
                this.classList.remove('ts-chip-active');
            } else {
                filters[filterKey].push(val);
                this.classList.add('ts-chip-active');
            }
            applyFilter();
            renderActiveTags();
        });
    });

    // 價格滑桿
    function updateRangeUI() {
        var min = parseInt(rangeMin.value);
        var max = parseInt(rangeMax.value);
        var pct_min = (min - MIN_PRICE) / (MAX_PRICE - MIN_PRICE) * 100;
        var pct_max = (max - MIN_PRICE) / (MAX_PRICE - MIN_PRICE) * 100;
        rangeFill.style.left  = pct_min + '%';
        rangeFill.style.right = (100 - pct_max) + '%';
        priceMinLabel.textContent = 'NT$' + min.toLocaleString();
        priceMaxLabel.textContent = 'NT$' + max.toLocaleString();
        filters.priceMin = min;
        filters.priceMax = max;
        applyFilter();
    }

    rangeMin.addEventListener('input', function() {
        if (parseInt(this.value) > parseInt(rangeMax.value)) this.value = rangeMax.value;
        updateRangeUI();
    });
    rangeMax.addEventListener('input', function() {
        if (parseInt(this.value) < parseInt(rangeMin.value)) this.value = rangeMin.value;
        updateRangeUI();
    });
    updateRangeUI();

    // 重置
    resetBtn.addEventListener('click', function() {
        filters = { stype: [], nat: [], cup: [], services: [], priceMin: MIN_PRICE, priceMax: MAX_PRICE };
        document.querySelectorAll('.ts-chip').forEach(function(c) { c.classList.remove('ts-chip-active'); });
        rangeMin.value = MIN_PRICE;
        rangeMax.value = MAX_PRICE;
        updateRangeUI();
        applyFilter();
        renderActiveTags();
    });

    // 已選標籤
    function renderActiveTags() {
        var tags = [];
        filters.stype.forEach(function(v)    { tags.push({key:'stype', val:v}); });
        filters.nat.forEach(function(v)      { tags.push({key:'nat', val:v}); });
        filters.cup.forEach(function(v)      { tags.push({key:'cup', val:v}); });
        filters.services.forEach(function(v) { tags.push({key:'services', val:v}); });

        resetBtn.style.display = tags.length > 0 ? 'inline-flex' : 'none';

        if (tags.length === 0) { activeTagsEl.innerHTML = ''; return; }

        activeTagsEl.innerHTML = tags.map(function(t) {
            return '<span class="ts-active-tag" data-key="'+t.key+'" data-val="'+t.val+'">× '+t.val+'</span>';
        }).join('');

        activeTagsEl.querySelectorAll('.ts-active-tag').forEach(function(el) {
            el.addEventListener('click', function() {
                var key = this.dataset.key;
                var val = this.dataset.val;
                var idx = filters[key].indexOf(val);
                if (idx > -1) filters[key].splice(idx, 1);
                document.querySelectorAll('.ts-chips[data-filter="'+key+'"] .ts-chip').forEach(function(c) {
                    if (c.dataset.val === val) c.classList.remove('ts-chip-active');
                });
                applyFilter();
                renderActiveTags();
            });
        });
    }

    applyFilter();
})();
</script>

<style>
#ts-filter-trigger-btn {
    display: block;
    width: 100%;
    background: #2D1B4E;
    color: #FF8AB2;
    border: 0.5px solid #FF8AB2;
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 15px;
    letter-spacing: 4px;
    cursor: pointer;
    font-family: 'Noto Serif TC', serif;
    margin-bottom: 16px;
    text-align: center;
    box-sizing: border-box;
}
#ts-filter-close-custom {
    display: none;
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #FF8AB2;
    color: #0A0118;
    border: none;
    border-radius: 24px;
    padding: 12px 32px;
    font-size: 14px;
    letter-spacing: 3px;
    cursor: pointer;
    font-family: 'Noto Serif TC', serif;
    z-index: 99999;
    white-space: nowrap;
    font-weight: 500;
}
#ts-filter-overlay-custom {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    z-index: 9990;
}
#ts-filter-panel-custom {
    display: none;
    position: fixed;
    top: 60px; left: 0; right: 0;
    height: 50vh;
    max-height: 50vh;
    overflow-y: auto;
    background: #0A0118;
    z-index: 9995;
    border-bottom: 1px solid rgba(255,138,178,0.3);
    box-shadow: 0 8px 32px rgba(0,0,0,0.7);
    box-sizing: border-box;
}
#ts-filter-panel-custom.ts-open { display: block; }
#ts-filter-panel-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 0.5px solid rgba(255,138,178,0.2);
    color: #E0CFFF;
    font-family: 'Noto Serif TC', serif;
    font-size: 16px;
    letter-spacing: 4px;
    background: rgba(200,162,255,0.08);
    position: sticky;
    top: 0;
    z-index: 2;
}
#ts-filter-active-count { font-size: 12px; color: #FF8AB2; letter-spacing: 2px; }
#ts-filter-panel-body-custom { padding: 16px 20px; }
.ts-fs { margin-bottom: 20px; }
.ts-fs-title { color: #FF8AB2; font-size: 11px; letter-spacing: 4px; margin-bottom: 10px; }
.ts-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.ts-chip {
    padding: 6px 14px;
    border-radius: 999px;
    border: 0.5px solid rgba(200,162,255,0.3);
    color: rgba(224,207,255,0.7);
    font-size: 12px;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.15s;
    font-family: 'Noto Sans TC', sans-serif;
}
.ts-chip:hover { border-color: rgba(200,162,255,0.6); color: #E0CFFF; }
.ts-chip.ts-chip-active {
    background: rgba(255,138,178,0.15);
    border-color: #FF8AB2;
    color: #FF8AB2;
}
.ts-price-display {
    color: rgba(224,207,255,0.8);
    font-size: 13px;
    margin-bottom: 14px;
    letter-spacing: 1px;
}
.ts-range-wrap {
    position: relative;
    height: 20px;
    margin-bottom: 4px;
}
.ts-range-wrap input[type="range"] {
    position: absolute;
    width: 100%;
    height: 4px;
    top: 8px;
    left: 0;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    z-index: 3;
}
.ts-range-wrap input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #FF8AB2;
    cursor: pointer;
    pointer-events: all;
    border: 2px solid #0A0118;
}
.ts-range-track {
    position: absolute;
    width: 100%;
    height: 4px;
    top: 8px;
    background: rgba(200,162,255,0.15);
    border-radius: 2px;
    z-index: 1;
}
#ts-range-fill {
    position: absolute;
    height: 100%;
    background: linear-gradient(90deg, #C8A2FF, #FF8AB2);
    border-radius: 2px;
}
#ts-active-tags-wrap {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-top: 0.5px solid rgba(255,138,178,0.1);
    min-height: 0;
}
#ts-filter-reset {
    display: none;
    align-items: center;
    padding: 4px 12px;
    background: rgba(255,59,131,0.15);
    border: 0.5px solid rgba(255,59,131,0.4);
    border-radius: 999px;
    color: #FF3B83;
    font-size: 12px;
    letter-spacing: 1px;
    cursor: pointer;
    white-space: nowrap;
}
.ts-active-tag {
    padding: 4px 12px;
    background: rgba(255,138,178,0.15);
    border: 0.5px solid rgba(255,138,178,0.4);
    border-radius: 999px;
    color: #FF8AB2;
    font-size: 12px;
    letter-spacing: 1px;
    cursor: pointer;
}
#ts-filter-result-bar {
    padding: 12px 20px;
    border-top: 0.5px solid rgba(255,138,178,0.15);
    color: rgba(224,207,255,0.6);
    font-size: 13px;
    letter-spacing: 3px;
    text-align: center;
    font-family: 'Noto Serif TC', serif;
}
#ts-filter-result-count { color: #FF8AB2; font-size: 15px; }
@media (min-width: 922px) {
    #ts-filter-trigger-btn,
    #ts-filter-close-custom,
    #ts-filter-panel-custom,
    #ts-filter-overlay-custom { display: none !important; }
}
</style>
<?php }