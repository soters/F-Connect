<?php
declare(strict_types=1);
session_start();
require_once('../../connection/connection.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin" />
    <!-- Custom Links -->
    <link rel="stylesheet" href="../../assets/css/kiosk-design.css" />
    <link rel="shortcut icon" href="../../assets/images/F-Connect.ico" type="image/x-icon" />
    <title>F - Connect</title>
</head>

<body class="fade-out">

    <?php
    if (!empty($_SESSION['error_message'])) {
        echo '<div id="error-message" class="error-message alert alert-danger" role="alert">';
        echo '<i class="bi bi-exclamation-triangle"></i> ';
        echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8');
        echo '</div>';
        echo '<br>';
        unset($_SESSION['error_message']); // Clear the error message after displaying it
    }
    ?>

    <?php
    if (!empty($_SESSION['success_message'])) {
        echo '<div id="success-message" class="success-message alert alert-success" role="alert">';
        echo '<i class="bi bi-check-circle"></i> ';
        echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8');
        echo '</div>';
        echo '<br>';
        unset($_SESSION['success_message']); // Clear the success message after displaying it
    }
    ?>

    <nav class="navi-bar" role="banner">
        <a id="current-time"></a>
        <a id="live-date"></a>
    </nav>

    <div id="default-container">
        <p id="action-message-rfid">Tap your RFID Card</p>
        <p id="action-message-small">To manage your appointments</p>
        <br>
        <div id="flip-card-container">
            <div class="flip-card">
                <div class="flip-card-inner">
                    <div class="flip-card-front">
                        <p class="heading_8264">RFID CARD</p>
                        <svg class="logo" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="36" height="36"
                            viewBox="0 0 48 48">
                            <path fill="#fff" d="M32 10A14 14 0 1 0 32 38A14 14 0 1 0 32 10Z"></path>
                            <path fill="#03045e" d="M16 10A14 14 0 1 0 16 38A14 14 0 1 0 16 10Z"></path>
                            <path fill="##caf0f8"
                                d="M18,24c0,4.755,2.376,8.95,6,11.48c3.624-2.53,6-6.725,6-11.48s-2.376-8.95-6-11.48 C20.376,15.05,18,19.245,18,24z">
                            </path>
                        </svg>
                        <svg fill="#b2d214" viewBox="15 -15 90 90" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path
                                    d="M6.62012 16.4414V18H5.46289V13.7168H6.86621C8.03027 13.7168 8.6123 14.1387 8.6123 14.9824C8.6123 15.4785 8.37012 15.8623 7.88574 16.1338L9.13379 18H7.82129L6.91309 16.4414H6.62012ZM6.62012 15.5713H6.83691C7.24121 15.5713 7.44336 15.3926 7.44336 15.0352C7.44336 14.7402 7.24512 14.5928 6.84863 14.5928H6.62012V15.5713Z">
                                </path>
                                <path
                                    d="M10.6631 18H9.52344V13.7168H12.0547V14.6455H10.6631V15.4629H11.9463V16.3916H10.6631V18Z">
                                </path>
                                <path d="M12.7578 18V13.7168H13.9209V18H12.7578Z"></path>
                                <path
                                    d="M18.4854 15.7676C18.4854 16.4824 18.2881 17.0332 17.8936 17.4199C17.501 17.8066 16.9482 18 16.2354 18H14.8496V13.7168H16.332C17.0195 13.7168 17.5498 13.8926 17.9229 14.2441C18.2979 14.5957 18.4854 15.1035 18.4854 15.7676ZM17.2842 15.8086C17.2842 15.416 17.2061 15.125 17.0498 14.9355C16.8955 14.7461 16.6602 14.6514 16.3438 14.6514H16.0068V17.0508H16.2646C16.6162 17.0508 16.874 16.9492 17.0381 16.7461C17.2021 16.541 17.2842 16.2285 17.2842 15.8086Z">
                                </path>
                                <path
                                    d="M17 9C17 10.1046 16.1046 11 15 11C13.8954 11 13 10.1046 13 9C13 7.89543 13.8954 7 15 7C16.1046 7 17 7.89543 17 9Z">
                                </path>
                                <path
                                    d="M10.0588 4L9 2H4C2.89543 2 2 2.89543 2 4V20C2 21.1046 2.89543 22 4 22H20C21.1046 22 22 21.1046 22 20V15L20 13.9412V20H4V4H10.0588Z">
                                </path>
                                <path
                                    d="M21.9469 12.9698C22.6169 11.7999 22.9998 10.4447 22.9998 9C22.9998 4.58172 19.4181 1 14.9998 1C13.5551 1 12.1999 1.38295 11.03 2.05287L12.0225 3.78965C12.8998 3.28721 13.9163 3 14.9998 3C18.3135 3 20.9998 5.68629 20.9998 9C20.9998 10.0835 20.7126 11.1 20.2102 11.9773L21.9469 12.9698Z">
                                </path>
                                <path
                                    d="M19.3418 11.4811C19.7605 10.75 19.9998 9.90293 19.9998 9C19.9998 6.23858 17.7612 4 14.9998 4C14.0969 4 13.2498 4.23934 12.5187 4.65804L13.5111 6.39483C13.9498 6.14361 14.458 6 14.9998 6C16.6567 6 17.9998 7.34315 17.9998 9C17.9998 9.54176 17.8562 10.05 17.605 10.4887L19.3418 11.4811Z">
                                </path>
                            </g>
                        </svg>
                        <svg version="1.1" class="contactless" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px"
                            viewBox="0 0 50 50" xml:space="preserve">
                            <image id="image0" width="50" height="50" x="0" y="0" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAQAAAC0NkA6AAAABGdBTUEAALGPC/xhBQAAACBjSFJN
                      AAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAJcEhZ
                      cwAACxMAAAsTAQCanBgAAAAHdElNRQfnAg0IEzgIwaKTAAADDklEQVRYw+1XS0iUURQ+f5qPyjQf
                      lGRFEEFK76koKGxRbWyVVLSOgsCgwjZBJJYuKogSIoOonUK4q3U0WVBWFPZYiIE6kuArG3VGzK/F
                      fPeMM/MLt99/NuHdfPd888/57jn3nvsQWWj/VcMlvMMd5KRTogqx9iCdIjUUmcGR9ImUYowyP3xN
                      GQJoRLVaZ2DaZf8kyjEJALhI28ELioyiwC+Rc3QZwRYyO/DH51hQgWm6DMIh10KmD4u9O16K49it
                      VoPOAmcGAWWOepXIRScAoJZ2Frro8oN+EyTT6lWkkg6msZfMSR35QTJmjU0g15tIGSJ08ZZMJkJk
                      HpNZgSkyXosS13TkJpZ62mPIJvOSzC1bp8vRhhCakEk7G9/o4gmZdbpsTcKu0m63FbnBP9Qrc15z
                      bkbemfgNDtEOI8NO5L5O9VYyRYgmJayZ9nPaxZrSjW4+F6Uw9yQqIiIZwhp2huQTf6OIvCZyGM6g
                      DJBZbyXifJXr7FZjGXsdxADxI7HUJFB6iWvsIhFpkoiIiGTJfjJfiCuJg2ZEspq9EHGVpYgzKqwJ
                      qSAOEwuJQ/pxPvE3cYltJCLdxBLiSKKIE5HxJKcTRNeadxfhDiuYw44zVs1dxKwRk/uCxIiQkxKB
                      sSctRVAge9g1E15EHE6yRUaJecRxcWlukdRIbGFOSZCMWQA/iWauIP3slREHXPyliqBcrrD71Amz
                      Z+rD1Mt2Yr8TZc/UR4/YtFnbijnHi3UrN9vKQ9rPaJf867ZiaqDB+czeKYmd3pNa6fuI75MiC0uX
                      XSR5aEMf7s7a6r/PudVXkjFb/SsrCRfROk0Fx6+H1i9kkTGn/E1vEmt1m089fh+RKdQ5O+xNJPUi
                      cUIjO0Dm7HwvErEr0YxeibL1StSh37STafE4I7zcBdRq1DiOkdmlTJVnkQTBTS7X1FYyvfO4piaI
                      nKbDCDaT2anLudYXCRFsQBgAcIF2/Okwgvz5+Z4tsw118dzruvIvjhTB+HOuWy8UvovEH6beitBK
                      xDyxm9MmISKCWrzB7bSlaqGlsf0FC0gMjzTg6GgAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjMtMDIt
                      MTNUMDg6MTk6NTYrMDA6MDCjlq7LAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIzLTAyLTEzVDA4OjE5
                      OjU2KzAwOjAw0ssWdwAAACh0RVh0ZGF0ZTp0aW1lc3RhbXAAMjAyMy0wMi0xM1QwODoxOTo1Nisw
                      MDowMIXeN6gAAAAASUVORK5CYII="></image>
                        </svg>
                        <p class="number">97594 24844 - 9023 9231</p>
                        <p class="valid_thrus">VALID THRU</p>
                        <p class="date_8264">1 2 / 2 4</p>
                        <p class="name">JUAN DELACRUZ</p>
                    </div>
                    <div class="flip-card-back">
                        <div class="strip"></div>
                        <div class="mstrip"></div>
                        <div class="sstrip">
                            <p class="code">***</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Input Field -->
        <form id="rfid-form" method="POST" action="../functions/fill-information-2.php">
            <input type="" id="rfid-id" name="rfid_id" value="">
        </form>
    </div>

    <div id="top-right-button">
        <button type="button" class="small-button" data-bs-toggle="tooltip" title="Need help?" data-bs-placement="left">
            <i class="bi bi-question-lg"></i>
        </button>
    </div>

    <div id="top-left-button">
        <a href="kiosk-student.php" class="no-underline">
            <button type="button" class="small-button" data-bs-toggle="tooltip" title="Back" data-bs-placement="right">
                <i class="bi bi-arrow-left"></i>
            </button>
        </a>
    </div>

    <footer>
        <p id="collaboration-text">In collaboration with Colegio de Sta. Teresa de Avila</p>
    </footer>

    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="../../assets/js/custom-javascript.js"></script>

    <script>
        $(document).ready(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
    <script>
        // Automatically hide the error message after 2 seconds
        setTimeout(() => {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.transition = 'opacity 0.5s ease';
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove(); // Remove the element completely after fade-out
                }, 500); // Delay to match the fade-out duration
            }
        }, 7000); // 2 seconds delay before hiding
    </script>

    <script>
        // Automatically hide the success message after 7 seconds
        setTimeout(() => {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove(); // Remove the element completely after fade-out
                }, 500); // Delay to match the fade-out duration
            }
        }, 7000); // 7 seconds delay before hiding
    </script>

    <script>
        /** RFID Input Auto-Submit */
        const rfidInput = document.getElementById('rfid-id');
        const rfidForm = document.getElementById('rfid-form');

        if (rfidInput && rfidForm) {
            document.addEventListener('keydown', (event) => {
                if (event.target === document.body) {
                    const key = event.key;

                    if (key === 'Enter') {
                        // Submit the form when Enter is pressed
                        if (rfidInput.value.trim() !== '') {
                            rfidForm.submit();
                        }
                    } else {
                        // Append keystrokes to the hidden input field
                        rfidInput.value += key;
                    }
                }
            });
        }
    </script>
    <script>
        document.querySelectorAll('a.no-underline').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent immediate navigation
                const targetUrl = this.href; // Store the URL

                // Add the 'hidden' class to start the fade-out effect
                document.body.classList.add('hidden');

                // Wait for the transition to complete before navigating
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 500); // Match the CSS transition duration
            });
        });
    </script>
</body>

</html>