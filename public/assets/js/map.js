
    function myMap() {
        var mapOptions = {
            center: new google.maps.LatLng(45.4869, 9.1903), // Milan coordinates
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map"), mapOptions);
        
        // Add marker
        var marker = new google.maps.Marker({
            position: mapOptions.center,
            map: map,
            title: "Our Restaurant"
        });
    }