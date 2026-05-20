# 天上人間 · 網站建置記憶檔 CLAUDE.md
> 最後更新：2026-05-20

---

## 網站基本資訊
- **網址**：https://demo.megeve168.com
- **後台**：https://demo.megeve168.com/wp-admin
- **佈景主題**：Astra 4.13.1（免費版）
- **頁面編輯器**：Elementor 4.0.3
- **電商系統**：WooCommerce
- **篩選外掛**：HUSKY - Products Filter Professional v1.3.8.1
- **程式碼管理**：Code Snippets（WPCode）
- **自定義欄位**：ACF（Advanced Custom Fields）

---

## 資料庫資訊
- **資料庫名稱**：sql_demo_megeve1
- **資料表前綴**：wp_730c2d_
- **重要資料表**：
  - wp_730c2d_posts（文章/商品）
  - wp_730c2d_postmeta（商品 ACF 欄位）
  - wp_730c2d_terms（分類名稱）
  - wp_730c2d_term_taxonomy（分類結構）
  - wp_730c2d_term_relationships（商品與分類的關聯）

---

## 網站架構

### 主選單（由左至右）
首頁 | 水色雲間 | 水色外送 | 水色定點 | 桃源手記 | 青羚報音 | 紙鳶寄情

### 頁面對應
| 頁面名稱 | URL | 說明 |
|---------|-----|------|
| 首頁 | / | Elementor 編輯，自訂 HTML+CSS 區塊 |
| 水色雲間 | /shop/ | WooCommerce 商店頁，商品列表+HUSKY篩選 |
| 水色外送 | /waiso/ | Elementor 頁面，顯示外送茶商品 |
| 水色定點 | /dingdian/ | Elementor 頁面，顯示定點茶商品 |
| 桃源手記 | /category/taoyuan/ | 文章分類頁 |
| 青羚報音 | /category/news/ | 文章分類頁 |
| 紙鳶寄情 | /about/ | 關於頁面（Elementor） |

---

## 商品（WooCommerce Products）

### 現有商品
| 商品名稱 | Post ID | ACF service_type | 縣市 | product_cat |
|---------|---------|-----------------|------|-------------|
| 月柔 | 205 | 外送+定點 | 高雄市（鼓山區） | ⭐ 外送茶, ⭐ 定點茶, 高雄市, 鼓山區 |
| 嫣然 | 268 | 外送茶 | 台北市（信義區） | ⭐ 外送茶, 台北市, 信義區 |
| 如雪 | 267 | 定點茶 | 台中市（太平區） | ⭐ 定點茶, 台中市, 太平區 |

### 頁面顯示結果
- **水色外送**（/waiso/）：月柔 + 嫣然 ✅
- **水色定點**（/dingdian/）：月柔 + 如雪 ✅
- **首頁水色外送區塊**：嫣然 + 月柔 ✅
- **首頁水色定點區塊**：如雪 + 月柔 ✅

### 商品 ACF 欄位
- `nationality`：國籍
- `age`：年齡
- `height`：身高
- `weight`：體重
- `cup_size`：罩杯
- `service_type`：服務類型（外送茶/定點茶/外送+定點）
- `services`：可配合服務
- `extra_price`：加價購
- `discount`：優惠方案

### ⚠️ 服務類型的資料結構
1. **ACF 欄位** `service_type`：「外送茶」「定點茶」「外送+定點」
2. **WooCommerce Attribute** `pa_service-type`：HUSKY 篩選器用的
3. **商品分類** `product_cat`：slug `waiso`（2140）/ `dingdian`（2141）

### ACF 下拉選項值（已統一為中文）
```
外送茶 : 外送茶
定點茶 : 定點茶
外送+定點 : 外送+定點
```

### 商品分類 term_taxonomy_id
- ⭐ 外送茶：slug `waiso`，**2140**
- ⭐ 定點茶：slug `dingdian`，**2141**
- 台北市：164 / 信義區：171
- 台中市：197 / 太平區：208

---

## 水色外送 / 水色定點 頁面

### ⚠️ Shortcode 格式（必用 category=，不可用 tag=）
```
[products limit="12" category="waiso" columns="3"]
[products limit="12" category="dingdian" columns="3"]
```

---

## Code Snippets 清單（已整理）

| 檔名 | 說明 |
|-----|------|
| 01-match-card.php | 媒合卡片 v3，matches 文章類型卡片顯示 |
| 02-taoyuan-notes.php | 桃源手記頁面功能 |
| 03-explore.php | 踏雲尋境頁面功能 |
| 04-news.php | 青羚報音頁面功能 |
| 05-homepage-shortcode.php | 首頁3個 shortcode + OR meta_query |
| 06-homepage-featured.php | [ts_latest_matches] shortcode |
| 07-shop-city-display.php | 商品卡片顯示縣市（JS前端替換） |
| 08-shop-layout.php | /shop 縣市 banner + CSS |
| 09-shop-city-banner.php | 水色雲間縣市 Banner |

### 重要 Shortcodes
- `[ts_products_by_service type="外送茶" limit="4"]`：首頁外送茶（OR meta_query，含外送+定點）
- `[ts_products_by_service type="定點茶" limit="4"]`：首頁定點茶（OR meta_query，含外送+定點）
- `[ts_city_count]`：縣市商品數量導航
- `[ts_latest_news limit="4"]`：最新消息列表

---

## 全站 CSS

完整整理版：`tianshang-style.css`（共 15 個區塊）
分類版本：`css/` 資料夾（GitHub 管理用）

### CSS 區塊 4：Header 控制邏輯
```css
/* 桌機版（922px以上）：顯示桌機 header，隱藏手機 header */
@media (min-width: 922px) {
    .ast-mobile-header-wrap { display: none !important; }
    .ast-primary-header-bar { display: block !important; }
}

/* 手機版（921px以下）：顯示手機 header */
@media (max-width: 921px) {
    /* ⚠️ 不可加 .ast-primary-header-bar { display: none } */
    /* 漢堡按鈕在 mobile-header-wrap 裡的 primary-header-bar 內 */
    .ast-mobile-header-wrap {
        display: flex !important;
        z-index: 99999 !important;
        min-height: 60px !important;
        align-items: center !important;
    }
}
```

### CSS 區塊 15：手機版 Shop 頁面
```css
@media (max-width: 921px) {
    .ts-shop-hero { display: none !important; }
    #secondary {
        display: none !important;
        order: 1 !important;
        width: 100% !important;
        float: none !important;
        max-width: 100% !important;
    }
    #secondary.ts-open { display: block !important; }
    #primary {
        order: 2 !important;
        width: 100% !important;
        float: none !important;
        max-width: 100% !important;
    }
    body #secondary.ts-open {
        position: fixed !important;
        top: 60px !important;
        left: 0 !important;
        right: 0 !important;
        width: 100vw !important;
        max-width: 100vw !important;
        min-width: 0 !important;
        max-height: 80vh !important;
        overflow-y: auto !important;
        z-index: 9995 !important;
        background: rgba(10, 1, 24, 0.96) !important;
        border-bottom: 1px solid rgba(255, 138, 178, 0.3) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6) !important;
        padding: 16px 20px 32px !important;
        box-sizing: border-box !important;
    }
}
```

---

## 首頁架構

```
區塊① 縣市導航     [ts_city_count]
區塊② 水色外送     [ts_products_by_service type="外送茶" limit="4"]
區塊③ 水色定點     [ts_products_by_service type="定點茶" limit="4"]
區塊⑤ 最新動態     [ts_latest_news limit="4"] + 桃源手記固定連結
區塊⑥ 浮世千帆詩句
區塊⑦ 山中無甲子詩句
```

踏雲尋境 CTA 已移除。

### 首頁 HTML 結構重點
- 商品卡片：`section.ts-section` → `div.ts-product-grid` → `a.ts-product-card`
- **不是** `ul.products li.product`

---

## 已解決的重要問題

1. 商品卡片顯示縣市 → JS 前端替換
2. 商品內頁分類順序 → functions.php filter
3. 縣市分類重複建立 → 使用舊 term_taxonomy_id
4. get_the_terms filter 影響 HUSKY → 改用 JS
5. 浮世千帆跑版 → 補齊 ts-* 樣式
6. 水色外送/定點無商品 → Elementor shortcode 改用 `category=`
7. 外送茶/定點茶分類難找 → 加「⭐」前綴
8. 首頁商品卡片跑版 → 改用 `.ts-section .ts-product-grid`
9. ACF service_type 值不一致 → SQL 更新 + ACF 選項值改中文 + OR meta_query
10. 踏雲尋境從首頁和選單移除 ✅
11. 手機選單可以點擊 → wp_body_open hook 輸出選單 HTML，綁定漢堡按鈕 click 事件
12. 桌機版雙頁首 → 移除 `.ast-primary-header-bar { display: none }` 讓 Astra 自己控制
13. 手機版漢堡按鈕不顯示 → height 只有 1px，加 `min-height: 60px` ✅
14. 手機版漢堡按鈕寬高為 0 → 原因是 CSS 誤加 `.ast-primary-header-bar { display: none }` 把漢堡按鈕壓扁，移除後正常 ✅
15. 手機版漢堡點擊無反應 → JS 選擇器從 `.ast-button-wrap.ast-mobile-menu-buttons` 改為 `button.ast-mobile-menu-trigger-minimal` ✅
16. 水色雲間手機版縣市格子佔版面 → `.ts-shop-hero { display: none }` ✅
17. 水色雲間手機版篩選 bar → 固定按鈕 + `#secondary.ts-open` + `position: fixed` 浮層 ✅
18. HUSKY AJAX 與 sidebar 隱藏衝突 → 不移動 DOM，用 CSS `display:none` + JS toggle `ts-open` class ✅
19. 篩選展開時擠壓商品 → `position: fixed` 讓 sidebar 脫離文件流 ✅
20. 篩選展開時側邊顯示 → JS 用 `style.setProperty('flex-direction', 'column', 'important')` 強制覆蓋 Astra CSS ✅

---

## 手機選單（已解決）

### 解法
- `wp_body_open` hook 輸出 `#ts-mob-overlay` + `#ts-mob-menu`
- JS 綁定 `button.ast-mobile-menu-trigger-minimal` 的 click 事件
- 樣式：從上往下展開（`display: block`），不是側滑
- 程式碼在 **functions.php** 的 `ts_mobile_menu_output` 函數

### ⚠️ 重要：為何用 wp_body_open 而非 wp_footer
- `wp_footer` 輸出的 HTML 被 emoji JS 腳本字串包住，`getElementById` 回傳 null
- `wp_body_open` 在 `<body>` 開頭輸出，DOM 正常

---

## 水色雲間手機版篩選（已解決）

### 解法
- `wp_footer` hook 輸出 `#ts-filter-btn` + `#ts-filter-close-btn` + `#ts-filter-overlay`
- CSS 預設 `#secondary { display: none !important }`
- 點擊篩選按鈕 → `sidebar.classList.add('ts-open')` + overlay 顯示 + 關閉按鈕顯示
- `#secondary.ts-open` 用 `position: fixed` 浮在商品上方
- 關閉按鈕固定在畫面底部中央

### ⚠️ HUSKY 限制
- HUSKY AJAX 篩選需要 `#secondary` 在原本 DOM 位置
- 不可移動 `#secondary` 的 DOM（`appendChild`、`insertBefore` 都會讓商品消失）
- 不可用 `display: none` 在 CSS 隱藏 `.sidebar-main`（HUSKY 初始化需要它可見）
- 正確做法：CSS `display:none` 隱藏整個 `#secondary`，JS 切換 `ts-open` class

### ⚠️ Astra flex-direction 覆蓋問題
- Astra CSS 會把 `.ast-container` 強制設為 `flex-direction: row`
- CSS `!important` 無法覆蓋
- 解法：用 JS `element.style.setProperty('flex-direction', 'column', 'important')`
- 但 `position: fixed` 後 sidebar 脫離文件流，不需要 flex-direction

---

## Astra Header Builder 設定

### 桌機版
- 第二列（主要頁首）：網站標題及標誌 + 主要選單
- 可見度：桌機 ✅ 平板 ✅ 手機 ✅（靠 CSS media query 控制）

### 手機版
- OFF CANVAS：Social
- 第二列：網站標題及標誌 + 切換按鈕
- 可見度：手機 ✅ 桌機 ✅（靠 CSS media query 控制）

### ⚠️ 重要限制
- Astra 免費版的切換按鈕 JS 不觸發
- 漢堡按鈕選擇器：`button.ast-mobile-menu-trigger-minimal`
- 手機 header 元素：`.ast-mobile-header-wrap`（computed display: flex ✅）
- ⚠️ `.ast-primary-header-bar` 存在於 `.ast-mobile-header-wrap` 內部，不可用 CSS 隱藏它

---

## functions.php 自定義內容（最新版）

完整程式碼在 GitHub：`functions.php`

### 三個主要函數
1. `tianshang_auto_short_desc` — 水色雲間商品簡短說明（ACF 欄位轉 HTML）
2. `ts_mobile_menu_output` — 手機選單（wp_body_open hook）
3. `ts_mobile_filter_btn` — 水色雲間手機篩選按鈕（wp_footer hook）

---

## /shop 頁面設定
- WooCommerce 商店頁面設定指向「水色雲間」
- 篩選器用 HUSKY，透過 Astra sidebar 顯示
- 縣市 banner 用 `woocommerce_before_main_content` hook 輸出
- 手機版：縣市 banner 隱藏，改用篩選按鈕浮層

---

## 待辦事項
- [x] 首頁商品卡片置中
- [x] 水色外送頁面商品正確顯示（月柔+嫣然）
- [x] 水色定點頁面商品正確顯示（月柔+如雪）
- [x] 首頁水色外送/定點商品正確顯示（月柔同時出現）
- [x] 踏雲尋境從首頁和選單移除
- [x] 手機選單可以點擊（wp_body_open + 自製下拉選單）
- [x] 桌機版雙頁首移除
- [x] 手機版漢堡按鈕顯示與點擊 ✅
- [x] 水色雲間手機版縣市格子隱藏 ✅
- [x] 水色雲間手機版篩選按鈕（fixed 浮層）✅
- [ ] 篩選 bar 內下拉選單樣式（HUSKY chosen.js 在 fixed 層的渲染）
- [ ] /shop 左側篩選 bar 的 Astra sidebar 設定（桌機版）
- [ ] 首頁縣市數量「-- 位」問題
- [ ] 桃源手記和青羚報音 SEO 維持分開
- [ ] 商品卡片點擊後 URL slug 確認

---

## 常用 SQL 語法模板

```sql
-- 查商品分類關聯
SELECT t.term_id, t.name, t.slug, tt.term_taxonomy_id, tt.parent
FROM wp_730c2d_term_relationships tr
JOIN wp_730c2d_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN wp_730c2d_terms t ON tt.term_id = t.term_id
WHERE tr.object_id = [POST_ID] AND tt.taxonomy = 'product_cat';

-- 新增商品分類關聯
INSERT INTO wp_730c2d_term_relationships (object_id, term_taxonomy_id, term_order)
VALUES ([POST_ID], [TERM_TAXONOMY_ID], 0);

-- 刪除商品分類關聯
DELETE FROM wp_730c2d_term_relationships
WHERE object_id = [POST_ID] AND term_taxonomy_id = [TERM_TAXONOMY_ID];

-- 查 ACF service_type 欄位值
SELECT post_id, meta_key, meta_value
FROM wp_730c2d_postmeta
WHERE meta_key = 'service_type'
AND post_id IN (205, 267, 268);

-- 查某分類下的所有商品
SELECT p.ID, p.post_title
FROM wp_730c2d_posts p
JOIN wp_730c2d_term_relationships tr ON p.ID = tr.object_id
JOIN wp_730c2d_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
JOIN wp_730c2d_terms t ON tt.term_id = t.term_id
WHERE t.slug = 'waiso' AND p.post_type = 'product' AND p.post_status = 'publish';
```

---

## GitHub 檔案結構

```
tianshang-website/
├── CLAUDE.md
├── functions.php
├── tianshang-style.css
├── css/
│   ├── 01-base.css
│   ├── 02-header-mobile.css
│   ├── 03-homepage.css
│   ├── 04-shop.css
│   ├── 05-articles.css
│   └── 06-product-single.css
└── snippets/
    ├── 01-match-card.php
    ├── 02-taoyuan-notes.php
    ├── 03-explore.php
    ├── 04-news.php
    ├── 05-homepage-shortcode.php
    ├── 06-homepage-featured.php
    ├── 07-shop-city-display.php
    ├── 08-shop-layout.php
    └── 09-shop-city-banner.php
```

---

## 每次開新對話貼這些 URL

```
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/CLAUDE.md
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/functions.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/tianshang-style.css
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/01-match-card.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/02-taoyuan-notes.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/03-explore.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/04-news.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/05-homepage-shortcode.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/06-homepage-featured.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/07-shop-city-display.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/08-shop-layout.php
https://raw.githubusercontent.com/dodo997444/tianshang-website/main/snippets/09-shop-city-banner.php
```