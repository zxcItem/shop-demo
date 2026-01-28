// 腾讯地图验证组件 (GL版) - 用于展示多点坐标对比
// 使用方法:
// layui.config({base: '/static/extra/'}).use(['mapVerifier'], function(){
//     layui.mapVerifier.render({
//         el: '#container',
//         markers: [
//            {lat: 39.9, lng: 116.4, label: '起点', color: 'blue'},
//            {lat: 39.91, lng: 116.41, label: '当前', color: 'red'}
//         ]
//     });
// });

layui.define(['jquery', 'layer'], function(exports){
    var $ = layui.jquery;
    var API_KEY = 'SDPBZ-GYE6U-PTTVR-2H2XK-7RWTK-3BB6K'; // 默认Key

    var MapVerifier = {
        render: function(options) {
            var opts = $.extend({
                el: '#container',
                zoom: 16,
                key: API_KEY,
                markers: [] // [{lat, lng, label, color}]
            }, options);

            var initMap = function() {
                if (!$(opts.el).length) return;
                
                // 默认中心点 (如果没有标记)
                var center = new TMap.LatLng(39.916527, 116.397128);
                
                // 初始化地图
                var map = new TMap.Map($(opts.el)[0], {
                    center: center,
                    zoom: opts.zoom,
                    pitch: 40,
                    viewMode: '3D'
                });

                // 定义样式
                var styles = {
                    'red': new TMap.MarkerStyle({
                        'width': 25, 'height': 35, 'anchor': { x: 16, y: 32 },
                        'src': 'https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/markerDefault.png'
                    }),
                    'blue': new TMap.MarkerStyle({
                        'width': 25, 'height': 35, 'anchor': { x: 16, y: 32 },
                        'src': 'https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/marker_blue.png' // 蓝色图标
                    })
                };

                var geometries = [];
                var labelGeometries = []; // 文本标签
                var bounds = new TMap.LatLngBounds();
                var hasValidMarker = false;

                if (opts.markers && opts.markers.length > 0) {
                    opts.markers.forEach(function(m, idx) {
                        if (m.lat && m.lng) {
                            var pos = new TMap.LatLng(m.lat, m.lng);
                            geometries.push({
                                'id': 'marker_' + idx,
                                'styleId': m.color || (idx === 0 ? 'blue' : 'red'), // 第一个默认蓝，第二个默认红
                                'position': pos
                            });
                            
                            // 添加文本标签
                            if (m.label) {
                                labelGeometries.push({
                                    'id': 'label_' + idx,
                                    'styleId': 'label',
                                    'position': pos,
                                    'content': m.label
                                });
                            }

                            bounds.extend(pos);
                            hasValidMarker = true;
                        }
                    });
                }

                // 初始化marker层
                if (geometries.length > 0) {
                    new TMap.MultiMarker({
                        map: map,
                        styles: styles,
                        geometries: geometries
                    });
                }

                // 初始化文本标签层
                if (labelGeometries.length > 0) {
                    new TMap.MultiLabel({
                        id: 'label-layer',
                        map: map,
                        styles: {
                            'label': new TMap.LabelStyle({
                                'color': '#333333', // 颜色
                                'size': 14, // 字号
                                'offset': { x: 0, y: -45 }, // 偏移量，显示在marker上方
                                'angle': 0,
                                'alignment': 'center', // 水平对齐
                                'verticalAlignment': 'middle' // 垂直对齐
                            })
                        },
                        geometries: labelGeometries
                    });
                }

                // 自动调整视野以包含所有标记
                if (hasValidMarker) {
                    map.fitBounds(bounds, { padding: 80 });
                }
            };

            // 加载脚本
            if (typeof TMap === 'object' && TMap.Map) {
                initMap();
            } else {
                var cbName = 'initMapVerifierCallback';
                window[cbName] = function() { initMap(); };
                
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.src = "https://map.qq.com/api/gljs?v=1.exp&key=" + opts.key + "&callback=" + cbName;
                document.body.appendChild(script);
            }
        }
    };

    exports('mapVerifier', MapVerifier);
});
