
/** Side Nav **/
$(".sidebar ul li").on('click', function() {
    $(".sidebar ul li.active").removeClass('active');
    $(this).addClass('active');
});

$('.open-btn').on('click', function() {
    $('.sidebar').addClass('active');
    $('.open-btn').hide();
    $('.close-btn').show();
});

$('.close-btn').on('click', function() {
    $('.sidebar').removeClass('active');
    $('.close-btn').hide();
    $('.open-btn').show();
})


/** Tool Tip 
 *   usage:
 *    kiosk-index **/

 var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});


/** Scroll Top Button **/
let mybutton = document.getElementById("myBtn");

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {
    scrollFunction()
};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        mybutton.style.display = "block";
    } else {
        mybutton.style.display = "none";
    }
}

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
    document.body.scrollTop = 0; // For Safari
    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}

/** Current Time Fucntion **/
let time = document.getElementById('current-time');

setInterval(() => {
    let d = new Date();
    time.innerHTML = d.toLocaleTimeString();
}, 1000)

/** Current Date Fucntion **/
function updateLiveDate() {
    var currentDate = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    var formattedDate = currentDate.toLocaleDateString('en-US', options);

    document.getElementById('live-date').textContent = formattedDate;
}
