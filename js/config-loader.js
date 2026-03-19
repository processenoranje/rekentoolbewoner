// Tiny deep merge (no dependencies)
function deepMerge(target, source) {
  if (typeof target !== 'object' || target === null) return source;
  if (typeof source !== 'object' || source === null) return target;

  const out = Array.isArray(target) ? target.slice() : { ...target };
  for (const key of Object.keys(source)) {
    const sv = source[key];
    const tv = out[key];
    out[key] = (typeof sv === 'object' && sv && !Array.isArray(sv))
      ? deepMerge(typeof tv === 'object' && tv ? tv : {}, sv)
      : sv;
  }
  return out;
}

async function fetchJson(url) {
  const res = await fetch(url, { cache: 'no-store' }); // avoid stale brand issues
  if (!res.ok) throw new Error(`Failed to fetch ${url}: ${res.status}`);
  return res.json();
}

async function loadConfig() {
  try {
    const defaults = await fetchJson('/config/defaults.config.json');

    let client = {};
    try {
      client = await fetchJson('/config/client.config.json');
    } catch (_) {
      // Client file missing/corrupt — we proceed with defaults
      console.warn('No or invalid client.config.json; using defaults only.');
    }

    // Optional: simple version guard & migration hook
    if (client.configVersion && client.configVersion !== defaults.configVersion) {
      console.warn('Config version mismatch; consider migration.');
      // migrateConfig(client) // if you need
    }

    const config = deepMerge(defaults, client);
    window.__APP_CONFIG__ = config; // make globally available early
    applyBranding(config);
    applyTheme(config);
    return config;
  } catch (e) {
    console.error('Config load failed, using hardcoded safe defaults', e);
    const fallback = {
      brand: { siteTitle: 'Rekentool', logo: '/afbeeldingen/logo.png', favicon: '/afbeeldingen/favicon.ico' },
      theme: {
        colors: { primary: '#0A84FF', 'primary-contrast': '#FFFFFF', background: '#FFFFFF', 'background-column': '#ffffff', surface: '#F5F7FA', text: '#0F172A' },
        radius: '8px'
      },
      ui: { showHelp: true, language: 'nl' }
    };
    window.__APP_CONFIG__ = fallback;
    applyBranding(fallback);
    applyTheme(fallback);
    return fallback;
  }
}

function applyBranding(cfg) {
  const title = (cfg.brand && cfg.brand.siteTitle) || 'Site';
  const logo = (cfg.brand && cfg.brand.logo) || '/assets/logo-default.svg';
  const favicon = (cfg.brand && cfg.brand.favicon) || '/assets/favicon.ico';

  document.title = title;
  const titleEl = document.getElementById('brand-title');
  if (titleEl) titleEl.textContent = title;

  const logoEl = document.getElementById('brand-logo');
  if (logoEl) logoEl.src = logo;

  const fav = document.getElementById('favicon');
  if (fav) fav.href = favicon;
}

function applyTheme(cfg) {
  const root = document.documentElement;
  const c = cfg.theme?.colors || {};
  const radius = cfg.theme?.radius;

  if (c.primary) root.style.setProperty('--color-primary', c.primary);
  if (c['primary-contrast']) root.style.setProperty('--color-primary-contrast', c['primary-contrast']);
  if (c.background) root.style.setProperty('--color-bg', c.background);
  if (c['background-column']) root.style.setProperty('--color-bg-column', c['background-column']);
  if (c['background-footer']) root.style.setProperty('--color-bg-footer', c['background-footer']);
  if (c['background-nav']) root.style.setProperty('--color-bg-nav', c['background-nav']);
  if (c.surface) root.style.setProperty('--color-surface', c.surface);
  if (c.text) root.style.setProperty('--color-text', c.text);
  if (c['text-nav']) root.style.setProperty('--color-text-nav', c['text-nav']);
  if (radius) root.style.setProperty('--radius', radius);
}

// Load as early as possible so the first paint is branded
window.addEventListener('DOMContentLoaded', () => {
  loadConfig().catch(console.error);
});