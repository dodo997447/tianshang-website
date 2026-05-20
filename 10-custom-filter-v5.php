<?php
/**
 * 水色雲間－自製篩選器 v5
 * 桌機：左側 sidebar
 * 手機：浮層
 * 功能：國旗、數量、即時篩選、條件累加、重置
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
        $cup      = get_field('cup_size', $id) ?: '';
        $cup      = str_replace('罩杯以上', '+', $cup);
        $cup      = str_replace('罩杯', '', $cup);
        $stype    = get_field('service_type', $id) ?: '';
        $services = get_field('services', $id) ?: '';

        $svc_arr = [];
        if ($services) {
            $svc_arr = array_map('trim', preg_split('/[、,，]/u', $services));
            $svc_arr = array_filter($svc_arr);
            $svc_arr = array_values($svc_arr);
        }

        $data[] = [
            'id'       => $id,
            'name'     => $product->get_name(),
            'price'    => $price,
            'nat'      => $nat,
            'cup'      => $cup,
            'stype'    => $stype,
            'services' => $svc_arr,
        ];
    }

    // 國旗對應表
    $flags = [
        '台灣'   => '🇹🇼',
        '日本'   => '🇯🇵',
        '韓國'   => '🇰🇷',
        '越南'   => '🇻🇳',
        '泰國'   => '🇹🇭',
        '馬來'   => '🇲🇾',
        '港澳'   => '🇭🇰',
        '大陸'   => '🇨🇳',
        '新加坡' => '🇸🇬',
        '俄羅斯' => '🇷🇺',
        '印尼'   => '🇮🇩',
        '寮國'   => '🇱🇦',
        '其他國家'=> '🌏',
    ];

    // 收集所有服務
    $all_services = [];
    foreach ($data as $d) {
        foreach ($d['services'] as $s) {
            if ($s && !in_array($s, $all_services)) $all_services[] = $s;
        }
    }
    sort($all_services);

    // 收集國籍（按照 flags 順序）
    $all_nat_raw = array_unique(array_filter(array_column($data, 'nat')));
    $all_nat = [];
    foreach ($flags as $name => $flag) {
        if (in_array($name, $all_nat_raw)) $all_nat[] = $name;
    }
    foreach ($all_nat_raw as $n) {
        if (!in_array($n, $all_nat)) $all_nat[] = $n;
    }

    $min_price = 1500;
    $max_price = 200000;

    $flags_json = json_encode($flags, JSON_UNESCAPED_UNICODE);
    $data_json  = json_encode($data, JSON_UNESCAPED_UNICODE);
    ?>

<!-- 篩選器 HTML（桌機sidebar + 手機浮層共用） -->
<div id="ts-cf-wrap">

  <!-- 桌機版 sidebar -->
  <div id="ts-cf-sidebar">
    <div id="ts-cf-sidebar-inner">

      <div class="ts-cf-section">
        <div class="ts-cf-title">價格範圍</div>
        <div class="ts-cf-price-display">
          <span id="ts-cf-price-min">NT$1,500</span>
          <span> — </span>
          <span id="ts-cf-price-max">NT$200,000</span>
        </div>
        <div class="ts-cf-range-wrap">
          <input type="range" id="ts-cf-range-min" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $min_price; ?>" step="500">
          <input type="range" id="ts-cf-range-max" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $max_price; ?>" step="500">
          <div class="ts-cf-range-track"><div id="ts-cf-range-fill"></div></div>
        </div>
      </div>

      <div class="ts-cf-section">
        <div class="ts-cf-title">服務類型</div>
        <div class="ts-cf-options" data-filter="stype">
          <div class="ts-cf-option" data-val="外送茶">
            <span class="ts-cf-opt-name">外送茶</span>
            <span class="ts-cf-opt-count" data-key="stype" data-val="外送茶">0</span>
          </div>
          <div class="ts-cf-option" data-val="定點茶">
            <span class="ts-cf-opt-name">定點茶</span>
            <span class="ts-cf-opt-count" data-key="stype" data-val="定點茶">0</span>
          </div>
          <div class="ts-cf-option" data-val="外送+定點">
            <span class="ts-cf-opt-name">外送+定點</span>
            <span class="ts-cf-opt-count" data-key="stype" data-val="外送+定點">0</span>
          </div>
        </div>
      </div>

      <div class="ts-cf-section">
        <div class="ts-cf-title">國籍</div>
        <div class="ts-cf-options" data-filter="nat">
          <?php foreach ($all_nat as $n):
            $flag = isset($flags[$n]) ? $flags[$n] : '🌏'; ?>
          <div class="ts-cf-option" data-val="<?php echo esc_attr($n); ?>">
            <span class="ts-cf-flag"><?php echo $flag; ?></span>
            <span class="ts-cf-opt-name"><?php echo esc_html($n); ?></span>
            <span class="ts-cf-opt-count" data-key="nat" data-val="<?php echo esc_attr($n); ?>">0</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="ts-cf-section">
        <div class="ts-cf-title">罩杯</div>
        <div class="ts-cf-chips" data-filter="cup">
          <?php foreach (['A','B','C','D','E','F','G+'] as $c): ?>
          <span class="ts-cf-chip" data-val="<?php echo $c; ?>"><?php echo $c; ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="ts-cf-section">
        <div class="ts-cf-title">可配合服務</div>
        <div class="ts-cf-chips" data-filter="services">
          <?php foreach ($all_services as $s): ?>
          <span class="ts-cf-chip" data-val="<?php echo esc_attr($s); ?>"><?php echo esc_html($s); ?></span>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div><!-- end sidebar -->

  <!-- 手機版浮層面板 -->
  <div id="ts-cf-overlay"></div>
  <div id="ts-cf-panel">
    <div id="ts-cf-panel-header">
      <span>篩選條件</span>
      <span id="ts-cf-active-count"></span>
    </div>
    <div id="ts-cf-panel-body">
      <!-- 手機版篩選內容由 JS 複製 sidebar 內容 -->
    </div>
    <div id="ts-cf-tags-wrap">
      <span id="ts-cf-reset-mobile" style="display:none">× 重置</span>
      <div id="ts-cf-tags-mobile"></div>
    </div>
    <div id="ts-cf-result-bar">顯示 <span id="ts-cf-count-mobile">0</span> 位</div>
  </div>

  <!-- 手機觸發按鈕 -->
  <button id="ts-cf-trigger">⚙ 篩選</button>
  <!-- 外部條件列 -->
  <div id="ts-cf-tags-outside"></div>
  <!-- 確認按鈕 -->
  <button id="ts-cf-confirm">✓ 確認篩選</button>

</div><!-- end wrap -->

<script>
(function(){
    var PRODUCTS  = <?php echo $data_json; ?>;
    var FLAGS     = <?php echo $flags_json; ?>;
    var MIN_PRICE = <?php echo $min_price; ?>;
    var MAX_PRICE = <?php echo $max_price; ?>;

    var filters = { stype:[], nat:[], cup:[], services:[], priceMin:MIN_PRICE, priceMax:MAX_PRICE };

    var panel       = document.getElementById('ts-cf-panel');
    var overlay     = document.getElementById('ts-cf-overlay');
    var triggerBtn  = document.getElementById('ts-cf-trigger');
    var confirmBtn  = document.getElementById('ts-cf-confirm');
    var outsideEl   = document.getElementById('ts-cf-tags-outside');
    var resetMobile = document.getElementById('ts-cf-reset-mobile');
    var tagsMobile  = document.getElementById('ts-cf-tags-mobile');
    var countMobile = document.getElementById('ts-cf-count-mobile');
    var activeCount = document.getElementById('ts-cf-active-count');
    var rangeMin    = document.getElementById('ts-cf-range-min');
    var rangeMax    = document.getElementById('ts-cf-range-max');
    var rangeFill   = document.getElementById('ts-cf-range-fill');
    var priceMinEl  = document.getElementById('ts-cf-price-min');
    var priceMaxEl  = document.getElementById('ts-cf-price-max');

    // 移動觸發按鈕和外部條件列到商品區上方
    var shopArea = document.querySelector('.woocommerce-result-count, .woocommerce-ordering');
    if (shopArea && shopArea.parentNode) {
        shopArea.parentNode.insertBefore(outsideEl, shopArea);
        shopArea.parentNode.insertBefore(triggerBtn, outsideEl);
    }

    // 手機版：複製 sidebar 內容到 panel
    var sidebarInner = document.getElementById('ts-cf-sidebar-inner');
    if (sidebarInner) {
        document.getElementById('ts-cf-panel-body').innerHTML = sidebarInner.innerHTML;
    }

    function openPanel() {
        panel.classList.add('ts-open');
        overlay.style.display = 'block';
        confirmBtn.style.display = 'block';
        triggerBtn.style.display = 'none';
        applyFilter();
    }
    function closePanel() {
        panel.classList.remove('ts-open');
        overlay.style.display = 'none';
        confirmBtn.style.display = 'none';
        triggerBtn.style.display = 'block';
    }

    triggerBtn.addEventListener('click', openPanel);
    confirmBtn.addEventListener('click', closePanel);
    overlay.addEventListener('click', closePanel);

    // 計算每個選項的商品數量
    function updateCounts() {
        // 服務類型數量
        ['外送茶','定點茶','外送+定點'].forEach(function(v) {
            var c = PRODUCTS.filter(function(p){ return p.stype && p.stype.indexOf(v) > -1; }).length;
            document.querySelectorAll('.ts-cf-opt-count[data-key="stype"][data-val="'+v+'"]').forEach(function(el){ el.textContent = c; });
        });
        // 國籍數量
        PRODUCTS.reduce(function(acc, p){ if(p.nat) acc[p.nat] = (acc[p.nat]||0)+1; return acc; }, {});
        var natCounts = {};
        PRODUCTS.forEach(function(p){ if(p.nat) natCounts[p.nat] = (natCounts[p.nat]||0)+1; });
        Object.keys(natCounts).forEach(function(n) {
            document.querySelectorAll('.ts-cf-opt-count[data-key="nat"][data-val="'+n+'"]').forEach(function(el){ el.textContent = natCounts[n]; });
        });
    }

    // 篩選
    function getFiltered() {
        return PRODUCTS.filter(function(p) {
            if (p.price > 0 && (p.price < filters.priceMin || p.price > filters.priceMax)) return false;
            if (filters.stype.length > 0) {
                if (!filters.stype.some(function(s){ return p.stype && p.stype.indexOf(s) > -1; })) return false;
            }
            if (filters.nat.length > 0 && filters.nat.indexOf(p.nat) === -1) return false;
            if (filters.cup.length > 0 && filters.cup.indexOf(p.cup) === -1) return false;
            if (filters.services.length > 0) {
                if (!filters.services.every(function(s){ return p.services.indexOf(s) > -1; })) return false;
            }
            return true;
        });
    }

    function applyFilter() {
        var filtered = getFiltered();
        countMobile.textContent = filtered.length;

        var productList = document.querySelector('.woocommerce ul.products');
        if (!productList) return;
        productList.querySelectorAll('li.product').forEach(function(item) {
            var found = filtered.some(function(p){ return item.className.indexOf('post-'+p.id) > -1; });
            item.style.display = found ? '' : 'none';
        });

        var total = filters.stype.length + filters.nat.length + filters.cup.length + filters.services.length;
        if (filters.priceMin > MIN_PRICE || filters.priceMax < MAX_PRICE) total++;
        activeCount.textContent = total > 0 ? total + ' 項已選' : '';
    }

    // 綁定所有 option 點擊（sidebar + panel）
    function bindOptions(container) {
        container.querySelectorAll('.ts-cf-option').forEach(function(opt) {
            opt.addEventListener('click', function() {
                var filterKey = this.closest('.ts-cf-options').dataset.filter;
                var val = this.dataset.val;
                var idx = filters[filterKey].indexOf(val);
                if (idx > -1) {
                    filters[filterKey].splice(idx, 1);
                    this.classList.remove('ts-cf-active');
                    // 同步另一個容器
                    document.querySelectorAll('.ts-cf-options[data-filter="'+filterKey+'"] .ts-cf-option[data-val="'+val+'"]').forEach(function(el){ el.classList.remove('ts-cf-active'); });
                } else {
                    filters[filterKey].push(val);
                    document.querySelectorAll('.ts-cf-options[data-filter="'+filterKey+'"] .ts-cf-option[data-val="'+val+'"]').forEach(function(el){ el.classList.add('ts-cf-active'); });
                }
                applyFilter();
                renderTags();
            });
        });
        container.querySelectorAll('.ts-cf-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                var filterKey = this.closest('.ts-cf-chips').dataset.filter;
                var val = this.dataset.val;
                var idx = filters[filterKey].indexOf(val);
                if (idx > -1) {
                    filters[filterKey].splice(idx, 1);
                    document.querySelectorAll('.ts-cf-chips[data-filter="'+filterKey+'"] .ts-cf-chip[data-val="'+val+'"]').forEach(function(el){ el.classList.remove('ts-cf-active'); });
                } else {
                    filters[filterKey].push(val);
                    document.querySelectorAll('.ts-cf-chips[data-filter="'+filterKey+'"] .ts-cf-chip[data-val="'+val+'"]').forEach(function(el){ el.classList.add('ts-cf-active'); });
                }
                applyFilter();
                renderTags();
            });
        });
    }

    bindOptions(document.getElementById('ts-cf-sidebar'));
    bindOptions(document.getElementById('ts-cf-panel-body'));

    // 價格滑桿
    function updateRange() {
        var min = parseInt(rangeMin.value);
        var max = parseInt(rangeMax.value);
        var pMin = (min - MIN_PRICE) / (MAX_PRICE - MIN_PRICE) * 100;
        var pMax = (max - MIN_PRICE) / (MAX_PRICE - MIN_PRICE) * 100;
        rangeFill.style.left  = pMin + '%';
        rangeFill.style.right = (100 - pMax) + '%';
        priceMinEl.textContent = 'NT$' + min.toLocaleString();
        priceMaxEl.textContent = 'NT$' + max.toLocaleString();
        filters.priceMin = min;
        filters.priceMax = max;
        applyFilter();
    }
    rangeMin.addEventListener('input', function(){ if(parseInt(this.value)>parseInt(rangeMax.value)) this.value=rangeMax.value; updateRange(); });
    rangeMax.addEventListener('input', function(){ if(parseInt(this.value)<parseInt(rangeMin.value)) this.value=rangeMin.value; updateRange(); });
    updateRange();

    // 重置
    function doReset() {
        filters = { stype:[], nat:[], cup:[], services:[], priceMin:MIN_PRICE, priceMax:MAX_PRICE };
        document.querySelectorAll('.ts-cf-option, .ts-cf-chip').forEach(function(el){ el.classList.remove('ts-cf-active'); });
        rangeMin.value = MIN_PRICE;
        rangeMax.value = MAX_PRICE;
        updateRange();
        applyFilter();
        renderTags();
    }
    resetMobile.addEventListener('click', doReset);

    function removeTag(key, val) {
        var idx = filters[key].indexOf(val);
        if (idx > -1) filters[key].splice(idx, 1);
        document.querySelectorAll('[data-filter="'+key+'"] [data-val="'+val+'"]').forEach(function(el){ el.classList.remove('ts-cf-active'); });
        applyFilter();
        renderTags();
    }

    function renderTags() {
        var tags = [];
        filters.stype.forEach(function(v)    { tags.push({key:'stype', val:v}); });
        filters.nat.forEach(function(v)      { tags.push({key:'nat', val:v, flag: FLAGS[v]||''}); });
        filters.cup.forEach(function(v)      { tags.push({key:'cup', val:v}); });
        filters.services.forEach(function(v) { tags.push({key:'services', val:v}); });

        resetMobile.style.display = tags.length > 0 ? 'inline-flex' : 'none';

        // 手機面板內標籤
        tagsMobile.innerHTML = tags.map(function(t) {
            return '<span class="ts-cf-tag" data-key="'+t.key+'" data-val="'+t.val+'">× '+(t.flag?t.flag+' ':'')+t.val+'</span>';
        }).join('');
        tagsMobile.querySelectorAll('.ts-cf-tag').forEach(function(el) {
            el.addEventListener('click', function(){ removeTag(this.dataset.key, this.dataset.val); });
        });

        // 面板外標籤
        if (!outsideEl) return;
        if (tags.length === 0) { outsideEl.innerHTML=''; outsideEl.style.display='none'; return; }
        outsideEl.style.display = 'flex';
        var html = '<span id="ts-cf-outside-reset">× 重置</span>';
        html += tags.map(function(t) {
            return '<span class="ts-cf-outside-tag" data-key="'+t.key+'" data-val="'+t.val+'">× '+(t.flag?t.flag+' ':'')+t.val+'</span>';
        }).join('');
        outsideEl.innerHTML = html;
        document.getElementById('ts-cf-outside-reset').addEventListener('click', doReset);
        outsideEl.querySelectorAll('.ts-cf-outside-tag').forEach(function(el) {
            el.addEventListener('click', function(){ removeTag(this.dataset.key, this.dataset.val); });
        });
    }

    updateCounts();
    applyFilter();
})();
</script>

<style>
/* ===== 桌機版 Sidebar ===== */
@media (min-width: 922px) {
    #ts-cf-trigger, #ts-cf-confirm, #ts-cf-overlay, #ts-cf-panel { display: none !important; }

    #ts-cf-wrap { display: block; }

    /* 整體內容置中，左右留白對稱 */
    .woocommerce-shop .ast-container {
        max-width: 1400px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        position: relative !important;
    }

    /* sidebar 固定在容器左側 */
    #ts-cf-sidebar {
        position: fixed;
        top: 80px;
        width: 200px;
        height: calc(100vh - 80px);
        overflow-y: auto;
        background: #0A0118;
        border-right: 0.5px solid rgba(255,138,178,0.2);
        border-bottom: none !important;
        z-index: 100;
        padding-bottom: 40px;
    }

    #ts-cf-sidebar-inner { padding: 20px 16px; }

    /* 商品區往右推 200px */
    .woocommerce-shop #primary {
        padding-left: 220px !important;
    }

    /* 外部條件列 */
    #ts-cf-tags-outside {
        display: none;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
        align-items: center;
    }
}

/* ===== 手機版 ===== */
@media (max-width: 921px) {
    #ts-cf-sidebar { display: none !important; }

    #ts-cf-trigger {
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
        margin-bottom: 8px;
        text-align: center;
        box-sizing: border-box;
    }
    #ts-cf-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.3);
        z-index: 9990;
    }
    #ts-cf-panel {
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
    #ts-cf-panel.ts-open { display: block; }
    #ts-cf-panel-header {
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
    #ts-cf-active-count { font-size: 12px; color: #FF8AB2; }
    #ts-cf-panel-body { padding: 16px 20px; }
    #ts-cf-tags-wrap {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        border-top: 0.5px solid rgba(255,138,178,0.1);
    }
    #ts-cf-reset-mobile {
        display: none;
        align-items: center;
        padding: 4px 12px;
        background: rgba(255,59,131,0.15);
        border: 0.5px solid rgba(255,59,131,0.4);
        border-radius: 999px;
        color: #FF3B83;
        font-size: 12px;
        cursor: pointer;
    }
    #ts-cf-result-bar {
        padding: 12px 20px;
        border-top: 0.5px solid rgba(255,138,178,0.15);
        color: rgba(224,207,255,0.6);
        font-size: 13px;
        letter-spacing: 3px;
        text-align: center;
        font-family: 'Noto Serif TC', serif;
    }
    #ts-cf-count-mobile { color: #FF8AB2; font-size: 15px; }
    #ts-cf-confirm {
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
    #ts-cf-tags-outside {
        display: none;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
        align-items: center;
    }
}

/* ===== 共用樣式 ===== */
.ts-cf-section { margin-bottom: 24px; }
.ts-cf-title {
    color: #FF8AB2;
    font-size: 11px;
    letter-spacing: 4px;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 0.5px solid rgba(255,138,178,0.2);
}

/* Option 列表（服務類型、國籍）*/
.ts-cf-options { display: flex; flex-direction: column; gap: 2px; }
.ts-cf-option {
    display: flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s;
}
.ts-cf-option:hover { background: rgba(200,162,255,0.08); }
.ts-cf-option.ts-cf-active { background: rgba(255,138,178,0.1); }
.ts-cf-flag { font-size: 18px; margin-right: 10px; line-height: 1; }
.ts-cf-opt-name { flex: 1; color: #E0CFFF; font-size: 13px; letter-spacing: 1px; }
.ts-cf-option.ts-cf-active .ts-cf-opt-name { color: #FF8AB2; }
.ts-cf-opt-count {
    color: rgba(224,207,255,0.35);
    font-size: 11px;
    background: rgba(255,255,255,0.05);
    padding: 1px 6px;
    border-radius: 999px;
}

/* Chips（罩杯、服務）*/
.ts-cf-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.ts-cf-chip {
    padding: 5px 12px;
    border-radius: 999px;
    border: 0.5px solid rgba(200,162,255,0.3);
    color: rgba(224,207,255,0.7);
    font-size: 12px;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.15s;
    font-family: 'Noto Sans TC', sans-serif;
}
.ts-cf-chip:hover { border-color: rgba(200,162,255,0.6); color: #E0CFFF; }
.ts-cf-chip.ts-cf-active {
    background: rgba(255,138,178,0.15);
    border-color: #FF8AB2;
    color: #FF8AB2;
}

/* 價格滑桿 */
.ts-cf-price-display {
    color: rgba(224,207,255,0.8);
    font-size: 12px;
    margin-bottom: 14px;
}
.ts-cf-range-wrap {
    position: relative;
    height: 20px;
    margin-bottom: 4px;
}
.ts-cf-range-wrap input[type="range"] {
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
.ts-cf-range-wrap input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #FF8AB2;
    cursor: pointer;
    pointer-events: all;
    border: 2px solid #0A0118;
}
.ts-cf-range-track {
    position: absolute;
    width: 100%;
    height: 4px;
    top: 8px;
    background: rgba(200,162,255,0.15);
    border-radius: 2px;
    z-index: 1;
}
#ts-cf-range-fill {
    position: absolute;
    height: 100%;
    background: linear-gradient(90deg, #C8A2FF, #FF8AB2);
    border-radius: 2px;
}

/* 標籤 */
.ts-cf-tag, .ts-cf-outside-tag {
    padding: 4px 12px;
    background: rgba(255,138,178,0.15);
    border: 0.5px solid rgba(255,138,178,0.4);
    border-radius: 999px;
    color: #FF8AB2;
    font-size: 12px;
    cursor: pointer;
    letter-spacing: 1px;
}
#ts-cf-outside-reset {
    padding: 4px 12px;
    background: rgba(255,59,131,0.15);
    border: 0.5px solid rgba(255,59,131,0.4);
    border-radius: 999px;
    color: #FF3B83;
    font-size: 12px;
    cursor: pointer;
}

/* Sidebar scrollbar */
#ts-cf-sidebar::-webkit-scrollbar { width: 4px; }
#ts-cf-sidebar::-webkit-scrollbar-track { background: transparent; }
#ts-cf-sidebar::-webkit-scrollbar-thumb { background: rgba(255,138,178,0.3); border-radius: 2px; }
</style>
<?php }