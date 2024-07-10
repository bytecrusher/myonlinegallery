<?php
// Define your shared username and password.
$accesscode = 'testaccess';
$pagetitlename = 'My Online Gallery';
$shortDescription = "Short description of your event.";
$errormessage = "";

// If Button was pressed.
if (isset($_POST['btnlogin'])) {
  $errormessage = "";
  // Check if the entered accesscode is valid.
  // TODO If Accesscode is wrong, lock a few seconds (for prevent brute force attack).
  // TODO Rename the $_POST['accesscode'] var name to a random name, for every session, for prevent for hijacking the post url command.
  if (isset($_POST['accesscode']) && $_POST['accesscode'] == $accesscode) {
    session_start();
    $_SESSION['loggedIn'] = true;
  } else {
    $errormessage = "Wrong Access Code";
  }
} else if (isset($_POST['btnlogout'])) {
  // Logged out
  if (isset($_SESSION)) {
    unset($_SESSION['loggedIn']);
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo ($pagetitlename); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lightgallery.min.css" integrity="sha512-F2E+YYE1gkt0T5TVajAslgDfTEUQKtlu4ralVq78ViNxhKXQLrgQLLie8u1tVdG2vWnB3ute4hcdbiBtvJQh0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-zoom.min.css" integrity="sha512-vIrTyLijDDcUJrQGs1jduUCSVa3+A2DaWpVfNyj4lmXkqURVQJ8LL62nebC388QV3P4yFBSt/ViDX8LRW0U6uw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/css/lg-thumbnail.min.css" integrity="sha512-GRxDpj/bx6/I4y6h2LE5rbGaqRcbTu4dYhaTewlS8Nh9hm/akYprvOTZD7GR+FRCALiKfe8u1gjvWEEGEtoR6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384 Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" integrity="sha512-mQ77VzAakzdpWdgfL/lM1ksNy89uFgibRQANsNneSTMD/bj0Y/8+94XMwYhnbzx8eki2hrbPpDm0vD0CiT2lcg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" integrity="sha512-6ZCLMiYwTeli2rVh3XAPxy3YoR5fVxGdH/pz+KMCzRY2M65Emgkw00Yqmhh8qLGeYQ3LbVZGdmOX9KUjSKr0TA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="styles/my.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"   integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="   crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/lightgallery.umd.min.js" integrity="sha512-VOQBxCIgNssJrB8+irZF7L8MvfpAshegc36C3H5QD7vmibXM4uCNaqJIaSNatD2z2ZQQJSx0k+q+m+xsSPp4Xw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/thumbnail/lg-thumbnail.umd.min.js" integrity="sha512-dc8xJSGs0ib9uo0fLT/v4wp2LG7+4OSzc+UpFiIKiv6QP/e4hZH/S8manUCTtO3tNVGzcje8uJjSdL+NH29blQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.2/plugins/zoom/lg-zoom.umd.min.js" integrity="sha512-OUF2jbRheQR5yXPCvXN71udWa5cvwPf+shcXM+5GrW1vtNurTn7az8LCP3hS50gm17ULXdh3cdkhiPa0Qqyczw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" integrity="sha512-OFs3W4DIZ5ZkrDhBFtsCP6JXtMEDGmhl0QPlmWYBJay40TT1n3gt2Xuw8Pf/iezgW9CdabjkNChRqozl/YADmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </head>

  <body class="home">
    <div class="container mt-4">
      <div class="jumbotron">
        <div class="d-flex">
          <h1><?php echo ($pagetitlename); ?></h1>
          <?php
          if (isset($_SESSION['loggedIn'])) {
          ?>
            <form name="logout_mit_php" method="post" class="ml-auto">
              <input class="btn btn-primary mb-2" type="submit" name="btnlogout" value="Logout">
            </form>
          <?php
          }
          ?>
        </div>
        <p><?php echo ($shortDescription); ?></p>
      </div>
    </div>
    <?php
    if (!isset($_SESSION['loggedIn'])) {
    ?>
      <div class="container wrapper mx-auto mt-5">
        <h2 class="text-center mb-4">Login to <?php echo ($pagetitlename); ?></h2>
        <form class="form-inline d-flex justify-content-center" name="login" method="post">
          <div class="form-group mx-sm-3 mb-2">
            <label for="accesscode">Enter Access Code:</label>
          </div>
          <div class="form-group mx-sm-3 mb-2">
            <input type="text" class="form-control" id="accesscode" placeholder="accesscode" name="accesscode" required>
          </div>
          <button type="submit" name="btnlogin" class="btn btn-primary mb-2">Login</button>
        </form>
        <div class="d-flex justify-content-center text-danger bg-dark" id="errormessage"><?php echo ($errormessage); ?></div>
      </div>
    <?php
    } else {
    ?>
      <div class="mycontainer">
        <div class="mygallery mb-5">
          <ul id="lightgallery" class="list-unstyled row justify-content-center">
          </ul>
        </div>
      </div>
      <div class="container">
        <div id="map"></div>
      </div>
      <script>
        var map = L.map('map').setView([53.03, 8.86], 10);
        var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        var clustermarkers = L.markerClusterGroup();
      </script>
    <?php
      // Auto collect pictures:
      $path    = './img';
      $files = scandir($path);
      $files = array_diff(scandir($path), array('.', '..'));
      $array1 = array();
      $array2 = array();
      $oldlat = 53.03;
      $oldlng = 8.86;
      foreach ($files as &$value) {
        //image file path
        $imageURL = "img/" . $value;
        $imgLocation = null;

        //get location of image
        if (($imageURL != "img/.DS_Store") && (!str_ends_with($imageURL, '.mov'))) {
          $imgLocation = get_image_location($imageURL);
          $imgLat = $imgLng = $takenDate = null;

          if (($imgLocation != false)) {
            //latitude & longitude
            $imgLat = $imgLocation['latitude'];
            $imgLng = $imgLocation['longitude'];
            $takenDate = $imgLocation['takenDate']->format('Y/m/d H:i');
          }
          $array1 = array("filename" => $imageURL, "Lat" => $imgLat, "Lng" => $imgLng, "takenDate" => $takenDate);
          array_push($array2, $array1);
        }
      }
      $columns = array_column($array2, 'takenDate');
      array_multisort($columns, SORT_ASC, $array2);

      foreach ($array2 as $key=>&$value2) {
        if ($value2['takenDate'] != null) {
          ?>
          <script>
            var marker = L.marker([<?php echo $value2['Lat'] ?>, <?php echo $value2['Lng'] ?>]).bindPopup('<img class="img-responsive" src="<?php echo  $value2['filename'] ?>" style="width:300px"><!--?php echo $key ?-->');
            clustermarkers.addLayer(marker);
            map.addLayer(clustermarkers);

            var polyline = L.polyline([ [<?php echo $oldlat ?>, <?php echo $oldlng ?>], [<?php echo $value2['Lat'] ?>, <?php echo $value2['Lng'] ?>] ]);
            //.addTo(map);
            map.addLayer(polyline);

            $(document).ready(function(){
              $('#lightgallery').append('<li class="col-xs-6 col-sm-4 col-md-3" data-responsive="<?php echo $value2['filename'] ?> 800" data-src="<?php echo $value2['filename'] ?>" data-sub-html=""> <a href=""><img class="img-responsive" src="<?php echo $value2['filename'] ?>"></a></li>');
            });
          </script>
          <?php
          $oldlat = $value2['Lat'];
          $oldlng = $value2['Lng'];
        }
      }
    }
    ?>

    <?PHP
    /**
     * get_image_location
     * Returns an array of latitude and longitude from the Image file
     * @param $image file path
     * @return multitype:array|boolean
     */
    function get_image_location($image = ''){
      $exif = exif_read_data($image, 0, true);
      if($exif && isset($exif['GPS'])){
          $GPSLatitudeRef = $exif['GPS']['GPSLatitudeRef'];
          $GPSLatitude    = $exif['GPS']['GPSLatitude'];
          $GPSLongitudeRef= $exif['GPS']['GPSLongitudeRef'];
          $GPSLongitude   = $exif['GPS']['GPSLongitude'];
          
          $lat_degrees = count($GPSLatitude) > 0 ? gps2Num($GPSLatitude[0]) : 0;
          $lat_minutes = count($GPSLatitude) > 1 ? gps2Num($GPSLatitude[1]) : 0;
          $lat_seconds = count($GPSLatitude) > 2 ? gps2Num($GPSLatitude[2]) : 0;
          
          $lon_degrees = count($GPSLongitude) > 0 ? gps2Num($GPSLongitude[0]) : 0;
          $lon_minutes = count($GPSLongitude) > 1 ? gps2Num($GPSLongitude[1]) : 0;
          $lon_seconds = count($GPSLongitude) > 2 ? gps2Num($GPSLongitude[2]) : 0;
          
          $lat_direction = ($GPSLatitudeRef == 'W' or $GPSLatitudeRef == 'S') ? -1 : 1;
          $lon_direction = ($GPSLongitudeRef == 'W' or $GPSLongitudeRef == 'S') ? -1 : 1;
          
          $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60*60)));
          $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60*60)));

          //$takenDate = new DateTime( $exif['DateTime'] );
          $takenDate = new DateTime( $exif['EXIF']['DateTimeOriginal'] );

          return array('latitude'=>$latitude, 'longitude'=>$longitude, 'takenDate'=>$takenDate);
      } else {
          return false;
      }
    }

    function gps2Num($coordPart){
      $parts = explode('/', $coordPart);
      if(count($parts) <= 0)
      return 0;
      if(count($parts) == 1)
      return $parts[0];
      return floatval($parts[0]) / floatval($parts[1]);
    }
  ?>

    <script type="text/javascript">
      $(document).ready(function() {
        // TODO maybe possible to reduce lightgallery config?
        lightGallery(document.getElementById('lightgallery'), {
          animateThumb: false,
          zoomFromOrigin: false,
          //allowMediaOverlap: true,
          //toggleThumb: true,
          share: false,
          download: false,
          actualSize: false,
          flipHorizontal: false,
          flipVertical: false,
          rotate: false,
          thumbnail: true
          //plugins: [lgZoom, lgThumbnail],
          //speed: 500,
        });
      });
    </script>

    <div class="container mb-4">
      <hr>
      by <a href="mailto:info@derguntmar.de">info@derguntmar.de</a> in 2024
    </div>
  </body>
</html>
