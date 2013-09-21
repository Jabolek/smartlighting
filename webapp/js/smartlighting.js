$(function() {
    Map.Init();
    UI.Init();
    ko.applyBindings(SmartLightingViewModel);
});

var UI = (function(){
    var $navItems = null;
    var $navHome = null;
    var $navMap = null;
    var $navSummary = null;

    var $multiselects;

    function initVariables() {
        $navItems = $('#nav li');
        $navHome = $('#nav li.home');
        $navMap = $('#nav li.map');
        $navSummary = $('#nav li.summary');

        $multiselects = $('.multiselect');
    }
    // Menu
    function handleMenuClicks() {
        for ( var i = 0; i < $navItems.length; i++) {
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

// Web application View Model
var SmartLightingViewModel = (function() {
    var self = this;

    //public
    return {
        multiSelectInitOptions : {
            includeSelectAllOption: true,
            enableFiltering:true
        },
        bulbsViewModel      : new BulbsViewModel(),
        lanternsViewModel   : new LanternsViewModel(),
        roadsViewModel      : new RoadsViewModel(),
        choosenRoads        : ko.observableArray(),
        problemViewModel    : new ProblemViewModel(),

        calculate : function() {
            var problemVM = SmartLightingViewModel.problemViewModel;
            console.log(problemVM.choosenBulbs(), problemVM.choosenLanterns(), SmartLightingViewModel.choosenRoads());
        }
    };
})();
// End Web application View Model

//View Models
function BulbsViewModel() {
    var self = this;
    var bulbsWebService = 'http://student.agh.edu.pl/~olekn/data.php?data_type=bulbs&callback=?';
    self.bulbs = ko.observableArray([]);
    self.getBulbs = function() {
        //Get bulbs from the server
//         $.getJSON(bulbsWebService, function(data){
//             if (data) {
//                 var bulbs = JSON.parse(data)
//                 self.bulbs(bulbs);
//             }
//         });
        //jQuery.support.cors = true;//To enable cross-domain ajax requests
        $.ajax({
            type: 'GET',
            url: bulbsWebService,
            dataType: 'jsonp',
            success: function(json) {
                console.log(json);
                if (data) {
                    var bulbs = JSON.parse(data);
                    self.bulbs(bulbs);
                }
            },
            error: function() {
                self.bulbs.push(new Bulb(
                    {
                        "name":"SpeedStar BGP323 GRN156-2S\/740 I DM FG AL SI",
                        "luminance":"13621",
                        "power_consumption":"137",
                        "lifetime":"100000",
                        "cost":"3899"
                    }
                )); //mock
            }
        });
        $.getJSON(bulbsWebService, function(data){
            console.log(data);
        });
        $.ajax({
            url: bulbsWebService,
            dataType: 'jsonp', // Notice! JSONP <-- P (lowercase)
        error: function(error) {
            console.log();
        },
            complete: function(xhr, status) {
                console.log(xhr);
                console.log(xhr.getAllResponseHeaders());
                if (status === 'error' || !xhr.responseText) {
                    console.log('getBilbsError');
                }
                else {
                    var data = xhr.responseText;
                    console.log(data);
                }
            }
        });
    };
    self.getBulbs();//Fetch bulbs from the server
}
function LanternsViewModel() {
    var self = this;
    self.lanterns = ko.observableArray([]);
    self.getLanterns = function() {
        //Get lanterns from the server
        self.lanterns.push(new Lantern({"name":"S\u201360 SRsP","height":"6","cost":"685","lifetime":"30"})); //mock
        self.lanterns.push(new Lantern({"name":"S\u201370 SRsP","height":"7","cost":"710","lifetime":"30"})); //mock
    };
    self.getLanterns();//Fetch lanterns from the server
}
function RoadsViewModel() {
    var self = this;
    self.roads = ko.observableArray([]);
    self.roadsNames = ko.computed(function() {
        return ko.utils.arrayMap(self.roads(), function(road) {
            return road.name;
        });
    });
    self.choosenRoads = ko.observableArray([]);
    self.getRoads = function() {
        //Get roads from the server
        self.roads.push(new Road({"name":"Wybickiego","width":4,"coords":[{"x":50.060975,"y":19.90101}]})); //mock
    };
    self.getRoads();//Fetch roads from the server
}
function ProblemViewModel() {
    var self = this;
    self.choosenBulbs = ko.observableArray([]);
    self.choosenLanterns = ko.observableArray([]);
    self.choosenRoads = ko.observableArray([]);
}

// Models
function Bulb(bulb) {
    var self = this;
    self.name = bulb.name;
    self.luminance = bulb.luminance;
    self.power_consumption = bulb.power_consumption;
    self.lifetime = bulb.lifetime;
    self.cost = bulb.cost;
}
function Lantern(lantern) {
    var self = this;
    self.name = lantern.name;
    self.height = lantern.height;
    self.cost = lantern.cost;
    self.lifetime = lantern.lifetime;
}
function Road(road) {
    var self = this;
    self.name = road.name;
    self.height = road.height;
    self.coords = road.coords;
}