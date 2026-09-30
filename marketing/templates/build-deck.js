// ساخت فایل پاورپوینت معرفی فَروَم از site/data/presentation.json
// اجرا: NODE_PATH=<node_modules with pptxgenjs, react-icons, react, react-dom, sharp> node marketing/templates/build-deck.js <out.pptx>
const path = require('path');
const fs = require('fs');
const pptxgen = require('pptxgenjs');
const sharp = require('sharp');
const React = require('react');
const { renderToStaticMarkup } = require('react-dom/server');
const fa = require('react-icons/fa6');

const ROOT = path.resolve(__dirname, '../../site');
const DATA = JSON.parse(fs.readFileSync(path.join(ROOT, 'data/presentation.json'), 'utf8'));
const OUT = process.argv[2] || path.join(ROOT, 'files/farvam-presentation.pptx');

// brand palette (dark, gold, teal)
const C = { bg: '070B14', card: '121A2E', card2: '17213A', line: '2A3553', text: 'EEF2F8', muted: 'A3AEC2', gold: 'FFC84A', gold2: 'D9A43A', teal: '2EF2D0', rose: 'FF6B8B' };
const FONT = 'Tahoma'; // ships with Windows/Office and covers Persian
const W = 10, H = 5.625, M = 0.5;

async function icon(Comp, color, size = 256) {
  const svg = renderToStaticMarkup(React.createElement(Comp, { color: '#' + color, size }));
  const png = await sharp(Buffer.from(svg)).resize(size, size).png().toBuffer();
  return 'image/png;base64,' + png.toString('base64');
}
async function img(name) {
  const f = path.join(ROOT, 'images', name + '.webp');
  const p = fs.existsSync(f) ? f : path.join(ROOT, 'images', name + '.png');
  const meta = await sharp(p).metadata();
  const alpha = name.startsWith('logo');
  const pipe = sharp(p).resize({ width: Math.min(meta.width, alpha ? 700 : 1300), withoutEnlargement: true });
  const buf = alpha ? await pipe.png({ compressionLevel: 9, palette: true }).toBuffer() : await pipe.flatten({ background: '#ffffff' }).jpeg({ quality: 80, mozjpeg: true }).toBuffer();
  return { data: (alpha ? 'image/png' : 'image/jpeg') + ';base64,' + buf.toString('base64'), w: meta.width, h: meta.height };
}
async function background() {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1920" height="1080">
    <defs>
      <radialGradient id="a" cx="88%" cy="-5%" r="70%"><stop offset="0" stop-color="#FFC84A" stop-opacity=".22"/><stop offset="1" stop-color="#FFC84A" stop-opacity="0"/></radialGradient>
      <radialGradient id="b" cx="0%" cy="90%" r="60%"><stop offset="0" stop-color="#2EF2D0" stop-opacity=".13"/><stop offset="1" stop-color="#2EF2D0" stop-opacity="0"/></radialGradient>
      <linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#060910"/><stop offset="1" stop-color="#0B1020"/></linearGradient>
    </defs>
    <rect width="1920" height="1080" fill="url(#g)"/><rect width="1920" height="1080" fill="url(#a)"/><rect width="1920" height="1080" fill="url(#b)"/>
  </svg>`;
  // gold dust
  let dots = '';
  for (let i = 0; i < 260; i++) { const x = Math.random() * 1920, y = Math.random() * 1080, r = Math.random() * 1.8 + .3, o = (Math.random() * .5 + .15).toFixed(2); dots += `<circle cx="${x}" cy="${y}" r="${r}" fill="#FFD27A" opacity="${o}"/>`; }
  const full = svg.replace('</svg>', dots + '</svg>');
  const jpg = await sharp(Buffer.from(full)).jpeg({ quality: 84, mozjpeg: true }).toBuffer();
  return 'image/jpeg;base64,' + jpg.toString('base64');
}

const T = (o) => Object.assign({ fontFace: FONT, color: C.text, rtlMode: true, align: 'right', isTextBox: true, margin: 0, valign: 'top' }, o);
const shadow = () => ({ type: 'outer', color: '000000', blur: 8, offset: 3, angle: 90, opacity: 0.45 });

(async () => {
  const pres = new pptxgen();
  pres.layout = 'LAYOUT_16x9';
  pres.rtlMode = true;
  pres.title = DATA.title; pres.author = 'فَروَم'; pres.company = 'Farvam';
  const BG = await background();
  const logo = await img('logo-metal');
  const I = {
    receipt: await icon(fa.FaReceipt, C.rose), scale: await icon(fa.FaScaleBalanced, C.rose), eye: await icon(fa.FaEye, C.rose), stamp: await icon(fa.FaStamp, C.rose), industry: await icon(fa.FaIndustry, C.rose),
    coins: await icon(fa.FaCoins, C.gold), camera: await icon(fa.FaCamera, C.teal), chart: await icon(fa.FaChartLine, C.gold), users: await icon(fa.FaUsers, C.teal),
    check: await icon(fa.FaCircleCheck, C.gold), phone: await icon(fa.FaPhone, C.gold), globe: await icon(fa.FaGlobe, C.teal), pin: await icon(fa.FaLocationDot, C.gold),
    arrow: await icon(fa.FaArrowLeftLong, C.teal), shield: await icon(fa.FaShieldHalved, C.gold), vault: await icon(fa.FaVault, C.teal), mobile: await icon(fa.FaMobileScreenButton, C.gold),
    gem: await icon(fa.FaGem, C.gold), handshake: await icon(fa.FaHandshake, C.teal), xmark: await icon(fa.FaXmark, C.rose),
  };
  const slides = DATA.slides;
  const total = slides.length;

  function base(s, i) {
    const sl = pres.addSlide();
    sl.background = { data: BG };
    if (s.kind !== 'cover') {
      sl.addImage({ data: logo.data, x: M, y: 0.28, w: 0.62, h: 0.62 * logo.h / logo.w });
      sl.addText(`${(i + 1).toLocaleString('fa-IR')} / ${total.toLocaleString('fa-IR')}`, T({ x: M + 0.75, y: 0.36, w: 1.2, h: 0.3, fontSize: 9, color: C.muted, align: 'left', rtlMode: false }));
    }
    return sl;
  }
  function title(sl, text, sub, y = 0.3, w = 7.8) {
    sl.addText(text, T({ x: W - M - w, y, w, h: 0.62, fontSize: 24, bold: true, color: C.text, valign: 'middle', fit: 'shrink' }));
    if (sub) sl.addText(sub, T({ x: W - M - w, y: y + 0.66, w, h: 0.55, fontSize: 12, color: C.muted, lineSpacingMultiple: 1.2 }));
  }
  function bullets(sl, items, x, y, w, h, size = 12) {
    const rows = [];
    items.forEach((b, k) => rows.push({ text: b, options: { bullet: { code: '25C6', indent: 14 }, color: C.text, breakLine: k < items.length - 1, paraSpaceAfter: 7 } }));
    sl.addText(rows, T({ x, y, w, h, fontSize: size, lineSpacingMultiple: 1.15 }));
  }
  function framed(sl, im, x, y, w, maxH) {
    let h = w * im.h / im.w; if (h > maxH) { h = maxH; w = h * im.w / im.h; }
    sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: x - 0.06, y: y - 0.06, w: w + 0.12, h: h + 0.12, fill: { color: C.card }, line: { color: C.gold2, width: 1.25 }, rectRadius: 0.1, shadow: shadow() });
    sl.addImage({ data: im.data, x, y, w, h, rounding: false });
    return { w, h };
  }
  function iconDisc(sl, data, x, y, d = 0.52, ring = C.gold2) {
    sl.addShape(pres.shapes.OVAL, { x, y, w: d, h: d, fill: { color: C.card2 }, line: { color: ring, width: 1.25 } });
    sl.addImage({ data, x: x + d * 0.24, y: y + d * 0.24, w: d * 0.52, h: d * 0.52 });
  }

  for (let i = 0; i < slides.length; i++) {
    const s = slides[i];
    const sl = base(s, i);

    if (s.kind === 'cover') {
      const lw = 3.3, lh = lw * logo.h / logo.w;
      sl.addImage({ data: logo.data, x: M + 0.2, y: (H - lh) / 2 - 0.1, w: lw, h: lh });
      sl.addText(s.title, T({ x: 4.3, y: 1.0, w: 5.2, h: 1.0, fontSize: 54, bold: true, color: C.gold }));
      sl.addText(s.subtitle, T({ x: 4.3, y: 2.1, w: 5.2, h: 0.9, fontSize: 18, bold: true }));
      sl.addText(s.tagline, T({ x: 4.3, y: 3.1, w: 5.2, h: 0.8, fontSize: 12.5, color: C.muted, lineSpacingMultiple: 1.25 }));
      sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: 6.9, y: 4.35, w: 2.6, h: 0.5, fill: { color: C.gold }, line: { color: C.gold }, rectRadius: 0.25 });
      sl.addText('یک ماه استفاده رایگان', T({ x: 6.9, y: 4.35, w: 2.6, h: 0.5, fontSize: 13, bold: true, color: '241803', align: 'center', valign: 'middle' }));
      sl.addNotes('معرفی کوتاه: فروم یک نرم‌افزار حسابداری ساده نیست؛ اکوسیستمی است که همه بخش‌های کسب‌وکار طلا را به هم وصل می‌کند.');
    }

    else if (s.kind === 'problem') {
      title(sl, s.title, s.subtitle, 1.0, 9);
      const ic = [I.receipt, I.scale, I.eye, I.stamp, I.industry];
      const cw = 1.7, gap = 0.125, y = 2.75;
      s.items.forEach(([t, d], k) => {
        const x = W - M - (k + 1) * cw - k * gap;
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w: cw, h: 2.1, fill: { color: C.card }, line: { color: C.line, width: 0.75 }, rectRadius: 0.12, shadow: shadow() });
        iconDisc(sl, ic[k], x + cw - 0.72, y + 0.2, 0.52, C.rose);
        sl.addText(t, T({ x: x + 0.15, y: y + 0.85, w: cw - 0.3, h: 0.6, fontSize: 12, bold: true }));
        sl.addText(d, T({ x: x + 0.15, y: y + 1.45, w: cw - 0.3, h: 0.55, fontSize: 10, color: C.muted }));
      });
    }

    else if (s.kind === 'grid') {
      title(sl, s.title, s.subtitle, 1.0, 9);
      const ic = [I.coins, I.camera, I.chart, I.users];
      const cw = 4.4, ch = 1.1;
      s.items.forEach(([t, d], k) => {
        const col = k % 2, row = Math.floor(k / 2);
        const x = W - M - (col + 1) * cw - col * 0.2, y = 2.55 + row * (ch + 0.2);
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w: cw, h: ch, fill: { color: C.card }, line: { color: C.line, width: 0.75 }, rectRadius: 0.12, shadow: shadow() });
        iconDisc(sl, ic[k], x + cw - 0.8, y + 0.29, 0.55);
        sl.addText(t, T({ x: x + 0.2, y: y + 0.17, w: cw - 1.15, h: 0.38, fontSize: 14, bold: true, color: C.gold }));
        sl.addText(d, T({ x: x + 0.2, y: y + 0.58, w: cw - 1.15, h: 0.4, fontSize: 11, color: C.muted }));
      });
    }

    else if (s.kind === 'feature' || s.kind === 'role') {
      const right = i % 2 === 0; // alternate image side
      const textW = 4.9;
      const tx = right ? M : W - M - textW;   // text column
      const ix = right ? M + textW + 0.35 : M;  // image column
      const iw = W - 2 * M - textW - 0.35;
      if (s.kind === 'role') sl.addText(s.role, T({ x: tx, y: 1.0, w: textW, h: 0.32, fontSize: 12, bold: true, color: C.teal }));
      sl.addText(s.title, T({ x: tx, y: s.kind === 'role' ? 1.32 : 1.0, w: textW, h: 0.75, fontSize: 21, bold: true, fit: 'shrink', valign: 'middle' }));
      let by = s.kind === 'role' ? 2.2 : 1.85;
      if (s.subtitle) { sl.addText(s.subtitle, T({ x: tx, y: by, w: textW, h: 0.5, fontSize: 11.5, color: C.muted })); by += 0.6; }
      bullets(sl, s.bullets, tx, by, textW, H - by - 0.35, s.bullets.length > 4 ? 11.5 : 12.5);
      if (s.image) {
        const im = await img(s.image);
        const r = framed(sl, im, ix, 1.2, iw, 3.9);
        // re-center vertically/horizontally within column
      } else {
        // trading room visual: icon composition
        const cx = ix + iw / 2;
        sl.addShape(pres.shapes.OVAL, { x: cx - 1.35, y: 1.45, w: 2.7, h: 2.7, fill: { color: C.card }, line: { color: C.gold2, width: 1.5 }, shadow: shadow() });
        sl.addImage({ data: I.chart, x: cx - 0.8, y: 2.0, w: 1.6, h: 1.6 });
        [['مظنه لحظه‌ای', I.globe], ['فی محرمانه', I.shield], ['برند شما', I.gem]].forEach(([t, ic], k) => {
          const x = ix + k * (iw / 3);
          iconDisc(sl, ic, x + iw / 6 - 0.26, 4.3, 0.5);
          sl.addText(t, T({ x, y: 4.85, w: iw / 3, h: 0.3, fontSize: 10.5, align: 'center', color: C.muted }));
        });
      }
    }

    else if (s.kind === 'flow') {
      title(sl, s.title, s.subtitle, 1.0, 9);
      const n = s.items.length, bw = 1.85, gap = 0.52, y = 2.55;
      const totalW = n * bw + (n - 1) * gap, x0 = (W - totalW) / 2;
      s.items.forEach(([t, d], k) => {
        const x = x0 + totalW - (k + 1) * bw - k * gap; // right to left
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w: bw, h: 1.25, fill: { color: C.card }, line: { color: C.gold2, width: 1 }, rectRadius: 0.12, shadow: shadow() });
        sl.addText((k + 1).toLocaleString('fa-IR'), T({ x, y: y + 0.12, w: bw - 0.18, h: 0.35, fontSize: 16, bold: true, color: C.gold }));
        sl.addText(t, T({ x: x + 0.12, y: y + 0.48, w: bw - 0.3, h: 0.4, fontSize: 12, bold: true }));
        sl.addText(d, T({ x: x + 0.12, y: y + 0.86, w: bw - 0.3, h: 0.3, fontSize: 10, color: C.muted }));
        if (k < n - 1) sl.addImage({ data: I.arrow, x: x - gap + 0.1, y: y + 0.47, w: gap - 0.2, h: gap - 0.2 });
      });
      sl.addText('به‌جای:', T({ x: W - M - 1, y: 4.3, w: 1, h: 0.35, fontSize: 11, color: C.muted, valign: 'middle' }));
      let cx = W - M - 1.1;
      s.replaces.forEach((r) => {
        const w = Math.max(0.95, r.length * 0.068 + 0.3);
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: cx - w, y: 4.3, w, h: 0.35, fill: { color: C.card2 }, line: { color: C.line, width: 0.75 }, rectRadius: 0.17 });
        sl.addText(r, T({ x: cx - w, y: 4.3, w, h: 0.35, fontSize: 8.5, color: C.muted, align: 'center', valign: 'middle', strike: 'sngStrike' }));
        cx -= w + 0.1;
      });
    }

    else if (s.kind === 'chain') {
      title(sl, s.title, s.subtitle, 1.0, 9);
      const ic = [I.vault, I.mobile, I.shield];
      const bw = 2.7, gap = 0.35, y = 2.6, x0 = (W - (3 * bw + 2 * gap)) / 2;
      s.items.forEach(([t, d], k) => {
        const x = x0 + (2 - k) * (bw + gap);
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x, y, w: bw, h: 2.1, fill: { color: C.card }, line: { color: k === 1 ? C.teal : C.gold2, width: 1.25 }, rectRadius: 0.14, shadow: shadow() });
        iconDisc(sl, ic[k], x + bw - 0.9, y + 0.25, 0.65, k === 1 ? C.teal : C.gold2);
        sl.addText(`لایه ${(k + 1).toLocaleString('fa-IR')}`, T({ x: x + 0.2, y: y + 0.3, w: 1.4, h: 0.3, fontSize: 11, color: C.gold, align: 'left', rtlMode: true }));
        sl.addText(t, T({ x: x + 0.2, y: y + 1.0, w: bw - 0.4, h: 0.4, fontSize: 15, bold: true }));
        sl.addText(d, T({ x: x + 0.2, y: y + 1.42, w: bw - 0.4, h: 0.55, fontSize: 11, color: C.muted }));
      });
    }

    else if (s.kind === 'panels') {
      title(sl, s.title, s.subtitle, 1.0, 9);
      const n = s.items.length, cw = 2.85, gap = 0.2, x0 = (W - (n * cw + (n - 1) * gap)) / 2;
      for (let k = 0; k < n; k++) {
        const [t, im] = s.items[k];
        const x = x0 + (n - 1 - k) * (cw + gap);
        const pic = await img(im);
        const r = framed(sl, pic, x, 2.4, cw, 2.2);
        sl.addText(t, T({ x, y: 2.4 + r.h + 0.14, w: cw, h: 0.32, fontSize: 12.5, bold: true, color: C.gold, align: 'center' }));
      }
    }

    else if (s.kind === 'cta') {
      // medal
      sl.addShape(pres.shapes.OVAL, { x: M + 0.35, y: 1.35, w: 2.6, h: 2.6, fill: { color: C.gold }, line: { color: 'FFE39A', width: 4 }, shadow: shadow() });
      sl.addShape(pres.shapes.OVAL, { x: M + 0.55, y: 1.55, w: 2.2, h: 2.2, fill: { type: 'none' }, line: { color: '8A5A08', width: 1, dashType: 'dash' } });
      sl.addText('۱', T({ x: M + 0.35, y: 1.75, w: 2.6, h: 1.2, fontSize: 72, bold: true, color: '241803', align: 'center', valign: 'middle' }));
      sl.addText('ماه رایگان', T({ x: M + 0.35, y: 2.85, w: 2.6, h: 0.5, fontSize: 18, bold: true, color: '241803', align: 'center' }));
      sl.addText(s.title, T({ x: 3.9, y: 1.1, w: 5.6, h: 0.7, fontSize: 26, bold: true, color: C.gold }));
      sl.addText(s.subtitle, T({ x: 3.9, y: 1.85, w: 5.6, h: 0.6, fontSize: 13, color: C.muted }));
      const ic = [I.phone, I.globe, I.pin];
      s.items.forEach(([t, v], k) => {
        const y = 2.65 + k * 0.72;
        sl.addShape(pres.shapes.ROUNDED_RECTANGLE, { x: 3.9, y, w: 5.6, h: 0.6, fill: { color: C.card }, line: { color: C.line, width: 0.75 }, rectRadius: 0.1 });
        iconDisc(sl, ic[k], 9.5 - 0.55, y + 0.08, 0.44);
        sl.addText(t, T({ x: 7.3, y, w: 1.55, h: 0.6, fontSize: 10.5, color: C.muted, valign: 'middle' }));
        sl.addText(v, T({ x: 4.05, y, w: 3.25, h: 0.6, fontSize: 12.5, bold: true, valign: 'middle', align: k === 2 ? 'right' : 'left', rtlMode: k === 2 }));
      });
    }
  }
  fs.mkdirSync(path.dirname(OUT), { recursive: true });
  await pres.writeFile({ fileName: OUT });
  console.log('wrote', OUT);
})();
