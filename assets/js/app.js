document.addEventListener('DOMContentLoaded', () => {
  const galleryDataElement = document.getElementById('gallery-data');
  const galleryElement = document.getElementById('lightgallery');
  const mapElement = document.getElementById('map');
  const mapPanel = document.getElementById('map-panel');

  if (!galleryDataElement || !galleryElement || !mapElement || !mapPanel) {
    return;
  }

  const payload = JSON.parse(galleryDataElement.textContent || '{}');
  const items = Array.isArray(payload.items) ? payload.items : [];
  const mapConfig = payload.map || {};

  renderGallery(items, galleryElement);
  initializeLightGallery(galleryElement);
  initializeMap(items, mapConfig, mapElement, mapPanel);
});

function renderGallery(items, galleryElement) {
  const fragment = document.createDocumentFragment();

  items.forEach((item) => {
    const listItem = document.createElement('li');
    listItem.className = 'gallery-card';
    listItem.dataset.src = item.filename;
    listItem.dataset.responsive = `${item.filename} 1600`;
    listItem.dataset.subHtml = item.takenDateLabel || '';

    const link = document.createElement('a');
    link.className = 'gallery-card__link';
    link.href = item.filename;

    const image = document.createElement('img');
    image.className = 'gallery-card__image';
    image.src = item.thumbnail || item.filename;
    image.alt = item.takenDateLabel || 'Gallery image';
    image.loading = 'lazy';
    image.decoding = 'async';

    const meta = document.createElement('div');
    meta.className = 'gallery-card__meta';
    meta.innerHTML = `
      <span>${escapeHtml(item.takenDateLabel || 'Undated')}</span>
      <span>${item.lat !== null && item.lng !== null ? 'Mapped' : 'Gallery'}</span>
    `;

    link.append(image, meta);
    listItem.appendChild(link);
    fragment.appendChild(listItem);
  });

  galleryElement.appendChild(fragment);
}

function initializeLightGallery(galleryElement) {
  if (typeof lightGallery !== 'function') {
    return;
  }

  const plugins = [window.lgThumbnail, window.lgZoom].filter(Boolean);

  lightGallery(galleryElement, {
    animateThumb: false,
    download: false,
    share: false,
    actualSize: false,
    zoomFromOrigin: false,
    thumbnail: true,
    plugins,
  });
}

function initializeMap(items, mapConfig, mapElement, mapPanel) {
  const mappedItems = items.filter((item) => item.lat !== null && item.lng !== null);

  if (mappedItems.length === 0 || typeof L === 'undefined') {
    mapPanel.hidden = true;
    return;
  }

  const defaultCenter = Array.isArray(mapConfig.default_center) ? mapConfig.default_center : [53.03, 8.86];
  const defaultZoom = Number.isFinite(mapConfig.default_zoom) ? mapConfig.default_zoom : 10;
  const tileUrl = mapConfig.tile_url || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
  const tileAttribution = mapConfig.tile_attribution || '';

  const map = L.map(mapElement).setView(defaultCenter, defaultZoom);
  L.tileLayer(tileUrl, {
    maxZoom: 19,
    attribution: tileAttribution,
  }).addTo(map);

  const markers = L.markerClusterGroup();
  const routePoints = [];

  mappedItems.forEach((item) => {
    const marker = L.marker([item.lat, item.lng]).bindPopup(
      `<img src="${escapeHtml(item.thumbnail || item.filename)}" alt="" style="width: 220px; border-radius: 12px;">`
    );
    markers.addLayer(marker);
    routePoints.push([item.lat, item.lng]);
  });

  map.addLayer(markers);

  if (routePoints.length > 1) {
    L.polyline(routePoints, {
      color: '#d36b38',
      weight: 3,
      opacity: 0.85,
    }).addTo(map);
  }

  map.fitBounds(L.latLngBounds(routePoints), {
    padding: [30, 30],
  });
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
