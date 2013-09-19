$(function() {
    Map.Init();
    UI.Init();
});

var UI = (function(){
    var $navItems = null;
    var $navHome = null;
    var $navMap = null;
    var $navSummary = null;

    function initVariables() {
        $navItems = $('#nav li');
        $navHome = $('#nav li.home');
        $navMap = $('#nav li.map');
        $navSummary = $('#nav li.summary');
    }
    function handleMenuClicks() {
        for ( var i = 0; i < $navItems.length; i++) {
//             var closure = function(i) {
                (function(i) {
                $($navItems[i]).click(function() {
                    try {
                        $navItems.removeClass('active');
                        var targetHref = $($navItems[i]).addClass('active').find('a')[0].href;
                        var targetPageName = targetHref.substring(targetHref.indexOf('#') + 1);
                    }
                    catch(e) {}
                    navigateToPage(targetPageName);
                });
             }(i));
             //closure(i);
        }
        $('.navbar-header').click(function() {
            $navItems.removeClass('active');
            $navHome.addClass('active');
            navigateToPage('home');
        });
    }
    function navigateToPage(id) {
        if (id) {
            $('.page.active').fadeOut(500, function() {
                $(this).removeClass('active');
                var $target = $('#' + id);
                if ($target) $target.fadeIn(500, function() { $target.addClass('active'); });
            });
        }
    }

    return {
        Init : function() {
            initVariables();
            handleMenuClicks();
            navigateToPage('home');
        }
    };
})();

// Map singleton
var Map = (function() {
    //private
    var _map = null;
    var _coordinates = new google.maps.LatLng(50.057793, 19.914543);
    var _options = {
        zoom: 18,
        center: _coordinates,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    //public
    return {
        Init : function() {
            console.log('Init map');
            _map = new google.maps.Map(document.getElementById("map"), _options);
        }
    };
})();
// End Map