 
(function () {
    'use strict';

    /* Tema: gelap (default) atau terang, diingat di browser */
    var THEME_KEY = 'ecr-theme';
    function getTheme() { try { return localStorage.getItem(THEME_KEY) || 'dark'; } catch (e) { return 'dark'; } }
    function setTheme(t) { document.documentElement.setAttribute('data-theme', t); try { localStorage.setItem(THEME_KEY, t); } catch (e) {} }
    document.documentElement.setAttribute('data-theme', getTheme());

    var ICON = {
        home: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/>',
        car: '<path d="M5 16v-5l2-5h10l2 5v5"/><path d="M3 16h18v3H3z"/><path d="M7.5 13h.01M16.5 13h.01"/>',
        users: '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 5a3 3 0 0 1 0 6M18 14c2 .6 3 2.5 3 6"/>',
        receipt: '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
        shield: '<path d="M12 3l8 3v6c0 4.5-3.3 8-8 9-4.7-1-8-4.5-8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        key: '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3"/>',
        plus: '<path d="M12 5v14M5 12h14"/>',
        wallet: '<path d="M4 7h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4z"/><path d="M4 7V6a2 2 0 0 1 2-2h11"/><path d="M16 13.5h.01"/>',
        user: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
        chart: '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
        history: '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        logout: '<path d="M9 4H5v16h4"/><path d="m16 8 4 4-4 4M20 12H9"/>',
        menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
        sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        moon: '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
        chev: '<path d="m6 9 6 6 6-6"/>',
        search: '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4-4"/>'
    };
    function svg(name) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + ICON[name] + '</svg>';
    }

    var ROLE_LABEL = { admin: 'Admin', owner: 'Mitra Pemilik', customer: 'Pelanggan' };
    var ROLE_SUB = { admin: 'Ruang kendali', owner: 'Portal mitra', customer: 'Sewa mobil' };

    function menus(role, pub) {
        var d = pub + 'dashboard/', m = pub + 'mobil/';
        if (role === 'admin') return [
            { t: 'Pelanggan', i: 'users', h: d + 'dashboard.php?menu=pelanggan' },
            { t: 'Verifikasi', i: 'shield', h: d + 'dashboard.php?menu=pengajuan' },
            { t: 'Pemilik mobil', i: 'key', h: d + 'dashboard.php?menu=pemilik' },
            { t: 'Armada', i: 'car', h: d + 'dashboard.php?menu=mobil' },
            { t: 'Pengajuan mobil', i: 'plus', h: d + 'dashboard.php?menu=persetujuan' },
            { t: 'Transaksi', i: 'receipt', h: d + 'dashboard.php?menu=rental' },
            { g: 'Input cepat' },
            { t: 'Mobil baru', i: 'plus', h: m + 'view/tambah.php' },
            { t: 'Pelanggan baru', i: 'plus', h: m + 'aset/pelanggan.php' },
            { t: 'Pemilik baru', i: 'plus', h: m + 'aset/pemilik.php' }
        ];
        if (role === 'owner') return [
            { t: 'Ringkasan', i: 'home', h: d + 'owner.php' },
            { t: 'Armada saya', i: 'car', h: d + 'owner_armada.php' },
            { t: 'Transaksi', i: 'receipt', h: d + 'owner_transaksi.php' },
            { t: 'Laporan', i: 'chart', h: d + 'owner_laporan.php' },
            { t: 'Profil mitra', i: 'user', h: d + 'owner_profil.php' }
        ];
        return [
            { t: 'Beranda', i: 'home', h: d + 'user_dashboard.php' },
            { t: 'Armada', i: 'car', h: d + 'armada.php' },
            { t: 'Riwayat', i: 'history', h: d + 'riwayat.php' },
            { t: 'Isi saldo', i: 'wallet', h: d + 'isi_saldo.php' },
            { t: 'Profil', i: 'user', h: d + 'profil.php' }
        ];
    }

    function isActive(href) {
        var a = new URL(href, location.href), c = location.href && new URL(location.href);
        if (a.pathname !== c.pathname) return false;
        var am = a.searchParams.get('menu'), cm = c.searchParams.get('menu');
        return am === null || am === (cm || 'pelanggan');
    }

    function linkHtml(x) {
        return '<a href="' + x.h + '"' + (isActive(x.h) ? ' class="is-active" aria-current="page"' : '') + '>' + svg(x.i) + '<span>' + x.t + '</span></a>';
    }
    function navHtml(items) {
        var out = '', more = null;
        items.forEach(function (x) {
            if (x.g) { more = { g: x.g, list: [] }; return; }
            if (more) more.list.push(x); else out += linkHtml(x);
        });
        if (more) out += '<details class="nav__more"><summary>' + svg('plus') + '<span>' + more.g + '</span>' + svg('chev') + '</summary><div class="nav__menu">' + more.list.map(linkHtml).join('') + '</div></details>';
        return out;
    }

    function markHtml(pub) { return '<img class="brand__logo" src="' + pub + 'assets/logo.svg" alt="">'; }
    var WM = '<b class="wm">eCar<i>-Rent</i></b>';

    /* ---------- Merapikan isi halaman ---------- */
    function stripPipes(root) {
        root.querySelectorAll('nav, td').forEach(function (el) {
            Array.prototype.slice.call(el.childNodes).forEach(function (n) {
                if (n.nodeType === 3 && /^\s*\|\s*$/.test(n.nodeValue)) n.parentNode.removeChild(n);
            });
        });
    }

    function actionKind(t) {
        t = t.toLowerCase();
        if (/hapus|delete|batal|tolak/.test(t)) return 'danger';
        if (/edit|ubah/.test(t)) return 'edit';
        if (/verifikasi|proses|selesai|setuju/.test(t)) return 'ok';
        return 'view';
    }

    function decorateActions(root) {
        root.querySelectorAll('td').forEach(function (td) {
            var links = td.querySelectorAll(':scope > a');
            if (!links.length) return;
            var rest = Array.prototype.slice.call(td.childNodes).filter(function (n) {
                return !(n.nodeType === 1 && n.tagName === 'A') && !(n.nodeType === 3 && !n.nodeValue.trim());
            });
            if (rest.length) return;
            var box = document.createElement('div');
            box.className = 'actions';
            links.forEach(function (a) {
                a.classList.add('act', 'act--' + actionKind(a.textContent));
                box.appendChild(a);
            });
            td.innerHTML = '';
            td.appendChild(box);
        });
    }

    var STATUS = [
        [/^(tersedia|disetujui|terverifikasi|selesai|dikembalikan|aktif|lunas|approved|baik)$/i, 'ok'],
        [/^(pending|belum verifikasi|belum terverifikasi|menunggu|diproses|proses|menunggu persetujuan)$/i, 'warn'],
        [/^(ditolak|dibatalkan|batal|blacklist|rejected|rusak)$/i, 'bad'],
        [/^(disewa|di sewa|dipinjam)$/i, 'info']
    ];
    function badgeKind(t) {
        t = t.trim();
        for (var i = 0; i < STATUS.length; i++) if (STATUS[i][0].test(t)) return STATUS[i][1];
        return null;
    }
    function decorateBadges(root) {
        root.querySelectorAll('td, span.status').forEach(function (el) {
            if (el.children.length && !el.matches('span.status')) return;
            if (el.querySelector('.badge')) return;
            var k = badgeKind(el.textContent);
            if (!k || el.textContent.length > 28) return;
            var b = document.createElement('span');
            b.className = 'badge badge--' + k;
            b.textContent = el.textContent.trim();
            if (el.matches('span.status')) { el.className = 'badge badge--' + k; return; }
            el.textContent = '';
            el.appendChild(b);
        });
    }

    function wrapTables(root) {
        root.querySelectorAll('table').forEach(function (t) {
            if (t.closest('.table-wrap')) return;
            if (!t.querySelector('th')) t.classList.add('kv');
            var w = document.createElement('div');
            w.className = 'table-wrap';
            t.parentNode.insertBefore(w, t);
            w.appendChild(t);
        });
    }

    function decorateAlerts(root) {
        root.querySelectorAll('p').forEach(function (p) {
            if (p.className || p.children.length !== 1 || !/^(B|STRONG)$/.test(p.children[0].tagName)) return;
            var t = p.textContent.trim();
            if (t.length > 220 || p.textContent.trim() !== p.children[0].textContent.trim()) return;
            if (!/berhasil|gagal|sudah|salah|belum|ditolak|error|sukses|tidak|lengkapi|valid|terdaftar|cukup/i.test(t)) return;
            p.classList.add('alert', /berhasil|sukses/i.test(t) ? 'alert--ok' : 'alert--bad');
        });
    }

    function passwordToggle(root) {
        root.querySelectorAll('input[type="password"]').forEach(function (i) {
            if (i.parentNode.classList.contains('pw')) return;
            var w = document.createElement('div');
            w.className = 'pw';
            i.parentNode.insertBefore(w, i);
            w.appendChild(i);
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = 'Lihat';
            b.setAttribute('aria-label', 'Tampilkan atau sembunyikan password');
            b.addEventListener('click', function () {
                var show = i.type === 'password';
                i.type = show ? 'text' : 'password';
                b.textContent = show ? 'Sembunyi' : 'Lihat';
            });
            w.appendChild(b);
        });
    }

    /* ---------- Shell ---------- */
    function buildShell(role) {
        var body = document.body, pub = body.dataset.pub || '../';
        var user = body.dataset.user || '', logout = pub + '../logout.php';
        var content = document.createElement('main');
        content.className = 'content';

        Array.prototype.slice.call(body.childNodes).forEach(function (n) {
            if (n.nodeType === 1 && /^(SCRIPT|STYLE)$/.test(n.tagName)) return;
            if (n.nodeType === 1 && n.tagName === 'HEADER') return n.remove();
            if (n.nodeType === 1 && n.tagName === 'NAV' && n.querySelector('a[href*="logout"]')) return n.remove();
            if (n.nodeType === 1 && n.tagName === 'MAIN' && !n.children.length) return n.remove();
            if (n.nodeType === 1 && n.tagName === 'MAIN') { while (n.firstChild) content.appendChild(n.firstChild); return n.remove(); }
            content.appendChild(n);
        });
        var first = content.firstElementChild;
        while (first && first.tagName === 'HR') { first.remove(); first = content.firstElementChild; }

        var items = menus(role, pub), me = '<div class="me"><span class="me__av">' + (user.charAt(0) || '?').toUpperCase() + '</span><span><b>' + (user || ROLE_LABEL[role]) + '</b><small>' + ROLE_LABEL[role] + '</small></span></div>';
        var brand = '<a class="brand" href="' + items.filter(function (x) { return x.h; })[0].h + '">' + markHtml(pub) + '<span>' + WM + '<small>' + ROLE_SUB[role] + '</small></span></a>';
        var app = document.createElement('div');
        var foot = '<footer class="foot">eCar-Rent — dari kunci sampai kembali, semua tercatat rapi.</footer>';

        var themeBtn = '<button type="button" class="theme-btn" data-theme-toggle aria-label="Ganti tema terang/gelap">' + svg(getTheme() === 'light' ? 'moon' : 'sun') + '</button>';
        app.className = 'app app--top';
        var cmdBtn = '<button type="button" class="cmdk-btn" data-palette aria-label="Pencarian cepat">' + svg('search') + '<span>Cari menu</span><kbd>Ctrl K</kbd></button>';
        window.__ecr = { role: role, items: items, svg: svg, logout: logout, isActive: isActive };
        app.innerHTML = '<header class="pill glass">' + brand + '<nav class="nav" aria-label="Menu utama">' + navHtml(items) + '</nav><div class="pill__end">' + cmdBtn + themeBtn + me + '<a class="btn ghost" href="' + logout + '">Keluar</a></div></header>';
        app.appendChild(content);
        content.insertAdjacentHTML('beforeend', foot);
        body.insertBefore(app, body.firstChild);
        return content;
    }

    function buildPaper() {
        var body = document.body, paper = document.createElement('div');
        paper.className = 'paper';
        Array.prototype.slice.call(body.childNodes).forEach(function (n) {
            if (n.nodeType === 1 && /^(SCRIPT|STYLE)$/.test(n.tagName)) return;
            paper.appendChild(n);
        });
        body.insertBefore(paper, body.firstChild);
        return paper;
    }


    /* Tautan "Kembali"/"Home" jadi tombol sekunder supaya jelas bisa diklik */
    function decorateBackLinks(root) {
        root.querySelectorAll('a').forEach(function (a) {
            if (a.className || a.closest('nav, td, .actions, .side, .pill, .brand, .stat, .auth__alt')) return;
            if (!/^\s*(←\s*)?(kembali|home)\b/i.test(a.textContent)) return;
            a.classList.add('btn', 'secondary');
            var p = a.parentNode;
            if (p.tagName === 'P' && p.children.length === 1 && p.textContent.trim() === a.textContent.trim()) p.style.margin = '0 0 18px';
        });
    }

    /* ---------- Elemen 3D & dekorasi ---------- */
    var BODY = 'M56 170C52 150 62 140 86 135L140 126C168 98 206 80 252 78L318 78C352 80 378 98 402 120L448 130C468 134 476 146 476 166L476 172C476 176 472 178 468 178L420 178A34 34 0 0 0 352 178L170 178A34 34 0 0 0 102 178L64 178C59 178 56 175 56 172Z';
    function wheel(cx) {
        return '<g transform="translate(' + cx + ' 178)"><circle class="sc-tire" r="29"/><g class="sc-spin"><circle class="sc-rim" r="19"/>' +
            '<path class="sc-spoke" d="M0 0V-18M0 0L17.1 -5.6M0 0L10.6 14.6M0 0L-10.6 14.6M0 0L-17.1 -5.6"/><circle class="sc-bolt" r="4"/></g></g>';
    }
    var SCENE = '<div class="scene" aria-hidden="true"><div class="scene__pad"><i></i><i></i><i></i></div>' +
        '<span class="scene__tag scene__tag--1">Siap jalan</span><span class="scene__tag scene__tag--2">Akun terverifikasi</span><span class="scene__tag scene__tag--3">Nota digital</span>' +
        '<div class="scene__car"><div class="scene__float"><svg viewBox="0 0 520 230"><defs>' +
        '<linearGradient id="scBody" x1="0" y1="0" x2="1" y2="1"><stop class="sc-a" offset="0"/><stop class="sc-b" offset="1"/></linearGradient>' +
        '<linearGradient id="scShine" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".38"/><stop offset=".45" stop-color="#fff" stop-opacity="0"/></linearGradient>' +
        '<linearGradient id="scBeam" x1="0" y1="0" x2="1" y2="0"><stop class="sc-lampc" offset="0" stop-opacity=".75"/><stop class="sc-lampc" offset="1" stop-opacity="0"/></linearGradient>' +
        '<path id="scBodyPath" d="' + BODY + '"/></defs>' +
        '<ellipse class="sc-ground" cx="266" cy="210" rx="224" ry="9"/><ellipse class="sc-glow" cx="266" cy="206" rx="196" ry="11"/>' +
        '<path class="sc-beam" d="M474 146L610 116V196Z"/>' +
        '<use href="#scBodyPath" class="sc-body"/><use href="#scBodyPath" fill="url(#scShine)"/>' +
        '<path class="sc-glass" d="M152 124C176 100 210 87 252 85L262 85L262 124ZM272 85L316 85C346 87 368 103 390 124L272 124Z"/>' +
        '<path class="sc-roof" d="M172 100C200 85 232 81 254 81L316 81C340 83 360 94 376 110"/>' +
        '<path class="sc-line" d="M70 150C200 138 340 138 466 152M267 124V174"/><path class="sc-line" d="M236 135H258M292 135H314"/>' +
        '<path class="sc-lamp" d="M452 140L474 146V150L452 146Z"/><path class="sc-tail" d="M58 148L80 144V148L58 152Z"/>' +
        wheel(136) + wheel(386) +
        '</svg></div></div></div>';

    function addDecor() {
        var body = document.body;
        if (!body.querySelector('.floor')) body.insertAdjacentHTML('beforeend', '<div class="floor" aria-hidden="true"></div>');
        var bar = document.createElement('div');
        bar.className = 'chargebar';
        bar.addEventListener('animationend', function () { bar.remove(); });
        body.appendChild(bar);

        document.querySelectorAll('.hero, .auth__pitch').forEach(function (el) {
            el.insertAdjacentHTML(el.classList.contains('hero') ? 'beforeend' : 'beforeend', SCENE);
        });

        document.querySelectorAll('[data-pct]').forEach(function (el) {
            var pct = Math.max(0, Math.min(100, Number(el.getAttribute('data-pct')) || 0));
            el.insertAdjacentHTML('beforeend', '<div class="meter" role="img" aria-label="' + pct + ' persen"><i></i></div>');
            var bar2 = el.querySelector('.meter i');
            requestAnimationFrame(function () { requestAnimationFrame(function () { bar2.style.width = pct + '%'; }); });
        });

        var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!fine || calm) return;

        document.querySelectorAll('.car, .stat, .tile').forEach(function (el) {
            el.classList.add('tilt');
            el.addEventListener('pointermove', function (e) {
                var r = el.getBoundingClientRect(), x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
                el.style.setProperty('--ry', ((x - .5) * 10).toFixed(2) + 'deg');
                el.style.setProperty('--rx', ((.5 - y) * 8).toFixed(2) + 'deg');
                el.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
                el.style.setProperty('--my', (y * 100).toFixed(1) + '%');
            });
            el.addEventListener('pointerleave', function () { el.style.setProperty('--rx', '0deg'); el.style.setProperty('--ry', '0deg'); });
        });

        var scenes = document.querySelectorAll('.scene');
        if (scenes.length) document.addEventListener('pointermove', function (e) {
            var px = (e.clientX / window.innerWidth - .5) * 2, py = (e.clientY / window.innerHeight - .5) * 2;
            scenes.forEach(function (sc) { sc.style.setProperty('--px', px.toFixed(3)); sc.style.setProperty('--py', py.toFixed(3)); });
        });
    }

    function init() {
        var body = document.body, role = body.dataset.role, root = body;
        if (body.classList.contains('struk-page') || body.hasAttribute('data-redirect')) return;
        if (role === 'admin' || role === 'owner' || role === 'customer') root = buildShell(role);
        else if (role === 'print') root = buildPaper();

        stripPipes(document);
        root.querySelectorAll('nav').forEach(function (n) { if (!n.closest('.app > header, .side')) n.classList.add('tabs'); });
        root.querySelectorAll('.tabs a').forEach(function (a) { if (isActive(a.href)) a.classList.add('is-active'); });
        decorateActions(root);
        decorateBadges(root);
        wrapTables(root);
        decorateAlerts(root);
        decorateBackLinks(root);
        passwordToggle(document);
        addDecor();
        body.classList.add('page-ready');
    }

    /* ---------- Pengganti onclick inline ---------- */
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm], [data-print], [data-close], [data-menu]');
        if (!el) return;
        if (el.hasAttribute('data-confirm') && !window.confirm(el.getAttribute('data-confirm'))) { e.preventDefault(); return; }
        if (el.hasAttribute('data-print')) window.print();
        if (el.hasAttribute('data-close')) window.close();
        if (el.hasAttribute('data-menu')) document.body.classList.toggle('menu-open');
    });
    document.addEventListener('click', function (e) {
        var tb = e.target.closest('[data-theme-toggle]');
        if (tb) {
            var next = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            setTheme(next);
            tb.innerHTML = svg(next === 'light' ? 'moon' : 'sun');
        }
        document.querySelectorAll('.nav__more[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
    });
    document.addEventListener('click', function (e) {
        if (document.body.classList.contains('menu-open') && !e.target.closest('.side, [data-menu]')) document.body.classList.remove('menu-open');
    });

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
 
(function () {
    'use strict';

    var FAV_KEY = 'ecr-fav', USER_KEY = 'ecr-user';

    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
    function readJSON(key) { try { return JSON.parse(localStorage.getItem(key)) || {}; } catch (e) { return {}; } }
    function writeJSON(key, v) { try { localStorage.setItem(key, JSON.stringify(v)); } catch (e) {} }

    /* ---------- Favorit ---------- */
    var HEART = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.8-9.6-9.6A5.3 5.3 0 0 1 12 6.4a5.3 5.3 0 0 1 9.6 4.5c-2.1 4.8-9.6 9.6-9.6 9.6z"/></svg>';

    function favs() { return readJSON(FAV_KEY); }

    function paintHearts() {
        var f = favs();
        $$('button.fav').forEach(function (b) {
            var on = !!f[b.getAttribute('data-plat')];
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
            b.setAttribute('aria-label', on ? 'Hapus dari favorit' : 'Simpan ke favorit');
            b.title = on ? 'Hapus dari favorit' : 'Simpan ke favorit';
        });
    }

    function renderFavPanel() {
        var panel = $('#favPanel');
        if (!panel) return;
        var f = favs(), keys = Object.keys(f);
        var list = $('.favs__list', panel), empty = $('.favs__empty', panel);
        list.innerHTML = '';
        empty.hidden = keys.length > 0;
        keys.forEach(function (plat) {
            var c = f[plat], li = document.createElement('li');
            li.innerHTML = '<span><a class="name" href="' + esc(c.u) + '">' + esc(c.n) + '</a><small>' + esc(plat) + (c.h ? ' &middot; ' + rp(c.h) + ' / hari' : '') + '</small></span>' +
                '<button type="button" class="rm" data-plat="' + esc(plat) + '">Hapus</button>';
            list.appendChild(li);
        });
    }

    function applyFavFilter() {
        var btn = $('#favOnly');
        if (!btn) return;
        var f = favs(), n = Object.keys(f).length, on = btn.getAttribute('aria-pressed') === 'true';
        $('b', btn).textContent = n;
        var cars = $$('.cars .car[data-plat]'), shown = 0;
        cars.forEach(function (c) {
            var hide = on && !f[c.getAttribute('data-plat')];
            c.hidden = hide;
            if (!hide) shown++;
        });
        var msg = $('#favEmpty');
        if (msg) msg.hidden = !(on && shown === 0);
    }

    function refreshFavUI() { paintHearts(); renderFavPanel(); applyFavFilter(); }

    function initFavs() {
        $$('.car[data-plat]').forEach(function (car) {
            var box = $('.car__img', car);
            if (!box || $('button.fav', box)) return;
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'fav';
            b.setAttribute('data-plat', car.getAttribute('data-plat'));
            b.innerHTML = HEART;
            box.appendChild(b);
        });

        document.addEventListener('click', function (e) {
            var heart = e.target.closest('button.fav');
            if (heart) {
                var car = heart.closest('.car'), f = favs(), plat = heart.getAttribute('data-plat');
                if (f[plat]) delete f[plat];
                else f[plat] = { n: car.getAttribute('data-nama') || plat, h: Number(car.getAttribute('data-harga')) || 0, u: car.getAttribute('data-url') || '#' };
                writeJSON(FAV_KEY, f);
                refreshFavUI();
                return;
            }
            var rm = e.target.closest('.favs__list button.rm');
            if (rm) {
                var f2 = favs();
                delete f2[rm.getAttribute('data-plat')];
                writeJSON(FAV_KEY, f2);
                refreshFavUI();
                return;
            }
            var only = e.target.closest('#favOnly');
            if (only) {
                only.setAttribute('aria-pressed', only.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
                applyFavFilter();
            }
        });
        refreshFavUI();
    }

    /* ---------- Hitung mundur rental aktif ---------- */
    function span(ms) {
        var m = Math.floor(Math.abs(ms) / 60000), d = Math.floor(m / 1440), h = Math.floor((m % 1440) / 60), mi = m % 60;
        if (d > 0) return d + ' hari ' + h + ' jam';
        if (h > 0) return h + ' jam ' + mi + ' menit';
        return Math.max(mi, 1) + ' menit';
    }

    function initTrip() {
        var el = $('.trip[data-start][data-end]');
        if (!el) return;
        var start = new Date(el.getAttribute('data-start')).getTime(), end = new Date(el.getAttribute('data-end')).getTime();
        if (isNaN(start) || isNaN(end) || end <= start) return;
        var count = $('.trip__count', el), label = $('.trip__left small', el), bar = $('.trip__bar i', el);
        function tick() {
            var now = Date.now(), p = Math.max(0, Math.min(1, (now - start) / (end - start)));
            bar.style.width = (p * 100).toFixed(1) + '%';
            bar.style.setProperty('--p', (p * 100).toFixed(1) + '%');
            count.classList.remove('is-late');
            if (now < start) { label.textContent = 'Dimulai dalam'; count.textContent = span(start - now); }
            else if (now <= end) { label.textContent = 'Sisa waktu sewa'; count.textContent = span(end - now); }
            else { label.textContent = 'Segera kembalikan mobil'; count.textContent = 'Terlambat ' + span(now - end); count.classList.add('is-late'); }
        }
        tick();
        setInterval(tick, 30000);
    }

    /* ---------- Kalkulator estimasi ---------- */
    function initCalc() {
        var box = $('.calc');
        if (!box) return;
        var sel = $('#calcCar', box), days = $('#calcDays', box), out = $('.calc__out', box), go = $('#calcGo', box);
        var saldo = Number(box.getAttribute('data-saldo')) || 0;
        function clampDays() { var d = parseInt(days.value, 10); if (isNaN(d) || d < 1) d = 1; if (d > 60) d = 60; return d; }
        function update() {
            var opt = sel.options[sel.selectedIndex];
            if (!sel.value) {
                out.innerHTML = '<p class="calc__idle">Pilih mobil untuk melihat perkiraan biaya.</p>';
                go.setAttribute('aria-disabled', 'true'); go.setAttribute('href', '#');
                return;
            }
            var harga = Number(opt.getAttribute('data-harga')) || 0, d = clampDays(), total = harga * d;
            var note = total <= saldo
                ? '<p class="calc__note ok">Saldo Anda cukup untuk sewa ini.</p>'
                : '<p class="calc__note bad">Saldo kurang ' + rp(total - saldo) + '. <a href="isi_saldo.php">Isi saldo</a> sebelum menyewa.</p>';
            out.innerHTML = '<dl><dt>Tarif per hari</dt><dd>' + rp(harga) + '</dd><dt>Lama sewa</dt><dd>' + d + ' hari</dd></dl>' +
                '<div class="calc__total"><span>Perkiraan total</span><b>' + rp(total) + '</b></div>' + note;
            go.setAttribute('aria-disabled', 'false');
            go.setAttribute('href', 'sewamobil.php?plat_mobil=' + encodeURIComponent(sel.value));
        }
        sel.addEventListener('change', update);
        days.addEventListener('input', update);
        days.addEventListener('change', function () { days.value = clampDays(); update(); });
        $$('.stepbtn', box).forEach(function (b) {
            b.addEventListener('click', function () {
                days.value = Math.max(1, Math.min(60, clampDays() + Number(b.getAttribute('data-step'))));
                update();
            });
        });
        update();
    }

    /* ---------- Login & Daftar ---------- */
    var SUN = '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>';
    var MOON = '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>';

    function addThemeButton() {
        var light = document.documentElement.getAttribute('data-theme') === 'light';
        var wrap = document.createElement('div');
        wrap.className = 'auth-tools';
        wrap.innerHTML = '<button type="button" class="theme-btn" data-theme-toggle aria-label="Ganti tema terang/gelap"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (light ? MOON : SUN) + '</svg></button>';
        document.body.appendChild(wrap);
    }

    function after(node, el) { node.parentNode.insertBefore(el, node.nextSibling); }
    function holder(input) { return input.closest('.pw') || input; }

    function initCaps() {
        $$('input[type="password"]').forEach(function (inp) {
            var hint = document.createElement('span');
            hint.className = 'hint bad';
            hint.setAttribute('aria-live', 'polite');
            after(holder(inp), hint);
            function check(e) {
                if (!e.getModifierState) return;
                hint.textContent = e.getModifierState('CapsLock') ? 'Caps Lock sedang aktif.' : '';
                inp._capsHint = hint;
            }
            inp.addEventListener('keydown', check);
            inp.addEventListener('keyup', check);
            inp.addEventListener('blur', function () { if (!inp._keep) hint.textContent = ''; });
            inp._hint = hint;
        });
    }

    function initStrength() {
        var inp = $('input[data-strength]');
        if (!inp) return;
        var bar = document.createElement('div');
        bar.className = 'strength';
        bar.innerHTML = '<i></i><i></i><i></i><i></i>';
        var txt = document.createElement('span');
        txt.className = 'hint';
        after(holder(inp), bar);
        after(bar, txt);
        var NAMES = ['Minimal 6 karakter.', 'Lemah. Tambahkan huruf dan angka.', 'Cukup. Tambahkan huruf besar atau panjangkan.', 'Bagus.', 'Kuat.'];
        inp.addEventListener('input', function () {
            var v = inp.value, s = 0;
            if (v.length >= 6) s++;
            if (v.length >= 8 && /[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
            if (/\d/.test(v) && /[a-zA-Z]/.test(v)) s++;
            if (v.length >= 12 || /[^a-zA-Z0-9]/.test(v)) s++;
            if (v.length < 6) s = 0;
            bar.setAttribute('data-level', s);
            txt.textContent = v ? NAMES[s] : '';
        });
    }

    function initMatch() {
        $$('input[data-match]').forEach(function (inp) {
            var src = $(inp.getAttribute('data-match'));
            if (!src) return;
            var hint = document.createElement('span');
            hint.className = 'hint';
            hint.setAttribute('aria-live', 'polite');
            after(holder(inp), hint);
            function check() {
                if (!inp.value) { hint.textContent = ''; hint.className = 'hint'; inp.setCustomValidity(''); return; }
                var same = inp.value === src.value;
                hint.textContent = same ? 'Password cocok.' : 'Password belum sama.';
                hint.className = 'hint ' + (same ? 'ok' : 'bad');
                inp.setCustomValidity(same ? '' : 'Password belum sama.');
            }
            inp.addEventListener('input', check);
            src.addEventListener('input', check);
        });
    }

    function initDigits() {
        $$('input[data-digits]').forEach(function (inp) {
            var need = Number(inp.getAttribute('data-count')) || 0, hint = null;
            if (need) { hint = document.createElement('span'); hint.className = 'hint'; after(inp, hint); }
            inp.addEventListener('input', function () {
                var clean = inp.value.replace(/\D+/g, '');
                if (clean !== inp.value) inp.value = clean;
                if (hint) {
                    var n = inp.value.length;
                    hint.textContent = n ? (n < need ? 'NIK ' + need + ' digit (' + n + '/' + need + ').' : (n === need ? 'NIK lengkap.' : 'NIK lebih dari ' + need + ' digit.')) : '';
                    hint.className = 'hint ' + (n === need ? 'ok' : '');
                }
            });
        });
    }

    function initRemember() {
        var form = $('form[data-login]');
        if (!form) return;
        var user = $('#username', form), chk = $('#remember', form), pass = $('#password', form), saved = '';
        try { saved = localStorage.getItem(USER_KEY) || ''; } catch (e) {}
        if (saved && user && !user.value) { user.value = saved; if (chk) chk.checked = true; if (pass) pass.focus(); }
        else if (chk && saved) chk.checked = true;
        form.addEventListener('submit', function () {
            try {
                if (chk && chk.checked) localStorage.setItem(USER_KEY, user.value.trim());
                else localStorage.removeItem(USER_KEY);
            } catch (e) {}
        });
    }

    function initLoading() {
        $$('.auth__card form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var b = $('button[type="submit"]', form);
                if (b) { b.classList.add('is-loading'); b.setAttribute('aria-busy', 'true'); }
            });
        });
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) $$('button.is-loading').forEach(function (b) { b.classList.remove('is-loading'); b.removeAttribute('aria-busy'); });
        });
    }

    function init() {
        if (document.body.classList.contains('auth-page')) {
            addThemeButton();
            initCaps(); initStrength(); initMatch(); initDigits(); initRemember(); initLoading();
        }
        initFavs();
        initTrip();
        initCalc();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
 
document.addEventListener('DOMContentLoaded', function () {
    var box = document.querySelector('[data-quick]');
    var input = box && document.getElementById(box.dataset.quick);
    if (!input) return;
    [100000, 250000, 500000, 1000000].forEach(function (n) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'chip-btn';
        b.textContent = 'Rp ' + n.toLocaleString('id-ID');
        b.addEventListener('click', function () { input.value = n; input.focus(); });
        box.appendChild(b);
    });
});
 
document.addEventListener('DOMContentLoaded', function () {
    var mulaiEl = document.querySelector('[name="waktu_pinjam"]');
    var kembaliEl = document.querySelector('[name="waktu_kembali"]');
    var hargaEl = document.querySelector('[name="harga_rental"]');
    var durasiEl = document.getElementById('durasi_sewa');
    var totalEl = document.getElementById('total_sewa');
    if (!mulaiEl || !kembaliEl || !durasiEl || !totalEl) return;

    function hitungSewa() {
        var harga = Number(hargaEl ? hargaEl.value || 0 : 0);
        if (!mulaiEl.value || !kembaliEl.value) { durasiEl.textContent = '-'; totalEl.textContent = 'Rp 0'; return; }
        var awal = new Date(mulaiEl.value).getTime();
        var akhir = new Date(kembaliEl.value).getTime();
        if (!isFinite(awal) || !isFinite(akhir) || akhir <= awal) { durasiEl.textContent = 'Waktu tidak valid'; totalEl.textContent = 'Rp 0'; return; }
        var jam = (akhir - awal) / 3600000;
        var hari = Math.ceil(jam / 24);
        durasiEl.textContent = hari + ' hari (' + jam.toFixed(1) + ' jam)';
        totalEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(harga * hari);
    }
    mulaiEl.addEventListener('change', hitungSewa);
    kembaliEl.addEventListener('change', hitungSewa);
});
 
document.addEventListener('DOMContentLoaded', function () {
    var ids = ['plat_mobil', 'tanggal', 'jam', 'durasi'];
    var els = ids.map(function (i) { return document.getElementById(i); });
    if (els.some(function (e) { return !e; })) return;

    function f(x) {
        var p = function (n) { return String(n).padStart(2, '0'); };
        return x.getFullYear() + '-' + p(x.getMonth() + 1) + '-' + p(x.getDate()) + ' ' + p(x.getHours()) + ':' + p(x.getMinutes()) + ':00';
    }
    function hitung() {
        var s = els[0], o = s.options[s.selectedIndex];
        var harga = Number(o ? o.dataset.harga || 0 : 0);
        var d = Number(els[3].value || 0), t = els[1].value, j = els[2].value;
        if (harga && d) document.getElementById('total').textContent = 'Rp ' + (harga * d).toLocaleString('id-ID');
        if (t && j && d) {
            var a = new Date(t + 'T' + j + ':00'), b = new Date(a);
            b.setDate(b.getDate() + d);
            document.getElementById('waktu_pinjam').value = f(a);
            document.getElementById('waktu_kembali').value = f(b);
        }
    }
    els.forEach(function (e) { e.addEventListener('change', hitung); });
});

 
document.addEventListener('DOMContentLoaded', function () {
    var s = document.getElementById('plat_mobil');
    var p = document.getElementById('waktu_pinjam');
    var k = document.getElementById('waktu_kembali');
    var total = document.getElementById('total');
    if (!s || !p || !k || !total) return;

    function hitung() {
        var o = s.options[s.selectedIndex];
        var h = Number(o ? o.dataset.harga || 0 : 0);
        var a = new Date(p.value), b = new Date(k.value);
        var d = Math.max(1, Math.ceil((b - a) / 86400000));
        if (h && b > a) total.textContent = 'Rp ' + (h * d).toLocaleString('id-ID');
    }
    [s, p, k].forEach(function (e) { e.addEventListener('change', hitung); });
});
 
(function () {
    'use strict';
    function ready(fn) { if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn(); }

    /* Pesan lalu pindah halaman: <body data-flash="..." data-redirect="..."> */
    ready(function () {
        var b = document.body;
        if (!b || !b.hasAttribute('data-redirect')) return;
        var msg = b.getAttribute('data-flash'), url = b.getAttribute('data-redirect');
        if (msg) window.alert(msg);
        window.location.href = url;
    });

    
    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-theme-flip]');
        if (!t) return;
        var h = document.documentElement, n = h.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        h.setAttribute('data-theme', n);
        try { localStorage.setItem('ecr-theme', n); } catch (err) {}
    });

    /* Pratinjau foto pada form upload: <input type="file" data-preview="#idGambar"> */
    ready(function () {
        document.querySelectorAll('input[type="file"][data-preview]').forEach(function (inp) {
            var img = document.querySelector(inp.getAttribute('data-preview'));
            if (!img) return;
            inp.addEventListener('change', function () {
                var f = inp.files && inp.files[0];
                if (!f || f.type.indexOf('image/') !== 0) { img.classList.remove('is-on'); img.removeAttribute('src'); return; }
                img.src = URL.createObjectURL(f);
                img.classList.add('is-on');
            });
        });
    });
})();
 
(function () {
    'use strict';
    var doc = document, root = doc.documentElement, body = doc.body;
    var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function $(s, r) { return (r || doc).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || doc).querySelectorAll(s)); }
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function ready(fn) { if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', fn); else fn(); }
    function skip() { return body.classList.contains('struk-page') || body.hasAttribute('data-redirect') || body.getAttribute('data-role') === 'print'; }

    /* ---------- Latar hidup + spotlight ---------- */
    var fxEl = null;
    function fx() {
        if ($('.fx')) return;
        fxEl = doc.createElement('div');
        fxEl.className = 'fx';
        fxEl.setAttribute('aria-hidden', 'true');
        fxEl.innerHTML = '<i class="fx__blob fx__blob--1"></i><i class="fx__blob fx__blob--2"></i><i class="fx__blob fx__blob--3"></i><i class="fx__grid"></i><i class="fx__spot"></i><i class="fx__grain"></i>';
        body.insertBefore(fxEl, body.firstChild);
        body.insertAdjacentHTML('beforeend', '<div class="progress" aria-hidden="true"></div>');
    }

    function pointerFx() {
        if (!fine || calm) return;
        var busy = false, x = 0, y = 0, tgt = null;
        doc.addEventListener('pointermove', function (e) {
            x = e.clientX; y = e.clientY; tgt = e.target;
            if (busy) return;
            busy = true;
            requestAnimationFrame(function () {
                busy = false;
                if (fxEl) { fxEl.style.setProperty('--sx', x + 'px'); fxEl.style.setProperty('--sy', y + 'px'); }
                var g = tgt && tgt.closest ? tgt.closest('.glass') : null;
                if (g) {
                    var r = g.getBoundingClientRect();
                    g.style.setProperty('--gx', (x - r.left).toFixed(0) + 'px');
                    g.style.setProperty('--gy', (y - r.top).toFixed(0) + 'px');
                }
            });
        }, { passive: true });
    }

    /* ---------- Navbar: pil yang meluncur ---------- */
    function glider() {
        var nav = $('.pill .nav');
        if (!nav) return;
        var g = doc.createElement('span');
        g.className = 'nav__glider';
        g.setAttribute('aria-hidden', 'true');
        nav.insertBefore(g, nav.firstChild);
        function active() { return $(':scope > a.is-active', nav); }
        function place(a) {
            if (!a || !a.offsetWidth) { nav.classList.remove('has-glider'); return; }
            g.style.width = a.offsetWidth + 'px';
            g.style.height = a.offsetHeight + 'px';
            g.style.transform = 'translate(' + a.offsetLeft + 'px,' + a.offsetTop + 'px)';
            nav.classList.add('has-glider');
        }
        $$(':scope > a', nav).forEach(function (a) {
            a.addEventListener('pointerenter', function () { place(a); });
            a.addEventListener('focus', function () { place(a); });
        });
        nav.addEventListener('pointerleave', function () { place(active()); });
        window.addEventListener('resize', function () { place(active()); });
        window.addEventListener('ecr-nav-fit', function () { place(active()); });
        place(active());
        if (doc.fonts && doc.fonts.ready) doc.fonts.ready.then(function () { place(active()); });
    }

    /* ---------- Navbar: otomatis dipadatkan sampai muat satu baris ---------- */
    function fitNav() {
        var pill = $('.pill'), nav = $('.pill .nav');
        if (!pill || !nav) return;
        var steps = ['f1', 'f2', 'f3', 'f4'];
        function over() { return pill.scrollWidth > pill.clientWidth + 1; }
        function run() {
            pill.classList.remove('f1', 'f2', 'f3', 'f4', 'fw');
            if (!nav.offsetWidth && !nav.offsetHeight) return; /* ponsel: pakai dock */
            for (var i = 0; i < steps.length && over(); i++) pill.classList.add(steps[i]);
            if (over()) { pill.classList.remove('f1', 'f2', 'f3', 'f4'); pill.classList.add('fw'); }
            window.dispatchEvent(new Event('ecr-nav-fit'));
        }
        var t;
        window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(run, 60); });
        run();
        if (doc.fonts && doc.fonts.ready) doc.fonts.ready.then(run);
        window.addEventListener('load', run);
    }

    /* ---------- Strip HUD (jam & mode) ---------- */
    function hud() {
        var app = $('.app--top'), head = app && $('.pill', app);
        if (!head) return;
        var label = { admin: 'Mode admin', owner: 'Portal mitra', customer: 'Mode pelanggan' }[body.getAttribute('data-role')] || 'eCar-Rent';
        var h = doc.createElement('div');
        h.className = 'hud';
        h.innerHTML = '<div class="hud__l"><span class="dot"></span><span>' + label + '</span><span class="hud__sep">/</span><span>Sistem online</span></div>' +
            '<div class="hud__r"><span class="hud__date"></span><time class="hud__time"></time></div>';
        head.insertAdjacentElement('afterend', h);
        var t = $('.hud__time', h), d = $('.hud__date', h);
        function tick() {
            if (doc.hidden) return;
            var n = new Date();
            t.textContent = pad(n.getHours()) + ':' + pad(n.getMinutes()) + ':' + pad(n.getSeconds());
            d.textContent = n.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
        }
        tick();
        setInterval(tick, 1000);
    }

    /* ---------- Dock bawah (ponsel) ---------- */
    function shortLabel(t) { return t === 'Isi saldo' ? 'Saldo' : t.split(' ')[0]; }
    function dock() {
        var E = window.__ecr;
        if (!E || $('.dock')) return;
        var main = [];
        for (var i = 0; i < E.items.length; i++) { if (E.items[i].g) break; if (E.items[i].h) main.push(E.items[i]); }
        var html = main.map(function (x) {
            var on = E.isActive(x.h);
            return '<a href="' + x.h + '"' + (on ? ' class="is-active" aria-current="page"' : '') + '>' + E.svg(x.i) + '<span>' + shortLabel(x.t) + '</span></a>';
        }).join('');
        html += '<button type="button" data-palette aria-label="Pencarian cepat">' + E.svg('search') + '<span>Cari</span></button>';
        body.insertAdjacentHTML('beforeend', '<nav class="dock" aria-label="Menu cepat">' + html + '</nav>');
    }

    /* ---------- Command palette ---------- */
    function palette() {
        var E = window.__ecr;
        if (!E || $('.cmdk')) return;
        var entries = [];
        E.items.forEach(function (x) { if (x.h) entries.push({ t: x.t, i: x.i, h: x.h, k: 'Menu' }); });
        entries.push({ t: 'Ganti tema terang / gelap', i: 'sun', a: 'theme', k: 'Aksi' });
        entries.push({ t: 'Cetak halaman ini', i: 'receipt', a: 'print', k: 'Aksi' });
        entries.push({ t: 'Keluar dari akun', i: 'logout', h: E.logout, k: 'Aksi' });

        var mac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
        $$('.cmdk-btn kbd').forEach(function (k) { k.textContent = mac ? '⌘ K' : 'Ctrl K'; });

        var el = doc.createElement('div');
        el.className = 'cmdk';
        el.innerHTML = '<div class="cmdk__box" role="dialog" aria-modal="true" aria-label="Pencarian cepat">' +
            '<div class="cmdk__in">' + E.svg('search') + '<input type="text" placeholder="Ketik untuk mencari menu atau aksi…" aria-label="Cari menu" autocomplete="off" spellcheck="false"></div>' +
            '<div class="cmdk__list" role="listbox"></div>' +
            '<div class="cmdk__foot"><span><kbd>↑</kbd> <kbd>↓</kbd> pilih</span><span><kbd>Enter</kbd> buka</span><span><kbd>Esc</kbd> tutup</span></div></div>';
        body.appendChild(el);
        var input = $('input', el), list = $('.cmdk__list', el), shown = [], sel = 0, last = null;

        function render() {
            var q = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
            shown = entries.filter(function (e) { var s = (e.t + ' ' + e.k).toLowerCase(); return q.every(function (w) { return s.indexOf(w) !== -1; }); });
            sel = Math.min(sel, Math.max(shown.length - 1, 0));
            if (!shown.length) { list.innerHTML = '<p class="cmdk__none">Tidak ada yang cocok. Coba kata lain.</p>'; return; }
            list.innerHTML = shown.map(function (e, n) {
                return '<a role="option" class="cmdk__item' + (n === sel ? ' is-on' : '') + '" data-n="' + n + '" href="' + (e.h || '#') + '"' + (n === sel ? ' aria-selected="true"' : '') + '>' + E.svg(e.i) + '<span>' + e.t + '</span><small>' + e.k + '</small></a>';
            }).join('');
        }
        function mark() {
            $$('.cmdk__item', list).forEach(function (a, n) {
                a.classList.toggle('is-on', n === sel);
                if (n === sel) { a.setAttribute('aria-selected', 'true'); a.scrollIntoView({ block: 'nearest' }); } else a.removeAttribute('aria-selected');
            });
        }
        function open() {
            last = doc.activeElement;
            input.value = ''; sel = 0; render();
            el.classList.add('is-open');
            setTimeout(function () { input.focus(); }, 10);
        }
        function close() {
            el.classList.remove('is-open');
            if (last && last.focus) last.focus();
        }
        function run(e) {
            if (!e) return;
            if (e.a === 'theme') { var tb = $('[data-theme-toggle]'); if (tb) tb.click(); else { var n = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light'; root.setAttribute('data-theme', n); try { localStorage.setItem('ecr-theme', n); } catch (x) {} } close(); }
            else if (e.a === 'print') { close(); setTimeout(function () { window.print(); }, 120); }
            else if (e.h) { window.location.href = e.h; }
        }

        input.addEventListener('input', function () { sel = 0; render(); });
        list.addEventListener('click', function (ev) {
            var a = ev.target.closest('.cmdk__item');
            if (!a) return;
            ev.preventDefault();
            run(shown[Number(a.getAttribute('data-n'))]);
        });
        list.addEventListener('pointermove', function (ev) {
            var a = ev.target.closest('.cmdk__item');
            if (a && Number(a.getAttribute('data-n')) !== sel) { sel = Number(a.getAttribute('data-n')); mark(); }
        });
        el.addEventListener('click', function (ev) { if (ev.target === el) close(); });
        el.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') { ev.preventDefault(); close(); }
            else if (ev.key === 'ArrowDown') { ev.preventDefault(); if (shown.length) { sel = (sel + 1) % shown.length; mark(); } }
            else if (ev.key === 'ArrowUp') { ev.preventDefault(); if (shown.length) { sel = (sel - 1 + shown.length) % shown.length; mark(); } }
            else if (ev.key === 'Enter') { ev.preventDefault(); run(shown[sel]); }
        });
        doc.addEventListener('keydown', function (ev) {
            var typing = /^(INPUT|TEXTAREA|SELECT)$/.test((ev.target.tagName || '')) || ev.target.isContentEditable;
            if ((ev.key === 'k' || ev.key === 'K') && (ev.ctrlKey || ev.metaKey)) { ev.preventDefault(); el.classList.contains('is-open') ? close() : open(); }
            else if (ev.key === '/' && !typing && !ev.ctrlKey && !ev.metaKey && !ev.altKey) { ev.preventDefault(); open(); }
        });
        doc.addEventListener('click', function (ev) { if (ev.target.closest('[data-palette]')) open(); });
    }

    /* ---------- Muncul saat di-scroll ---------- */
    function reveal() {
        var scope = $('.content');
        if (!scope || calm || !('IntersectionObserver' in window) || typeof Map === 'undefined') return;
        var sel = '.stat, .car, .tile, .steps li, .panel-head, .table-wrap, .trip, .calc, .favs, .page-head, .empty, .profile, .detail > *, .booking > *, .marquee, .alert, .hero, .chips, .content > form, .content > .section';
        var els = $$(sel, scope).filter(function (e) { return !e.hidden && !e.closest('[hidden]'); });
        if (!els.length) return;
        var counts = new Map();
        function show(el) { el.classList.add('in'); setTimeout(function () { el.classList.remove('rv', 'in'); }, 1500); }
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) { if (!e.isIntersecting) return; io.unobserve(e.target); show(e.target); });
        }, { threshold: .06, rootMargin: '0px 0px -5% 0px' });
        els.forEach(function (el) {
            var p = el.parentNode, n = counts.get(p) || 0;
            counts.set(p, n + 1);
            el.style.setProperty('--i', Math.min(n, 8));
            el.classList.add('rv');
            io.observe(el);
        });
        root.classList.add('rv-on');
        setTimeout(function () { $$('.rv:not(.in)').forEach(show); }, 5000);
    }

    /* ---------- Angka statistik berhitung naik ---------- */
    function countUp() {
        var els = $$('.stat b').filter(function (b) { return !b.children.length; });
        if (!els.length) return;
        function parse(t) {
            var m = t.match(/^(Rp\s*)?(\d{1,3}(?:\.\d{3})+|\d+)(.*)$/);
            return m ? { pre: m[1] || '', val: parseInt(m[2].replace(/\./g, ''), 10), suf: m[3] || '' } : null;
        }
        els.forEach(function (el) {
            var p = parse(el.textContent.trim());
            if (!p || !isFinite(p.val) || p.val < 1 || p.val > 1e12) return;
            var finalText = el.textContent, done = false;
            function end() { done = true; el.textContent = finalText; }
            function run() {
                if (done) return;
                done = true;
                if (calm) { el.textContent = finalText; return; }
                var t0 = performance.now(), dur = 1200;
                (function step(now) {
                    var k = Math.min(1, (now - t0) / dur), e = k === 1 ? 1 : 1 - Math.pow(2, -10 * k);
                    el.textContent = p.pre + Math.round(p.val * e).toLocaleString('id-ID') + p.suf;
                    if (k < 1) requestAnimationFrame(step); else el.textContent = finalText;
                })(t0);
            }
            if (!('IntersectionObserver' in window) || calm) return;
            el.textContent = p.pre + '0' + p.suf;
            var io = new IntersectionObserver(function (es) { if (es[0].isIntersecting) { io.disconnect(); run(); } }, { threshold: .4 });
            io.observe(el);
            setTimeout(function () { if (!done) { io.disconnect(); end(); } }, 6000);
        });
    }

    function init() {
        if (skip()) return;
        fx();
        pointerFx();
        glider();
        fitNav();
        hud();
        dock();
        palette();
        reveal();
        countUp();
    }
    ready(init);
})();
