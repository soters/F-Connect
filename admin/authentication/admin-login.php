<?php
session_start();
include('../../connection/connection.php');
date_default_timezone_set('Asia/Manila');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <!-- For Nav Bar -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="stylesheet" href="../../assets/css/admin-login.css">
  <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
  <title>F - Connect</title>
</head>

<body>

  <?php
  if (isset($_GET['error'])) {
    $error = $_GET['error'];
    echo "<div class='error-container'>";
    if ($error === 'incorrect_password') {
      echo "<p class='error-message'><i class='bi bi-exclamation-circle'></i> Incorrect password. Please try again!</p>";
    } elseif ($error === 'account_not_found') {
      echo "<p class='error-message'><i class='bi bi-exclamation-circle'></i> Your email does not exist in our system!</p>";
    }
    echo "</div>";
  }
  ?>

  <div class="scroll-down">SCROLL DOWN
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
      <path
        d="M16 3C8.832031 3 3 8.832031 3 16s5.832031 13 13 13 13-5.832031 13-13S23.167969 3 16 3zm0 2c6.085938 0 11 4.914063 11 11 0 6.085938-4.914062 11-11 11-6.085937 0-11-4.914062-11-11C5 9.914063 9.914063 5 16 5zm-1 4v10.28125l-4-4-1.40625 1.4375L16 23.125l6.40625-6.40625L21 15.28125l-4 4V9z" />
    </svg>
  </div>

  <div class="container"></div>

  <div class="modal">
    <div class="modal-container">
      <div class="modal-left">
        <h1 class="modal-title">Welcome!</h1>
        <p class="modal-desc">Get started with our software, just sign in below to experience it.</p>

        <!-- Login Form -->
        <form action="../functions/login-process.php" method="POST">
          <!-- Display Error Messages -->

          <!-- Input for Email -->
          <div class="input-block">
            <label for="email" class="input-label">Email</label>
            <input type="email" name="email" id="email" placeholder="Email" required>
          </div>

          <!-- Input for Password -->
          <div class="input-block">
            <label for="password" class="input-label">Password</label>
            <input type="password" name="password" id="password" placeholder="Password" required>
          </div>

          <!-- Login Button -->
          <div class="modal-buttons">
            <button class="input-button" name="login" type="submit">Login</button>
          </div>
        </form>
      </div>

      <div class="modal-right">
        <img src="../../assets/images/BG2.jpg" alt="Image">
      </div>

      <button class="icon-button close-button">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
          <path
            d="M25 3C12.86158 3 3 12.86158 3 25C3 37.13842 12.86158 47 25 47C37.13842 47 47 37.13842 47 25C47 12.86158 37.13842 3 25 3ZM25 5C36.05754 5 45 13.94246 45 25C45 36.05754 36.05754 45 25 45C13.94246 45 5 36.05754 5 25C5 13.94246 13.94246 5 25 5ZM16.990234 15.990234A1.0001 1.0001 0 0016.292969 17.707031L23.585938 25L16.292969 32.292969A1.0001 1.0001 0 1017.707031 33.707031L25 26.414062L32.292969 33.707031A1.0001 1.0001 0 1033.707031 32.292969L26.414062 25L33.707031 17.707031A1.0001 1.0001 0 0032.980469 15.990234A1.0001 1.0001 0 0032.292969 16.292969L25 23.585938L17.707031 16.292969A1.0001 1.0001 0 0016.990234 15.990234Z" />
        </svg>
      </button>
    </div>

    <button class="modal-button">Click here to login</button>

    <!-- Credits -->
    <div class="credits">
      Powered by: <span>F-Connect</span>
    </div>
  </div>

  <script src="../../assets/js/admin-site-login.js"></script>

  <script>
    setTimeout(() => {
      const errorContainer = document.querySelector('.error-container');
      if (errorContainer) {
        errorContainer.style.display = 'none';
      }
    }, 3000); // 3 seconds

  </script>

  <script>
    // Trigger fade-out effect before navigating
    window.addEventListener('beforeunload', function () {
      document.body.classList.add('fade-out');
    });
  </script>
</body>

</html>