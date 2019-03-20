var i=0;

jQuery(function($){
    const scrollTime=500;
    $("#nav_contact").click(
        function(){
            $.scrollTo(clcScrollPosition("#contact"),scrollTime);
        });
    $("#nav_experience").click(
        function(){
            $.scrollTo(clcScrollPosition("#experience"),scrollTime);
        });
    $("#nav_school").click(
        function(){
            $.scrollTo(clcScrollPosition("#school"),scrollTime);
        });
    $("#nav_articles").click(
        function(){
            $.scrollTo(clcScrollPosition("#articles"),scrollTime);
        });
    $("#nav_competitions").click(
        function(){
            $.scrollTo(clcScrollPosition("#competitions"),scrollTime);
        });
    $("#nav_skils").click(
        function(){
            $.scrollTo(clcScrollPosition("#skils"),scrollTime);
        });
    $("#nav_interestings").click(
        function(){
            $.scrollTo(clcScrollPosition("#interestings"),scrollTime);
        });

    /*$("#divPhoto").click(function(){
        i++;
        if(i%2!=0){
            $("#divPhoto").html("<img id='photo' src='img/photo2.jpg' alt='photo'/>");
        }else{
            $("#divPhoto").html("<img id='photo' src='img/photo1.jpg' alt='photo'/>");
        }
    });*/
}
);

function clcScrollPosition(objectID){
    const headerOffset=60;
    navbar.classList.add("sticky");
    var position = $(objectID).position();
    position.top-=headerOffset;
    return position;
}

window.onscroll = function() {StickyNav()};

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

function StickyNav() {
if (window.pageYOffset >= sticky) {
    navbar.classList.add("sticky")
} else {
    navbar.classList.remove("sticky");
}
}