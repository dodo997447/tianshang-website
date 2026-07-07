#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
主機A -> 主機B 商品批量匯入腳本 (通用版)
在主機B執行: python3 import_products.py
"""

import re
import subprocess
import datetime
import os

# ============================================================
# 設定區（每次換縣市只需改這裡）
# ============================================================
DB_B = {
    'user': 'waisotw_com',
    'pass': '321944831ee83',
    'db':   'waisotw_com',
    'prefix': 'wp_730c2d_'
}
SSH_A = {
    'host': '34.80.120.192',
    'user': 'mis',
    'pass': 'vu4wj/3@5/3ck6',
}
DB_A = {
    'user': 'sql_vip5678_net',
    'pass': '22f3aa6c6617f',
    'db':   'sql_vip5678_net',
}
WP_UPLOAD_PATH = '/www/wwwroot/waisotw.com/wp-content/uploads'
WP_SITE_URL    = 'https://waisotw.com'
IMG_SRC_DOMAIN = 'https://vip5678.net'

# CITY_CONFIG 由互動式輸入產生，不需要手動設定
CITY_CONFIG = {}

# 縣市名稱 -> (SKU前綴, slug, 標題用名稱, 主機A taxonomy name)
CITY_SKU_MAP = {
    '台北市': ('taipei',    'taipei-city',      '台北定點茶', '台北'),
    '新北市': ('newtaipei', 'new-taipei-city',   '新北定點茶', '新北'),
    '桃園市': ('taoyuan',   'taoyuan-city',      '桃園定點茶', '桃園'),
    '台中市': ('taichung',  'taichung-city',     '台中定點茶', '台中'),
    '台南市': ('tainan',    'tainan-city',       '台南定點茶', '台南'),
    '高雄市': ('kaohsiung', 'kaohsiung-city',    '高雄定點茶', '高雄'),
    '新竹市': ('hsinchu',   'hsinchu-city',      '新竹定點茶', '新竹'),
    '基隆市': ('keelung',   'keelung-city',      '基隆定點茶', '基隆'),
    '苗栗縣': ('miaoli',    'miaoli-county',     '苗栗定點茶', '苗栗'),
    '彰化縣': ('changhua',  'changhua-county',   '彰化定點茶', '彰化'),
    '南投縣': ('nantou',    'nantou-county',     '南投定點茶', '南投'),
    '雲林縣': ('yunlin',    'yunlin-county',     '雲林定點茶', '雲林'),
    '嘉義市': ('chiayi',    'chiayi-city',       '嘉義定點茶', '嘉義'),
    '屏東縣': ('pingtung',  'pingtung-county',   '屏東定點茶', '屏東'),
    '宜蘭縣': ('yilan',     'yilan-county',      '宜蘭定點茶', '宜蘭'),
    '花蓮縣': ('hualien',   'hualien-county',    '花蓮定點茶', '花蓮'),
    '台東縣': ('taitung',   'taitung-county',    '台東定點茶', '台東'),
    '澎湖縣': ('penghu',    'penghu-county',     '澎湖定點茶', '澎湖'),
}

# ============================================================
# taxonomy term_taxonomy_id 對照
# ============================================================
TERM = {
    '泰國':    2116,
    '馬來西亞': 2117,
    '越南':    2142,
    '日本':    2112,
    '台灣':    2111,
    '韓國':    2113,
    '港澳':    2115,
    '其他國家': 2118,
    '定點茶':  2110,
    '外送茶':  2109,
    'simple':  21,
}

ACF = {
    'height':       'field_69fc5c227cff6',
    'weight':       'field_69fc5c3b7cff7',
    'age':          'field_69fc5c0e7cff5',
    'cup_size':     'field_69fc5c4d7cff8',
    'nationality':  'field_69fc5ba07cff4',
    'service_type': 'field_69fc5c8e7cff9',
    'services':     'field_69fc5c9f7cffa',
    'extra_price':  'field_69fc5cb97cffb',
    'discount':     'field_69fc5ccb7cffc',
}

VALID_SERVICES = {
    '2S','69','LG','口爆','變裝','絲襪','可親嘴','親親','舌吻','無套吹','無套做',
    '品鮑','舔蛋','按摩','輕功','奶推','屁推','奶砲','毒龍','共浴','漫遊過水',
    '殘廢澡','泡泡浴','浴中蕭','胸推','足交','漫遊','深喉嚨','冰火','奶炮',
    '玩具(按摩棒)','玩具(跳蛋)','情趣用品(客自備)','情趣用品(需自備全新)',
    '變裝(自備免付)','絲襪(自備免付)','穿絲襪(需自備)','角色扮演(需自備)',
    'LG(舌吻)依氣氛衛生而定','快餐','包夜','2S(雙人)','中文溝通','英文溝通',
    '白人','攝影','無套內射','鴛鴦戲水'
}

SERVICE_MAP = {
    '無套':    '無套做',
    '親嘴':    '可親嘴',
    '艷舞':    '變裝',
    '性感艷舞': '變裝',
    '絲襪誘惑': '絲襪',
    '自衛秀':  '攝影',
    '清槍':    '無套吹',
    '雙飛':    '2S(雙人)',
    '豪邁吃屌': '深喉嚨',
    '水中蕭':  '浴中蕭',
    '兩籠一鳳': '2S(雙人)',
    '主動69':  '69',
    '舔蛋蛋':  '舔蛋',
    '激情舌吻': '舌吻',
    '冰火五重天':'冰火',
    '奶砲':    '奶砲',
    '胸推':    '胸推',
    '屁推':    '屁推',
    '按摩':    '按摩',
}

# PRODUCTS_A 現在由腳本自動從主機A撈取，不需要手動設定

# ============================================================
# 工具函式
# ============================================================

def interactive_setup():
    """互動式設定縣市和筆數"""
    print("\n" + "="*60)
    print("  天上人間 商品批量匯入腳本")
    print("="*60)

    cities = list(CITY_SKU_MAP.keys())
    print("\n可用縣市：")
    for i, c in enumerate(cities):
        print(f"  {i+1:2d}. {c}", end="  ")
        if (i+1) % 4 == 0: print()
    print("\n")

    while True:
        city_input = input("請輸入縣市名稱或序號：").strip()
        if city_input.isdigit():
            idx = int(city_input) - 1
            if 0 <= idx < len(cities):
                city_input = cities[idx]
                print(f"  → {city_input}")
                break
            else:
                print("序號超出範圍")
                continue
        if city_input in CITY_SKU_MAP:
            break
        matches = [c for c in CITY_SKU_MAP if city_input in c]
        if len(matches) == 1:
            city_input = matches[0]
            print(f"  → 自動匹配：{city_input}")
            break
        elif len(matches) > 1:
            print(f"  多個匹配：{matches}")
        else:
            print(f"  找不到「{city_input}」")

    while True:
        limit_input = input("要匯入幾筆？（預設10）：").strip()
        if not limit_input:
            limit = 10
            break
        if limit_input.isdigit() and int(limit_input) > 0:
            limit = int(limit_input)
            break
        print("請輸入正整數")

    sku_prefix, city_slug, city_name_b, city_name_a = CITY_SKU_MAP[city_input]
    pf = DB_B['prefix']
    ttid = mysql_b(f"SELECT tt.term_taxonomy_id FROM {pf}terms t JOIN {pf}term_taxonomy tt ON t.term_id=tt.term_id WHERE tt.taxonomy=\'product_cat\' AND t.name=\'{city_input}\' LIMIT 1")
    if not ttid or not ttid.isdigit():
        print(f"  ❌ 找不到「{city_input}」的 term_taxonomy_id")
        return None

    print(f"\n  縣市：{city_input} | SKU前綴：{sku_prefix} | ttid：{ttid} | 筆數：{limit}")
    confirm = input("確認開始匯入？(y/n)：").strip().lower()
    if confirm != 'y':
        print("取消")
        return None

    return {
        'sku_prefix':   sku_prefix,
        'sku_start':    1,
        'city_slug':    city_slug,
        'city_name':    city_input,
        'city_name_b':  city_name_b,
        'city_name_a':  city_name_a,
        'cat_ttid':     int(ttid),
        'sub_cat_ttid': 0,
        'limit':        limit,
    }

def ssh_mysql_a(sql):
    sql_e = sql.replace("'", "'\\''")
    cmd = (
        f"sshpass -p '{SSH_A['pass']}' ssh -o StrictHostKeyChecking=no "
        f"{SSH_A['user']}@{SSH_A['host']} "
        f"\"echo '{SSH_A['pass']}' | sudo -S mysql -u {DB_A['user']} -p'{DB_A['pass']}' "
        f"{DB_A['db']} --default-character-set=utf8mb4 -se '{sql_e}' 2>/dev/null\""
    )
    r = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return r.stdout.strip()

def mysql_b(sql):
    cmd = ['mysql', f'-u{DB_B["user"]}', f'-p{DB_B["pass"]}', DB_B['db'],
           '--default-character-set=utf8mb4', '-se', sql]
    r = subprocess.run(cmd, capture_output=True, text=True)
    return r.stdout.strip()

def mysql_b_exec(sql):
    """用 stdin pipe 方式執行，保留 session 變數（@pid 等）"""
    cmd = ['mysql', f'-u{DB_B["user"]}', f'-p{DB_B["pass"]}', DB_B['db'],
           '--default-character-set=utf8mb4']
    r = subprocess.run(cmd, input=sql, capture_output=True, text=True)
    if r.returncode != 0:
        print(f"  ❌ SQL錯誤: {r.stderr.strip()[:200]}")
    return r.returncode == 0

def php_ser(lst):
    parts = []
    for i, s in enumerate(lst):
        b = len(s.encode('utf-8'))
        parts.append(f'i:{i};s:{b}:"{s}";')
    return 'a:' + str(len(lst)) + ':{' + ''.join(parts) + '}'

def esc(s):
    """MySQL 字串跳脫"""
    return s.replace('\\', '\\\\').replace("'", "\\'")

def clean_text(s):
    """清除多餘的 \\n 字串和空白行"""
    s = s.replace('\\n', '\n')
    lines = [l.strip() for l in s.split('\n')]
    lines = [l for l in lines if l]
    return '\n'.join(lines)

def parse_title(title):
    info = {'name':'', 'height':'', 'weight':'', 'cup':'', 'age':''}
    m = re.search(r'[:：](\S+)\s+([\d]+)/([\d]+)/([A-Z]+)(?:/([\d]+)Y)?', title)
    if m:
        info['name']   = m.group(1)
        info['height'] = m.group(2)
        info['weight'] = m.group(3)
        info['cup']    = m.group(4)
        info['age']    = m.group(5) or ''
    return info

def parse_excerpt(excerpt):
    result = {
        'nationality':  '泰國',
        'service_type': '⭐ 定點茶',
        'services':     [],
        'extra_price':  '',
        'discount':     '',
    }
    # 去除 HTML tags 和 span 包裝
    clean = re.sub(r'<[^>]+>', '', excerpt)
    clean = re.sub(r'&nbsp;', ' ', clean)
    clean = re.sub(r'\\n', '\n', clean)

    # 國籍
    if '馬來西亞' in clean:
        result['nationality'] = '馬來西亞'
    elif '越南' in clean:
        result['nationality'] = '越南'
    elif '日本' in clean:
        result['nationality'] = '日本'
    elif '台灣' in clean:
        result['nationality'] = '台灣'

    # 服務項目
    svc_raw = ''
    m = re.search(r'服務項目[：:]\s*(.+?)(?:加價項目|加值專區|優惠|價格|其他|$)', clean, re.DOTALL)
    if m:
        svc_raw = m.group(1).strip()
    else:
        for line in clean.split('\n'):
            line = line.strip()
            parts = re.split(r'[/、]', line)
            if len(parts) >= 4 and not re.search(r'\d+分鐘', line) \
               and '泰' not in line and '馬來' not in line \
               and '服務時間' not in line and not re.search(r'\d+Y$', line):
                svc_raw = line
                break

    raw_svcs = re.split(r'[/、，,]', svc_raw)
    services = []
    for s in raw_svcs:
        s = s.strip()
        if not s:
            continue
        if s in VALID_SERVICES:
            services.append(s)
        elif s in SERVICE_MAP and SERVICE_MAP[s] in VALID_SERVICES:
            services.append(SERVICE_MAP[s])
        else:
            for valid in VALID_SERVICES:
                if s in valid or valid in s:
                    services.append(valid)
                    break
    seen = set()
    result['services'] = [x for x in services if not (x in seen or seen.add(x))]

    # 加值專區 / 加價項目
    extra = ''
    m = re.search(r'加[值價]專區[?？💝\s]*\n?(.*?)(?:優惠專案|價格方案|價格：|其他事項|$)', clean, re.DOTALL)
    if not m:
        m = re.search(r'加價項目[：:]\s*(.+?)(?:優惠|價格|其他|$)', clean, re.DOTALL)
    if m:
        ep = m.group(1).strip()
        ep_lines = []
        for l in ep.split('\n'):
            l = l.strip().lstrip('?💝').strip()
            if l and l not in ('', '\\n') and not re.match(r'^[?💝\s]+$', l):
                ep_lines.append(l)
        extra = '\n'.join(ep_lines)
    result['extra_price'] = clean_text(extra)

    # 優惠方案 + 價格
    bonus = ''
    m = re.search(r'優惠專案[：:]\s*([^\n]+)', clean)
    if m:
        bonus = m.group(1).strip()

    price_text = ''
    m2 = re.search(r'價格方案[：:]\s*([^\n]+(?:\n[^\n]+)*?)(?:其他|加價|服務|$)', clean, re.DOTALL)
    if not m2:
        m2 = re.search(r'價格[：:]\s*\n?(.*?)(?:其他|$)', clean, re.DOTALL)
    if m2:
        lines = []
        for l in m2.group(1).strip().split('\n'):
            l = l.strip().lstrip('?💰').strip()
            if l and re.search(r'\d+', l) and '服務' not in l and '加價' not in l:
                # 修正被截斷的價格（補上完整數字）
                l = re.sub(r'(\d+)/2S/(\d+)', r'\1/2S：\2', l)
                lines.append(l)
        price_text = '\n'.join(lines)

    parts = []
    if bonus:
        parts.append(bonus)
    if price_text:
        parts.append(price_text)
    result['discount'] = clean_text('\n'.join(parts))

    return result

def get_img_url(thumb_id):
    """從主機A查圖片URL，自動換成 vip5678.net 域名"""
    guid = ssh_mysql_a(f"SELECT guid FROM wp_posts WHERE ID={thumb_id}")
    if not guid:
        return '', ''
    # 取第一行（避免多行）
    guid = guid.split('\n')[0].strip()
    url = re.sub(r'https?://[^/]+', IMG_SRC_DOMAIN, guid)
    return url, guid

def download_image(img_url, filename):
    """下載圖片，失敗時嘗試縮圖版本"""
    ym = datetime.datetime.now().strftime('%Y/%m')
    dest_dir = f"{WP_UPLOAD_PATH}/{ym}"
    dest_file = f"{dest_dir}/{filename}"
    os.makedirs(dest_dir, exist_ok=True)

    # 先試原圖
    r = subprocess.run(['wget', '-q', '-O', dest_file, img_url], capture_output=True)
    if r.returncode == 0 and os.path.exists(dest_file) and os.path.getsize(dest_file) > 1000:
        return dest_file, ym

    # 失敗：嘗試 -430x669 縮圖
    print(f"    原圖失敗，嘗試縮圖...", end='', flush=True)
    base, ext = os.path.splitext(img_url)
    thumb_url = f"{base}-430x669{ext}"
    r2 = subprocess.run(['wget', '-q', '-O', dest_file, thumb_url], capture_output=True)
    if r2.returncode == 0 and os.path.exists(dest_file) and os.path.getsize(dest_file) > 1000:
        return dest_file, ym

    # 嘗試其他常見縮圖尺寸
    for size in ['-300x450', '-600x900', '-430x645']:
        thumb_url2 = f"{base}{size}{ext}"
        r3 = subprocess.run(['wget', '-q', '-O', dest_file, thumb_url2], capture_output=True)
        if r3.returncode == 0 and os.path.exists(dest_file) and os.path.getsize(dest_file) > 1000:
            return dest_file, ym

    return None, None

def create_attachment(filename, ym, mime='image/jpeg'):
    """建立 attachment，用 guid 查 ID（最可靠）"""
    pf = DB_B['prefix']
    guid = f"{WP_SITE_URL}/wp-content/uploads/{ym}/{filename}"
    post_name = re.sub(r'[^a-z0-9-]', '-', filename.lower()).strip('-')

    # 先確認是否已存在
    existing = mysql_b(f"SELECT ID FROM {pf}posts WHERE guid='{esc(guid)}' LIMIT 1")
    if existing and existing.isdigit():
        print(f"    attachment 已存在 ID={existing}")
        return int(existing)

    ok = mysql_b_exec(f"""SET NAMES utf8mb4;
INSERT INTO {pf}posts
  (post_author,post_date,post_date_gmt,post_content,post_excerpt,post_title,
   post_status,comment_status,ping_status,post_password,post_name,
   to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,
   post_parent,guid,menu_order,post_type,post_mime_type,comment_count)
VALUES (1,NOW(),UTC_TIMESTAMP(),'','','{esc(filename)}',
   'inherit','closed','closed','','{esc(post_name)}',
   '','',NOW(),UTC_TIMESTAMP(),'',
   0,'{esc(guid)}',0,'attachment','{mime}',0);""")
    if not ok:
        return None

    # 用 guid 查 ID
    att_id = mysql_b(f"SELECT ID FROM {pf}posts WHERE guid='{esc(guid)}' AND post_type='attachment' LIMIT 1")
    if not att_id or not att_id.isdigit():
        return None
    att_id = int(att_id)

    mysql_b_exec(f"""SET NAMES utf8mb4;
INSERT IGNORE INTO {pf}postmeta (post_id,meta_key,meta_value) VALUES
({att_id},'_wp_attached_file','{ym}/{filename}'),
({att_id},'_wp_attachment_metadata','a:0:{{}}');""")
    return att_id

def insert_product(p_data, att_id, sku, parsed, info):
    pf = DB_B['prefix']
    # 標題格式：縣市+服務類型-人名（移除身材數字）
    title_raw = p_data['title'].replace(':', '-').replace('：', '-')
    title_b = re.sub(r'\s+[\d]+/[\d]+/[A-Z]+(/[\d]+Y)?$', '', title_raw).strip()
    # 直接用 SKU 當 post_name，避免中文被過濾掉
    post_name = sku

    nat = parsed['nationality']
    nat_ttid   = TERM.get(nat, 2116)
    sort_type_val = p_data.get('sort_type', '定點茶')
    stype_ttid = TERM.get(sort_type_val, TERM['定點茶'])
    price      = p_data['price']
    city_slug  = CITY_CONFIG['city_slug']
    city_name  = CITY_CONFIG['city_name_b']
    # 根據 sort_type 動態調整標題和城市名稱
    sort_type_val = p_data.get('sort_type', '定點茶')
    city_name_display = city_name.replace('定點茶', sort_type_val)

    name_display = info['name']
    size_display = f"{info['height']}/{info['weight']}/{info['cup']}"
    if info['age']:
        size_display += f"/{info['age']}Y"

    post_content = (
        f'<h3 style="text-align: center">定點茶 {name_display} {size_display}</h3>\n'
        f'<h3 style="text-align: center"><strong>{nat}{sort_type_val} '
        f'<a href="{WP_SITE_URL}/shop/swoof/product_cat-{city_slug}/">{city_name_display}</a>'
        f'</strong></h3>\n'
        f'<h1 style="text-align: center"><a href="https://line.me/ti/p/B7JFAhdPTs">'
        f'<span style="color: #ff0000"><strong>➜ 約妹私訊客服人員</strong></span></a></h1>\n'
        f'&nbsp;\n'
        f'<h3>定點茶服務流程</h3>'
        f'<p>1. 確認預約時間，安排妹妹檔期，確保服務品質。</p>'
        f'<p>2. 前往雙方約定地點。</p>'
        f'<p>3. 抵達後聯繫客服確認詳細房號或地址。</p>'
        f'<p>4. 見面確認雙方滿意後，將費用交予妹妹，即可開始享受服務。</p>'
        f'<h3>外送茶常見問題</h3>'
        f'<h4>外送茶旅館由誰負責訂？</h4>'
        f'<p>旅館由客人事先開好房間，並將房號告知我方，同時知會前台有訪客來訪，便於妹妹順利入房，保障雙方隱私。</p>'
        f'<h4>只預約一節，可以現場加節嗎？</h4>'
        f'<p>可以。妹妹尚未離開前，隨時可向我方提出加節需求，將費用直接交給妹妹即可，無需另行安排。</p>'
        f'<h4>可以私下加妹妹聯絡方式嗎？</h4>'
        f'<p>恕無法提供。透過平台統一安排，能確保雙方安全與權益。如私下聯繫發生任何糾紛，平台將無法介入處理。</p>'
        f'<h4>若見面後對人選不滿意，可以更換嗎？</h4>'
        f'<p>可以。如對安排人選有所疑慮，可立即向我方反映，我們將協助重新安排。每日最多可更換三次，超過次數後，當日將無法再行調度。</p>'
    )

    prod_attrs = 'a:5:{s:14:"pa_nationality";a:6:{s:4:"name";s:14:"pa_nationality";s:5:"value";s:0:"";s:8:"position";i:0;s:10:"is_visible";i:1;s:12:"is_variation";i:0;s:11:"is_taxonomy";i:1;}s:15:"pa_service-type";a:6:{s:4:"name";s:15:"pa_service-type";s:5:"value";s:0:"";s:8:"position";i:1;s:10:"is_visible";i:1;s:12:"is_variation";i:0;s:11:"is_taxonomy";i:1;}s:11:"pa_cup-size";a:6:{s:4:"name";s:11:"pa_cup-size";s:5:"value";s:0:"";s:8:"position";i:2;s:10:"is_visible";i:1;s:12:"is_variation";i:0;s:11:"is_taxonomy";i:1;}s:6:"pa_age";a:6:{s:4:"name";s:6:"pa_age";s:5:"value";s:0:"";s:8:"position";i:3;s:10:"is_visible";i:1;s:12:"is_variation";i:0;s:11:"is_taxonomy";i:1;}s:18:"pa_service-content";a:6:{s:4:"name";s:18:"pa_service-content";s:5:"value";s:0:"";s:8:"position";i:4;s:10:"is_visible";i:1;s:12:"is_variation";i:0;s:11:"is_taxonomy";i:1;}}'

    services_ser = esc(php_ser(parsed['services']))
    extra_esc    = esc(parsed['extra_price'])
    discount_esc = esc(parsed['discount'])
    content_esc  = esc(post_content)
    title_esc    = esc(title_b)
    nat_esc      = esc(nat)
    stype_esc    = esc(parsed['service_type'])
    p_data_a_sku = esc(p_data.get('a_sku', ''))
    p_data_thumb = esc(str(p_data.get('thumb_id', '')))

    # 用 post_name 查 ID（最可靠）
    pf = DB_B['prefix']

    # 計算 term_relationships
    cat_ttid = CITY_CONFIG['cat_ttid']
    sub_ttid = CITY_CONFIG['sub_cat_ttid']
    term_values = f"(@pid,{cat_ttid},0),(@pid,{nat_ttid},0),(@pid,{stype_ttid},0),(@pid,21,0)"
    term_update  = f"{cat_ttid},{nat_ttid},{stype_ttid},21"
    if sub_ttid:
        term_values = f"(@pid,{cat_ttid},0),(@pid,{sub_ttid},0),(@pid,{nat_ttid},0),(@pid,{stype_ttid},0),(@pid,21,0)"
        term_update  = f"{cat_ttid},{sub_ttid},{nat_ttid},{stype_ttid},21"

    sql = f"""
SET NAMES utf8mb4;
INSERT INTO {pf}posts
  (post_author,post_date,post_date_gmt,post_content,post_excerpt,post_title,
   post_status,comment_status,ping_status,post_password,post_name,
   to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,
   post_parent,guid,menu_order,post_type,post_mime_type,comment_count)
VALUES (1,NOW(),UTC_TIMESTAMP(),'{content_esc}','','{title_esc}',
   'publish','closed','closed','','{esc(post_name)}',
   '','',NOW(),UTC_TIMESTAMP(),'',
   0,'{WP_SITE_URL}/?post_type=product&p=NEW',0,'product','',0);
SET @pid=LAST_INSERT_ID();
INSERT INTO {pf}postmeta (post_id,meta_key,meta_value) VALUES
(@pid,'_sku','{sku}'),
(@pid,'_price','{price}'),
(@pid,'_regular_price','{price}'),
(@pid,'_manage_stock','no'),
(@pid,'_stock_status','instock'),
(@pid,'_backorders','no'),
(@pid,'_sold_individually','no'),
(@pid,'_virtual','no'),
(@pid,'_downloadable','no'),
(@pid,'_tax_status','taxable'),
(@pid,'_tax_class',''),
(@pid,'_download_limit','-1'),
(@pid,'_download_expiry','-1'),
(@pid,'_wc_average_rating','0'),
(@pid,'_wc_review_count','0'),
(@pid,'total_sales','0'),
(@pid,'_product_version','9.1.2'),
(@pid,'_a_source_sku','{p_data_a_sku}'),
(@pid,'_a_source_thumb','{p_data_thumb}'),
(@pid,'_thumbnail_id','{att_id}'),
(@pid,'_product_attributes','{prod_attrs}'),
(@pid,'height','{info["height"]}'),(@pid,'_height','{ACF["height"]}'),
(@pid,'weight','{info["weight"]}'),(@pid,'_weight','{ACF["weight"]}'),
(@pid,'age','{info["age"]}'),(@pid,'_age','{ACF["age"]}'),
(@pid,'cup_size','{info["cup"]}'),(@pid,'_cup_size','{ACF["cup_size"]}'),
(@pid,'nationality','{nat_esc}'),(@pid,'_nationality','{ACF["nationality"]}'),
(@pid,'service_type','{stype_esc}'),(@pid,'_service_type','{ACF["service_type"]}'),
(@pid,'services','{services_ser}'),(@pid,'_services','{ACF["services"]}'),
(@pid,'extra_price','{extra_esc}'),(@pid,'_extra_price','{ACF["extra_price"]}'),
(@pid,'discount','{discount_esc}'),(@pid,'_discount','{ACF["discount"]}');
INSERT INTO {pf}term_relationships (object_id,term_taxonomy_id,term_order) VALUES
{term_values};
UPDATE {pf}term_taxonomy SET count=count+1
WHERE term_taxonomy_id IN ({term_update});
UPDATE {pf}posts SET post_parent=@pid WHERE ID={att_id};
INSERT INTO {pf}wc_product_meta_lookup
  (product_id,sku,virtual,downloadable,min_price,max_price,onsale,stock_quantity,
   stock_status,rating_count,average_rating,total_sales,tax_status,tax_class)
VALUES (@pid,'{sku}',0,0,{price},{price},0,NULL,'instock',0,0,0,'taxable','');
"""
    mysql_b_exec(sql)

    # 用 post_name + sku 查 ID
    new_id = mysql_b(f"SELECT p.ID FROM {pf}posts p JOIN {pf}postmeta pm ON p.ID=pm.post_id WHERE p.post_type='product' AND pm.meta_key='_sku' AND pm.meta_value='{sku}' LIMIT 1")
    return int(new_id) if new_id and new_id.isdigit() else None

# ============================================================
# 主程式
# ============================================================
def get_imported_a_skus():
    """查主機B已匯入的主機A原始SKU和縮圖ID"""
    pf = DB_B['prefix']
    # 已匯入的 a_source_sku
    result_sku = mysql_b(f"SELECT meta_value FROM {pf}postmeta WHERE meta_key='_a_source_sku'")
    skus = set(result_sku.strip().split('\n')) if result_sku else set()

    # 已匯入的 a_source_thumb（主機A的 thumb_id）
    result_thumb = mysql_b(f"SELECT meta_value FROM {pf}postmeta WHERE meta_key='_a_source_thumb'")
    thumbs = set(result_thumb.strip().split('\n')) if result_thumb else set()

    return skus, thumbs

def fetch_products_from_a(city_name_a, limit=10):
    """從主機A自動撈取指定縣市的商品清單，跳過已匯入的，確保湊足 limit 筆"""
    print(f"從主機A查詢「{city_name_a}」商品...")

    # 查主機B已有哪些主機A的SKU和縮圖（避免重複）
    imported_skus, imported_thumbs = get_imported_a_skus()
    print(f"  主機B已匯入 {len(imported_skus)} 筆 SKU，{len(imported_thumbs)} 筆圖片（將跳過重複）")

    batch_size = limit * 3  # 每次多撈，避免全被跳過
    offset = 0
    collected = []

    while len(collected) < limit:
        sql = f"""
SELECT p.ID, p.post_title,
  MAX(CASE WHEN pm.meta_key='_thumbnail_id' THEN pm.meta_value END) AS thumb_id,
  MAX(CASE WHEN pm.meta_key='_price' THEN pm.meta_value END) AS price,
  MAX(CASE WHEN pm.meta_key='_sku' THEN pm.meta_value END) AS sku,
  MAX(CASE WHEN tt2.taxonomy='pa_sort' THEN t2.name END) AS sort_type
FROM wp_posts p
JOIN wp_term_relationships tr ON p.ID=tr.object_id
JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id=tt.term_taxonomy_id
JOIN wp_terms t ON tt.term_id=t.term_id
JOIN wp_postmeta pm ON p.ID=pm.post_id
LEFT JOIN wp_term_relationships tr2 ON p.ID=tr2.object_id
LEFT JOIN wp_term_taxonomy tt2 ON tr2.term_taxonomy_id=tt2.term_taxonomy_id AND tt2.taxonomy='pa_sort'
LEFT JOIN wp_terms t2 ON tt2.term_id=t2.term_id
WHERE p.post_type='product' AND p.post_status='publish'
AND t.name='{city_name_a}' AND tt.taxonomy='product_cat'
AND pm.meta_key IN ('_thumbnail_id','_price','_sku')
GROUP BY p.ID
ORDER BY p.ID
LIMIT {batch_size} OFFSET {offset}
"""
        result = ssh_mysql_a(sql)
        if not result or not result.strip():
            print(f"  主機A已無更多商品（總共撈到 {len(collected)} 筆）")
            break

        batch = []
        for line in result.strip().split('\n'):
            parts = line.strip().split('\t')
            if len(parts) >= 5:
                try:
                    a_sku = parts[4]
                    a_thumb = parts[2]
                    if a_sku in imported_skus:
                        print(f"  ⏭  跳過已匯入(SKU): {parts[1][:30]} ({a_sku})")
                        continue
                    if a_thumb in imported_thumbs:
                        print(f"  ⏭  跳過已匯入(圖片相同): {parts[1][:30]} (thumb={a_thumb})")
                        continue
                    sort_type = parts[5] if len(parts) > 5 else '定點茶'
                    batch.append({
                        'id':       int(parts[0]),
                        'title':    parts[1],
                        'thumb_id': int(parts[2]) if parts[2] else 0,
                        'price':    int(parts[3]) if parts[3] else 0,
                        'a_sku':    a_sku,
                        'sort_type': sort_type if sort_type else '定點茶',
                    })
                except:
                    pass

        needed = limit - len(collected)
        collected.extend(batch[:needed])

        if len(batch) < batch_size:
            # 主機A這批已經撈完
            break

        offset += batch_size

    print(f"  準備匯入 {len(collected)} 筆")
    return collected

def get_next_sku_number(prefix):
    """查 DB 現有最大 SKU 號碼，回傳下一個號碼"""
    pf = DB_B['prefix']
    result = mysql_b(f"""
SELECT meta_value FROM {pf}postmeta 
WHERE meta_key='_sku' AND meta_value REGEXP '^{prefix}[0-9]+$'
ORDER BY CAST(SUBSTRING(meta_value, {len(prefix)+1}) AS UNSIGNED) DESC
LIMIT 1
""")
    if result and result != 'NULL':
        m = re.search(r'\d+$', result)
        if m:
            return int(m.group()) + 1
    return CITY_CONFIG['sku_start']

def main():
    r = subprocess.run(['which', 'sshpass'], capture_output=True)
    if r.returncode != 0:
        print("安裝 sshpass...")
        subprocess.run(['yum', 'install', '-y', 'sshpass'], capture_output=True)

    # 互動式設定
    config = interactive_setup()
    if not config:
        return
    CITY_CONFIG.update(config)

    # 自動查現有最大 SKU，從下一個開始
    prefix = CITY_CONFIG['sku_prefix']
    sku_counter = get_next_sku_number(prefix)
    print(f"SKU 從 {prefix}{sku_counter:03d} 開始")
    success = 0
    failed  = []

    # 從主機A自動撈取商品清單
    products = fetch_products_from_a(CITY_CONFIG["city_name_a"], CITY_CONFIG["limit"])
    if not products:
        print("沒有商品可以匯入")
        return

    for p_data in products:
        if p_data.get('skip'):
            print(f"⏭  跳過 {p_data['title']}")
            continue

        title = p_data['title']
        print(f"\n{'='*60}")
        print(f"處理: {title}")

        info = parse_title(title)
        print(f"  標題解析: {info['name']} {info['height']}/{info['weight']}/{info['cup']}" +
              (f"/{info['age']}Y" if info['age'] else ''))

        # 查 post_excerpt
        excerpt = ssh_mysql_a(f"SELECT post_excerpt FROM wp_posts WHERE ID={p_data['id']}")
        if not excerpt:
            print(f"  ❌ 無法取得 post_excerpt，跳過")
            failed.append(title)
            continue
        parsed = parse_excerpt(excerpt)
        # 用主機A的 pa_sort 覆蓋 service_type
        sort = p_data.get('sort_type', '定點茶')
        parsed['service_type'] = '⭐ ' + sort
        print(f"  國籍: {parsed['nationality']} | 服務({len(parsed['services'])}): {parsed['services'][:5]}{'...' if len(parsed['services'])>5 else ''}")
        print(f"  加值: {parsed['extra_price'][:50]}")
        print(f"  優惠: {parsed['discount'][:50]}")

        # 查圖片
        img_url, orig_guid = get_img_url(p_data['thumb_id'])
        if not img_url:
            print(f"  ❌ 找不到圖片，跳過")
            failed.append(title)
            continue

        # 下載圖片
        ext = img_url.rsplit('.', 1)[-1].split('?')[0].lower()
        if ext not in ('jpg','jpeg','png','webp','gif'):
            ext = 'jpg'
        sku = f"{CITY_CONFIG['sku_prefix']}{sku_counter:03d}"
        filename = f"{sku}.{ext}"
        mime = 'image/jpeg' if ext in ('jpg','jpeg') else f'image/{ext}'

        print(f"  下載 {filename} ...", end='', flush=True)
        dest, ym = download_image(img_url, filename)
        if not dest:
            print(f" ❌ 下載失敗，跳過")
            failed.append(title)
            continue
        print(f" ✅")

        # 建立 attachment
        att_id = create_attachment(filename, ym, mime)
        if not att_id:
            print(f"  ❌ 建立 attachment 失敗，跳過")
            failed.append(title)
            continue
        print(f"  attachment_id: {att_id}")

        # 插入商品
        new_id = insert_product(p_data, att_id, sku, parsed, info)
        if new_id:
            print(f"  ✅ 完成 ID={new_id} SKU={sku}")
            success += 1
            sku_counter += 1
        else:
            print(f"  ❌ 插入商品失敗")
            failed.append(title)

    # 清除首頁城市數量快取
    mysql_b_exec("""
DELETE FROM wp_730c2d_options
WHERE option_name IN (
  '_transient_ts_city_count_cache',
  '_transient_timeout_ts_city_count_cache'
);
""")
    print("  🧹 已清除城市數量快取")

    print(f"\n{'='*60}")
    print(f"✅ 完成！成功 {success} 筆")
    if failed:
        print(f"❌ 失敗 {len(failed)} 筆:")
        for f in failed:
            print(f"   - {f}")

if __name__ == '__main__':
    main()
