$(function() {
    Map.Init();
});

// Map
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