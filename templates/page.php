<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo esc($config['site']['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo esc($config['site']['description']); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo esc(asset_url('favicon.ico', $config)); ?>">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" integrity="sha512-F2E+YYE1gkt0T5TVajAslgDfTEUQKtlu4ralVq78ViNxhKXQLrgQLLie8u1tVdG2vWnB3ute4hcdbiBtvJQh0g==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-thumbnail.min.css" integrity="sha512-GRxDpj/bx6/I4y6h2LE5rbGaqRcbTu4dYhaTewlS8Nh9hm/akYprvOTZD7GR+FRCALiKfe8u1gjvWEEGEtoR6g==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-zoom.min.css" integrity="sha512-vIrTyLijDDcUJrQGs1jduUCSVa3+A2DaWpVfNyj4lmXkqURVQJ8LL62nebC388QV3P4yFBSt/ViDX8LRW0U6uw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" integrity="sha512-mQ77VzAakzdpWdgfL/lM1ksNy89uFgibRQANsNneSTMD/bj0Y/8+94XMwYhnbzx8eki2hrbPpDm0vD0CiT2lcg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" integrity="sha512-6ZCLMiYwTeli2rVh3XAPxy3YoR5fVxGdH/pz+KMCzRY2M65Emgkw00Yqmhh8qLGeYQ3LbVZGdmOX9KUjSKr0TA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?php echo esc(asset_url('assets/css/app.css', $config)); ?>">
  </head>
  <body>
    <div class="page-shell">
      <header class="hero">
        <div class="hero__overlay"></div>
        <div class="hero__content">
          <div class="hero__eyebrow">Private Photo Atlas</div>
          <div class="hero__topline">
            <div>
              <h1><?php echo esc($config['site']['title']); ?></h1>
              <p class="hero__description"><?php echo esc($config['site']['description']); ?></p>
            </div>
            <?php if ($isLoggedIn) { ?>
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo esc($csrfToken); ?>">
                <button class="button button--ghost" type="submit" name="btnlogout">Logout</button>
              </form>
            <?php } ?>
          </div>

          <?php if ($isLoggedIn) { ?>
            <div class="hero__stats">
              <div class="stat-pill">
                <span class="stat-pill__value"><?php echo (int) $galleryPayload['stats']['imageCount']; ?></span>
                <span class="stat-pill__label">Photos</span>
              </div>
              <div class="stat-pill">
                <span class="stat-pill__value"><?php echo (int) $galleryPayload['stats']['mappedCount']; ?></span>
                <span class="stat-pill__label">Mapped</span>
              </div>
              <?php if (!empty($galleryPayload['issues']['count'])) { ?>
                <div class="stat-pill">
                  <span class="stat-pill__value"><?php echo (int) $galleryPayload['issues']['count']; ?></span>
                  <span class="stat-pill__label">Warnings</span>
                </div>
              <?php } ?>
              <?php if (!empty($galleryPayload['stats']['timespanLabel'])) { ?>
                <div class="stat-pill stat-pill--wide">
                  <span class="stat-pill__value"><?php echo esc((string) $galleryPayload['stats']['timespanLabel']); ?></span>
                  <span class="stat-pill__label">Timeline</span>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </header>

      <main class="main-content">
        <?php if (!$isLoggedIn) { ?>
          <section class="login-panel">
            <div>
              <p class="section-label">Access</p>
              <h2>Open the gallery</h2>
              <p class="section-copy">A single access code keeps the album private while staying easy to share with friends and family.</p>
            </div>

            <form method="post" class="login-form">
              <input type="hidden" name="csrf_token" value="<?php echo esc($csrfToken); ?>">
              <label class="field">
                <span>Access code</span>
                <input type="password" name="accesscode" placeholder="Enter access code" autocomplete="current-password" required>
              </label>
              <button class="button" type="submit" name="btnlogin">Login</button>
            </form>

            <?php if ($errorMessage !== '') { ?>
              <p class="alert"><?php echo esc($errorMessage); ?></p>
            <?php } ?>
          </section>
        <?php } else { ?>
          <section class="content-grid">
            <div class="panel panel--gallery">
              <div class="panel__header">
                <div>
                  <p class="section-label">Gallery</p>
                  <h2>Moments in sequence</h2>
                </div>
              </div>

              <?php if ($galleryPayload['items'] === []) { ?>
                <div class="empty-state">
                  <h3>No images found</h3>
                  <p>Add files to the `img` directory to populate the gallery.</p>
                </div>
              <?php } else { ?>
                <?php if (!empty($galleryPayload['issues']['count'])) { ?>
                  <p class="notice">
                    <?php echo (int) $galleryPayload['issues']['count']; ?> file(s) had metadata or thumbnail warnings.
                    <?php if (!empty($galleryPayload['issues']['samples'][0]['reason'])) { ?>
                      First issue: <?php echo esc((string) $galleryPayload['issues']['samples'][0]['reason']); ?>
                    <?php } ?>
                  </p>
                <?php } ?>
                <ul id="lightgallery" class="gallery-grid"></ul>
              <?php } ?>
            </div>

            <div class="panel panel--map" id="map-panel">
              <div class="panel__header">
                <div>
                  <p class="section-label">Map</p>
                  <h2>Route of the trip</h2>
                </div>
              </div>
              <div id="map" class="map-canvas" aria-label="Photo map"></div>
            </div>
          </section>

          <script id="gallery-data" type="application/json"><?php echo json_encode($galleryPayload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.umd.min.js" integrity="sha512-VOQBxCIgNssJrB8+irZF7L8MvfpAshegc36C3H5QD7vmibXM4uCNaqJIaSNatD2z2ZQQJSx0k+q+m+xsSPp4Xw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js" integrity="sha512-dc8xJSGs0ib9uo0fLT/v4wp2LG7+4OSzc+UpFiIKiv6QP/e4hZH/S8manUCTtO3tNVGzcje8uJjSdL+NH29blQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js" integrity="sha512-OUF2jbRheQR5yXPCvXN71udWa5cvwPf+shcXM+5GrW1vtNurTn7az8LCP3hS50gm17ULXdh3cdkhiPa0Qqyczw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" integrity="sha512-OFs3W4DIZ5ZkrDhBFtsCP6JXtMEDGmhl0QPlmWYBJay40TT1n3gt2Xuw8Pf/iezgW9CdabjkNChRqozl/YADmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
          <script src="<?php echo esc(asset_url('assets/js/app.js', $config)); ?>"></script>
        <?php } ?>
      </main>

      <footer class="site-footer">
        by <a href="mailto:<?php echo esc($config['site']['footer_email']); ?>"><?php echo esc($config['site']['footer_email']); ?></a>
        <span><?php echo esc($config['site']['footer_year']); ?></span>
      </footer>
    </div>
  </body>
</html>
