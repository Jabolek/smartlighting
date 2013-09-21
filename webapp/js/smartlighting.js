$(function() {
    Map.Init();
    UI.Init();
    ko.applyBindings(SmartLightingViewModel);
});

var UI = (function(){
    var $navItems;
    var $navHome;
    var $navMap;
    var $navSummary;

    var $shadow;

    var $accordion;
    var $checkboxes;//In lists in accordion

    var Messages = {};

    var _fadeInTime = 500, _fadeOutTime = 500;

    function initVariables() {
        $navItems = $('#nav li');
        $navHome = $('#nav li.home');
        $navMap = $('#nav li.map');
        $navSummary = $('#nav li.summary');

        $shadow = $('#shadow');

        $accordion = $('#accordion');
        $checkboxes = $('#accordion li');

        Messages.$dataError = $('#data_error');
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
    // End Menu
    function initializeAccordion() {
        var icons = {
            header: "ui-icon-circle-arrow-e",
            activeHeader: "ui-icon-circle-arrow-s"
        };
        $accordion.accordion({
            icons: icons,
            heightStyle: "fill",
            collapsible: true
        });
    }
    function handleCheckboxSelection() {
        $checkboxes.click(function(e) {
            var $a = $(this).children('a');
            var $input = $a.find('input');
            if ($a) $a.toggleClass('selected');
            if ($input) $input.prop('checked', !$input.prop('checked'));
            console.log(this);
            return false;
        });
    }

    //Messages
    function handleMessagesEvents() {
        Messages.$dataError.find('.btn.ok').click(function(e) {
            $shadow.fadeOut(_fadeOutTime);
            Messages.$dataError.fadeOut(_fadeOutTime);
        });
    }

    return {
        Init : function() {
            initVariables();
            handleMenuClicks();
            initializeAccordion();
            handleCheckboxSelection();
            handleMessagesEvents();
            navigateToPage('home');
        },
        RefreshAccordion : function() {
            $accordion.accordion('refresh');
        },
        Messages : {
            ShowDataError : function() {
                $shadow.fadeIn(_fadeInTime);
                Messages.$dataError.fadeIn(_fadeInTime);
            }
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
    //TODO Make the class and encapsulate functionality
    function fetchDataFromService(web_service_address, data_type, placeToStoreData) {
        if (web_service_address && data_type && placeToStoreData) {
            $.getJSON(web_service_address, data_type, function(result) {
                console.log(result);
                if (result.status === 'success') {
                    if (typeof placeToStoreData === "function" ) {
                        placeToStoreData(result.response);
                    if ( data_type.search('roads') !== -1 )
SmartLightingViewModel.problemViewModel.road(result.response[0].name);
                    }
                    UI.RefreshAccordion();
                } else if(result.status === 'error'){
                    return;//TODO throw an error
                }
            });
        }
    }

    //View Models
    function BulbsViewModel() {
        var self = this;
        self.bulbs = ko.observableArray([]);
        self.getBulbs = function() {
            var web_service = 'http://student.agh.edu.pl/~olekn/data.php?callback=?';
            var data_type = 'data_type=bulbs'
            fetchDataFromService(web_service, data_type, self.bulbs);//Fetch data and write to the array
        };
        self.getBulbs();//Fetch bulbs from the server
    }
    function LanternsViewModel() {
        var self = this;
        self.lanterns = ko.observableArray([]);
        self.getLanterns = function() {
            var web_service = 'http://student.agh.edu.pl/~olekn/data.php?callback=?';
            var data_type = 'data_type=lanterns'
            fetchDataFromService(web_service, data_type, self.lanterns);//Fetch data and write to the array
        };
        self.getLanterns();//Fetch lanterns from the server
    }
    function RoadsViewModel() {
        var self = this;
        self.roads = ko.observableArray([]);
        self.getRoads = function() {
            //Get roads from the server
            self.roads.push(new Road({"name":"Wybickiego","width":4,"coords":[{"x":50.060975,"y":19.90101}]})); //mock
            var web_service = 'http://student.agh.edu.pl/~olekn/data.php?callback=?';
            var data_type = 'data_type=roads'
            fetchDataFromService(web_service, data_type, self.roads);//Fetch data and write to the array
        };
        self.getRoads();//Fetch roads from the server
    }
    function ProblemViewModel() {
        var self = this;
        self.road = ko.observable(null);
        self.bulbs = ko.observableArray([]);
        self.lanterns = ko.observableArray([]);
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

    //public
    return {
        problemViewModel    : new ProblemViewModel(),
        bulbsViewModel      : new BulbsViewModel(),
        lanternsViewModel   : new LanternsViewModel(),
        roadsViewModel      : new RoadsViewModel(),

        calculate : function() {
            var problemVM = SmartLightingViewModel.problemViewModel;
            if (problemVM.bulbs().length <= 0 || problemVM.lanterns().length <= 0 || !problemVM.road()) {
                UI.Messages.ShowDataError();
            }
            else {
                console.log(problemVM.bulbs(), problemVM.lanterns(), problemVM.road());
            }
        }
    };
})();
// End Web application View Model
