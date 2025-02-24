<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
    <link rel="stylesheet" href="../../assets/css/admin-auth.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <a href="../pages/admin-index.php" class="back-button"><i class="bi bi-arrow-left"></i></a>
    <div class="wrapper">
        <div class="title">
            Admin Authentication
        </div>
        <form action="admin-check.php" method="POST">
            <div class="field">
                <input type="text" name="email" required>
                <label>Email Address</label>
            </div>
            <div class="field">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>
            <br>
            <div class="field">
                <input type="submit" value="Enter">
            </div>
            <br>
            <div class="credits">
                Powered by: <span>F-Connect</span>
            </div>
        </form>
        <div id="holder">
        <?php if (isset($_GET['message']) && isset($_GET['type'])): ?>
            <div id="alert-message" class="alert alert-<?php echo htmlspecialchars($_GET['type']); ?>">
                <?php echo htmlspecialchars(urldecode($_GET['message'])); ?>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide the alert after 5 seconds
        setTimeout(function () {
            var alertBox = document.getElementById('holder');
            if (alertBox) {
                alertBox.style.transition = "opacity 0.5s";
                alertBox.style.opacity = "0";
                setTimeout(function () {
                    alertBox.style.display = "none";
                }, 500);
            }
        }, 5000);
    </script>

</body>
</html>