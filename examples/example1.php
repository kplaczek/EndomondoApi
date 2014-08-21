<?php
ini_set('max_execution_time', 0);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8 />
        <title>Leaflet Heat</title>
        <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
        <script src='https://api.tiles.mapbox.com/mapbox.js/v2.0.1/mapbox.js'></script>
        <link href='https://api.tiles.mapbox.com/mapbox.js/v2.0.1/mapbox.css' rel='stylesheet' />
        <style>
            body { margin:0; padding:0; }
            #map { position:absolute; top:0; bottom:0; width:100%; }
        </style>
    </head>
    <body>
        <?php
        include_once './Uuid.php';
        include_once './Endomondo.php';
        $endo = new Endomondo();
        $endo->auth_token = "AUTH_TOKEN_HERE";

        //endomondo profile id
        $id = 8755217; 
        $n = 50;
        $heatPoints = array();
        foreach ($endo->workout_list($id, $n) as $v) {
            if (isset($v['points'])) {
                foreach ($v['points'] as $point) {
                    if (isset($point['lat']) && isset($point['lng']))
                        $heatPoints[] = array($point['lat'], $point['lng']);
                }
            }
        }


        //often there is more than 100k points in all workouts, there is no point to render all of them 
        //10k is more than enough so every count_of_points/10000 ish is added to the final list 
        
        $tmp = 0;
        $factor = floor(count($heatPoints) / 10000);
        
        $softenHeatPoints = [];
        foreach ($heatPoints as $point) {
            if (( ++$tmp % $factor) == 0)
                $softenHeatPoints[] = $point;
        }
        ?>
        <script src='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-heat/v0.1.0/leaflet-heat.js'></script>
        <div id='map'></div>
        <script>

<?php
echo ' var addressPoints = ' . json_encode($softenHeatPoints) . ';';
?>
            L.mapbox.accessToken = 'MAPBOX_ACCESS_TOKEN_HERE';
            var map = L.mapbox.map('map', 'examples.map-0l53fhk2');
            map.fitBounds(addressPoints);
            var heat = L.heatLayer(addressPoints, {
                radius: 20,
                blur: 15,
                maxZoom: 17,
            }).addTo(map);
        </script>
    </body>
</html>

