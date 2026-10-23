#!/usr/bin/env node
/*
 * Generează public/css/theme-neon-glass-recolor.css: fiecare regulă CSS care folosește galben
 * (din public/css/*.css și din blocurile <style> ale view-urilor) e redeclarată sub
 * html[data-theme="neon-glass"], cu galbenul înlocuit de nuanțe neon cyan.
 * Rulează din nou după ce adaugi stiluri cu galben: node scripts/build-neon-recolor.mjs
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const root = new URL('..', import.meta.url).pathname;
const output = join(root, 'public/css/theme-neon-glass-recolor.css');
const SCOPE = 'html[data-theme="neon-glass"]';
const SKIP_FILES = new Set(['theme-neon-glass.css', 'theme-neon-glass-recolor.css']);

const RGB = {
  main: [0, 229, 255],
  deep: [0, 200, 240],
  dark: [0, 184, 212],
  light: [125, 249, 255],
};
const HEX = {
  ffee00: 'main', fe0: 'main', fff000: 'main', ffe600: 'main',
  facc15: 'deep', eab308: 'deep', ffd700: 'deep',
  e6d600: 'dark', e6d800: 'dark', f5e100: 'dark', f7d900: 'dark', f5e000: 'dark',
  fde047: 'light', fff35c: 'light',
};
const RGB_IN = {
  '255,238,0': 'main', '250,204,21': 'deep', '234,179,8': 'deep', '255,215,0': 'deep',
  '230,216,0': 'dark', '253,224,71': 'light', '255,247,120': 'light',
};

const hexOf = (k) => '#' + RGB[k].map((n) => n.toString(16).padStart(2, '0')).join('');

export function recolor(text) {
  return text
    .replace(/#([0-9a-f]{6}|[0-9a-f]{3})\b/gi, (m, h) => {
      const k = HEX[h.toLowerCase()];
      return k ? hexOf(k) : m;
    })
    .replace(/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*[\d.]+\s*)?\)/gi, (m, r, g, b, a) => {
      const k = RGB_IN[`${r},${g},${b}`];
      if (!k) return m;
      const [nr, ng, nb] = RGB[k];
      return a ? `rgba(${nr}, ${ng}, ${nb}${a.replace(/\s+/g, ' ')})` : `rgb(${nr}, ${ng}, ${nb})`;
    });
}

function stripComments(css) {
  return css.replace(/\/\*[\s\S]*?\*\//g, '');
}

function findClose(css, open) {
  let depth = 0;
  let quote = null;
  for (let i = open; i < css.length; i++) {
    const c = css[i];
    if (quote) { if (c === quote && css[i - 1] !== '\\') quote = null; continue; }
    if (c === '"' || c === "'") { quote = c; continue; }
    if (c === '{') depth++;
    else if (c === '}') { depth--; if (depth === 0) return i; }
  }
  return css.length - 1;
}

function parse(css) {
  const items = [];
  let i = 0;
  while (i < css.length) {
    const open = css.indexOf('{', i);
    const semi = css.indexOf(';', i);
    if (open === -1) break;
    if (semi !== -1 && semi < open && css.slice(i, semi).trim().startsWith('@')) { i = semi + 1; continue; }
    const prelude = css.slice(i, open).trim();
    const close = findClose(css, open);
    const body = css.slice(open + 1, close);
    if (/^@(media|supports|container)\b/i.test(prelude)) items.push({ group: prelude, items: parse(body) });
    else if (!prelude.startsWith('@')) items.push({ selector: prelude, body });
    i = close + 1;
  }
  return items;
}

function splitTop(str, sep) {
  const parts = [];
  let depth = 0;
  let quote = null;
  let start = 0;
  for (let i = 0; i < str.length; i++) {
    const c = str[i];
    if (quote) { if (c === quote) quote = null; continue; }
    if (c === '"' || c === "'") quote = c;
    else if (c === '(' || c === '[') depth++;
    else if (c === ')' || c === ']') depth--;
    else if (c === sep && depth === 0) { parts.push(str.slice(start, i)); start = i + 1; }
  }
  parts.push(str.slice(start));
  return parts;
}

function scopeSelector(selector) {
  const out = [];
  for (let s of splitTop(selector, ',')) {
    s = s.trim().replace(/\s+/g, ' ');
    if (!s || s.includes('data-theme') || s.startsWith(':root') || s.includes('BLADE')) continue;
    if (/^html\b/.test(s)) out.push(SCOPE + s.slice(4));
    else out.push(`${SCOPE} ${s}`);
  }
  return out.join(',\n');
}

function emit(items, indent = '') {
  let css = '';
  for (const item of items) {
    if (item.group) {
      const inner = emit(item.items, indent + '  ');
      if (inner) css += `${indent}${item.group} {\n${inner}${indent}}\n`;
      continue;
    }
    /* Selectorii care țintesc galbenul din style="" trebuie să prindă și atributul recolorat de theme-neon-glass.js */
    const recoloredSelector = recolor(item.selector);
    const selectorChanged = recoloredSelector !== item.selector;
    const decls = splitTop(item.body, ';')
      .map((d) => d.trim())
      .filter((d) => d && d.includes(':') && !d.includes('BLADE'))
      .map((d) => [d, recolor(d)])
      .filter(([before, after]) => selectorChanged || before !== after)
      .map(([, after]) => after);
    if (!decls.length) continue;
    const selector = scopeSelector(recoloredSelector);
    if (!selector) continue;
    css += `${indent}${selector.split('\n').join('\n' + indent)} {\n${decls.map((d) => `${indent}  ${d};`).join('\n')}\n${indent}}\n`;
  }
  return css;
}

function walk(dir, ext, list = []) {
  for (const name of readdirSync(dir)) {
    const p = join(dir, name);
    if (statSync(p).isDirectory()) walk(p, ext, list);
    else if (p.endsWith(ext)) list.push(p);
  }
  return list;
}

const sources = [];
for (const file of readdirSync(join(root, 'public/css')).sort()) {
  if (!file.endsWith('.css') || file.endsWith('.min.css') || SKIP_FILES.has(file)) continue;
  sources.push([`public/css/${file}`, readFileSync(join(root, 'public/css', file), 'utf8')]);
}
for (const file of walk(join(root, 'resources/views'), '.blade.php').sort()) {
  const html = readFileSync(file, 'utf8');
  const blocks = [...html.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/gi)].map((m) => m[1]);
  if (blocks.length) sources.push([relative(root, file), blocks.join('\n')]);
}

let result = '/* Generat de scripts/build-neon-recolor.mjs — nu edita manual. */\n';
let rules = 0;
for (const [name, raw] of sources) {
  const css = stripComments(raw).replace(/\{\{[\s\S]*?\}\}|\{!![\s\S]*?!!\}/g, 'BLADE').replace(/@(php|endphp|if|endif|foreach|endforeach)\b[^\n]*/g, '');
  const block = emit(parse(css));
  if (!block) continue;
  rules += (block.match(/\{\n/g) || []).length;
  result += `\n/* ${name} */\n${block}`;
}

writeFileSync(output, result);
console.log(`${relative(root, output)}: ${rules} reguli din ${sources.length} surse`);
