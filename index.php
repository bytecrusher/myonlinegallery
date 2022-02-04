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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/css/lightgallery.css" integrity="sha512-+AEZYwZD0k41HR9Ibbwu8uhNeUGXvJzskNE20bHgIWtATbRIqN6haWC/TQYUHAxCnRjyMvh3J7k9SVESyechHQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/css/lg-zoom.min.css" integrity="sha512-SGo05yQXwPFKXE+GtWCn7J4OZQBaQIakZSxQSqUyVWqO0TAv3gaF/Vox1FmG4IyXJWDwu/lXzXqPOnfX1va0+A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/css/lg-thumbnail.min.css" integrity="sha512-GRxDpj/bx6/I4y6h2LE5rbGaqRcbTu4dYhaTewlS8Nh9hm/akYprvOTZD7GR+FRCALiKfe8u1gjvWEEGEtoR6g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384 Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <link rel="stylesheet" href="styles/my.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/lightgallery.umd.min.js" integrity="sha512-6XfQwCpN6Lvfs86oXCYZVOh4P/VaCupM+MzqJs80pG+n5e/wn18MrStrXr65suu1tajh4KqHNTW5+Q6enBI/Rw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/plugins/thumbnail/lg-thumbnail.umd.min.js" integrity="sha512-v+/cnd6XTt28XV37rip+QRMB9OTYr90c3TxqNLLZZSH7cfoirS2N6bt9HRvlbyRnhco/vBK5pUCJdaIpS+fuhw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/plugins/zoom/lg-zoom.umd.min.js" integrity="sha512-HUKhPg1xSgASHRlFw8S6QJX7+AsYEf0lV9P/UPrzI5l3nBmyRVOekKoU5rtexB9RQseLgbaelQK4BRWrpCbItw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      // TODO maybe possible to reduce lightgallery config?
      lightGallery(document.getElementById('lightgallery'), {
        animateThumb: false,
        zoomFromOrigin: false,
        allowMediaOverlap: true,
        toggleThumb: true,

        share: false,
        download: false,
        actualSize: false,
        flipHorizontal: false,
        flipVertical: false,
        rotate: false,

        plugins: [lgZoom, lgThumbnail],
        speed: 500,
      });

    });
  </script>
</head>

<body class="home">
  <div class="container">
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
      <div class="d-flex justify-content-center text-danger bg-dark"" id=" errormessage"><?php echo ($errormessage); ?></div>
    </div>
  <?php
  } else {
  ?>
    <div class="mycontainer">
      <div class="demo-gallery">
        <ul id="lightgallery" class="list-unstyled row">
          <?PHP
          // Auto collect pictures:
          $path    = './img';
          $files = scandir($path);
          $files = array_diff(scandir($path), array('.', '..'));
          foreach ($files as &$value) {
            echo "<li class='col-xs-6 col-sm-4 col-md-3' data-responsive='img/" . $value . " 800' data-src='img/" . $value . "' data-sub-html=''>" .
              "<a href=''><img class='img-responsive' src='img/" . $value . "'></a>" .
              "</li>";
          }
          ?>
        </ul>
      </div>
    </div>
  <?php
  }
  ?>
  <div class="container">
    <hr>
    by info@derguntmar.de 2021
  </div>
</body>

</html>
