

<script>
$(document).ready(function () {
    $("#search").keyup(function () {
        if ($("#search").val().length > 3) {
            $.ajax({
                type: "post",
                url: window.location.pathname + 'Livesearch',
                cache: false,
                data: 'search=' + $("#search").val(),
                success: function (response) {
                    $('#finalResult').html("");
                    var obj = JSON.parse(response);
                    if (obj.length > 0) {
                        try {
                            var items = [];
                            $.each(obj, function (i, val) {
                                items.push($('<li/>').text(val.title));
                            });
                            $('#finalResult').append.apply($('#finalResult'),
                                items);
                        } catch (e) {
                            alert('Exception while request..');
                        }
                    } else {
                        $('#finalResult').html($('<li/>').text("No Data Found"));
                    }

                },
                error: function () {
                    alert('Error while request..');
                }
            });
        }
        return false;
    });
    $(".left-menu-click").click(function () {
        $(".responcive-right .right-content").slideUp("fast");
        $(".toggal-filter").slideUp("fast");
        $(".toggal-category").slideUp("fast");
        $(".responcive-left .main-menu").slideToggle("slow");
    });
    $(".right-menu-click").click(function () {
        $(".responcive-left .main-menu").slideUp("fast");
        $(".toggal-filter").slideUp("fast");
        $(".toggal-category").slideUp("fast");
        $(".responcive-right .right-content").slideToggle("slow");
    });
});

</script>


<script>
$(document).ready(function () {
    $("#newsletter-check").change(function () {
        if ($(this).is(":checked")) {
            $('html, body').animate({
                scrollTop: $('#newsletter').offset().top
            }, 2000);
            document.getElementById("email").focus();
        } else if ($(this).is(":not(:checked)")) {
            $('html, body').animate({
                scrollTop: $('#header').offset().top
            }, 2000);
        }
    });

});

</script>
<script>
$('#curr').change(function () {
    location = "javascript:;" + $('#curr').val();
});

</script>
</div>

</div>
<script>
function togglesDiv(divsId) {

    var catdiv = document.getElementById(divsId);
    if (catdiv.style.display == "none") {
        catdiv.style.display = "block";
    }
}

</script>


<script>
function togglesDivmove() {
    alert(12);
    var catdiv = document.getElementById(divid);
    if (catdiv.style.display == "block") {
        catdiv.style.display = "none";
    }
}

</script>
<script>
$('.browse-nav a').mouseover(function () {
    $('#browse_cat').css("display", "block");
});
$('.browse-nav a').mouseout(function () {
    $('#browse_cat').css("display", "none");
});
$('#browse_cat').mouseover(function () {
    $('#browse_cat').css("display", "block");
});
$('#browse_cat').mouseout(function () {
    $('#browse_cat').css("display", "none");
});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous">
</script>
