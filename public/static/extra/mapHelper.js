// 腾讯地图通用组件 (GL版)
// 使用方法:
// layui.config({base: '/static/extra/'}).use(['mapHelper'], function(){
//     layui.mapHelper.render({
//         el: '#container',
//         lat: 39.9, lng: 116.4,
//         editable: true,
//         callback: function(res) { console.log(res); }
//     });
// });

layui.define(['jquery', 'layer'], function(exports){
    var $ = layui.jquery;
    var layer = layui.layer;
    var API_KEY = 'SDPBZ-GYE6U-PTTVR-2H2XK-7RWTK-3BB6K'; // 默认Key, 可配置

    var MapHelper = {
        render: function(options) {
            var opts = $.extend({
                el: '#container',
                lat: 39.916527,
                lng: 116.397128,
                zoom: 16, // GL maps handle zoom nicely, 16-18 is good
                key: API_KEY,
                editable: true,
                callback: function(){}
            }, options);

            var initMap = function() {
                if (!$(opts.el).length) return;
                
                var center = new TMap.LatLng(opts.lat, opts.lng);
                var map = new TMap.Map($(opts.el)[0], {
                    center: center,
                    zoom: opts.zoom,
                    pitch: 40, // 默认倾斜度，增加3D感
                    viewMode: '3D'
                });

                // 初始化marker
                var markerLayer = new TMap.MultiMarker({
                    map: map,
                    styles: {
                        'marker': new TMap.MarkerStyle({
                            'width': 25,
                            'height': 35,
                            'anchor': { x: 16, y: 32 }
                        })
                    },
                    geometries: [{
                        'id': 'center',
                        'styleId': 'marker',
                        'position': center
                    }]
                });

                var updatePosition = function(latLng) {
                    var lat = latLng.getLat().toFixed(6);
                    var lng = latLng.getLng().toFixed(6);
                    
                    // WebService API Reverse Geocoding via JSONP
                    $.ajax({
                        url: 'https://apis.map.qq.com/ws/geocoder/v1/',
                        type: 'GET',
                        dataType: 'jsonp',
                        data: {
                            location: lat + ',' + lng,
                            key: opts.key,
                            output: 'jsonp'
                        },
                        success: function(res) {
                            var resultData = {
                                lat: lat,
                                lng: lng,
                                address: '',
                                province: '',
                                city: '',
                                district: ''
                            };

                            if (res.status === 0) {
                                var r = res.result;
                                var comp = r.address_component;
                                resultData.province = comp.province;
                                resultData.city = comp.city;
                                resultData.district = comp.district;
                                resultData.address = (r.formatted_addresses && r.formatted_addresses.recommend) ? r.formatted_addresses.recommend : r.address;
                            }
                            
                            opts.callback(resultData);
                        }
                    });
                };

                if (opts.editable) {
                    // Click event to move marker
                    map.on('click', function(event) {
                        markerLayer.updateGeometries([{
                            'id': 'center',
                            'position': event.latLng
                        }]);
                        updatePosition(event.latLng);
                    });
                }
            };

            // Load Script if needed
            if (typeof TMap === 'object' && TMap.Map) {
                initMap();
            } else {
                var cbName = 'initTencentMapCallback';
                window[cbName] = function() { initMap(); };
                
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.src = "https://map.qq.com/api/gljs?v=1.exp&key=" + opts.key + "&callback=" + cbName;
                document.body.appendChild(script);
            }
        }
    };

    exports('mapHelper', MapHelper);
});
