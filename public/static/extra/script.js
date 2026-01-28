// +----------------------------------------------------------------------
// | Static Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 版权所有 2014~2024 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-static
// | github 代码仓库：https://github.com/zoujingli/think-plugs-static
// +----------------------------------------------------------------------
// | 自定义后台扩展脚本，需要在加载 admin.js 后载入
// | 使用 composer require zoujingli/think-plugs-static 时不会更新此文件
// +----------------------------------------------------------------------

$(function () {
    window.$body = $('body');

    /*! 初始化异步加载的内容扩展动作 */
    // $body.on('reInit', function (evt, $dom) {
    //     console.log('Event.reInit', $dom);
    // 

    /*! 追加 require 配置参数
    /*! 加载的文件不能与主配置重复 */
    // require.config({
    //     paths: {

        // },
        // shim: {
        //     'vue': ['json']
        // },
    // });
    // // 基于 Require 加载测试
    // require(['vue', 'md5'], function (vue, md5) {
    //     console.log(vue)
    //     console.log(md5.hash('content'))
    // });

    /*! 其他 javascript 脚本代码 */
    /*! 上传单个视频 */
    $.fn.uploadOneVideo = function () {
        return this.each(function () {
            if (this.dataset.inited) return; else this.dataset.inited = 'true';
            let $bt = $('<div class="uploadimage uploadvideo"><span><a data-file class="layui-icon layui-icon-upload-drag"></a><i class="layui-icon layui-icon-search"></i><i class="layui-icon layui-icon-close"></i></span><span data-file></span></div>');
            let $in = $(this).on('change', function () {
                if (this.value) $bt.css('backgroundImage', 'url("")').find('span[data-file]').html('<video width="100%" height="100%" autoplay loop muted><source src="' + encodeURI(this.value) + '" type="video/mp4"></video>');
            }).after($bt).trigger('change');
            $bt.on('click', 'i.layui-icon-search', function (event) {
                event.stopPropagation(), $in.val() && $.form.iframe(encodeURI($in.val()), '视频预览');
            }).on('click', 'i.layui-icon-close', function (event) {
                event.stopPropagation(), $bt.attr('style', '').find('span[data-file]').html('') && $in.val('').trigger('change');
            }).find('[data-file]').data('input', this).attr({
                'data-path': $in.data('path') || '', 'data-size': $in.data('size') || 0, 'data-type': $in.data('type') || 'mp4',
            });
        });
    };
    // 上传多个视频
    $.fn.uploadMultipleVideo = function () {
        return this.each(function () {
            if (this.dataset.inited) return; else this.dataset.inited = 'true';
            let $bt = $('<div class="uploadimage uploadvideo"><span><a data-file="mul" class="layui-icon layui-icon-upload-drag"></a></span><span data-file></span></div>');
            let ims = this.value ? this.value.split('|') : [], $in = $(this).after($bt);
            $bt.find('[data-file]').attr({
                'data-path': $in.data('path') || '', 'data-size': $in.data('size') || 0, 'data-type': $in.data('type') || 'mp4',
            }).on('push', function (evt, src) {
                ims.push(src), $in.val(ims.join('|')).trigger('change'), showImageContainer([src]);
            }) && (ims.length > 0 && showImageContainer(ims));

            function showImageContainer(srcs) {
                $(srcs).each(function (idx, src, $img) {
                    $img = $('<div class="uploadimage uploadimagemtl uploadvideo"><div><a data-event="left" class="layui-icon">&#xe603;</a><a data-event="preview" class="layui-icon layui-icon-search"></a><a data-event="remove" class="layui-icon">&#x1006;</a><a data-event="right" class="layui-icon">&#xe602;</a></div></div>');
                    $img.attr('data-video-src', encodeURI(src)).css('backgroundImage', 'none').prepend('<video width="100%" height="100%" autoplay loop muted><source src="' + encodeURI(src) + '" type="video/mp4"></video>').on('click', '[data-event]', function (event) {
                        event.stopPropagation();
                        let $item = $(this).parent().parent();
                        let type = this.dataset.event;
                        if (type === 'right' && $item.index() !== $bt.prevAll('div.uploadimage').length) $item.next().after($item);
                        else if (type === 'preview') $.form.iframe($item.attr('data-video-src'), '视频预览');
                        else if (type === 'left' && $item.index() > 1) $item.prev().before($item);
                        else if (type === 'remove') $item.remove();
                        ims = [], $bt.prevAll('.uploadimage').map(function () {
                            ims.push($(this).attr('data-video-src'));
                        });
                        ims.reverse(), $in.val(ims.join('|')).trigger('change');
                    }), $bt.before($img);
                });
            }
        });
    };




    
    // 上传多媒体（图片+视频）
    $.fn.uploadMultipleMedia = function () {
        return this.each(function () {
            if (this.dataset.inited) return; else this.dataset.inited = 'true';
            let $bt = $('<div class="uploadimage"><span><a data-file="mul" data-type="mp4,png,jpg,jpeg,gif" class="layui-icon layui-icon-upload-drag"></a></span><span data-file="images"></span></div>');
            let ims = this.value ? this.value.split('|') : [], $in = $(this).after($bt);
            $bt.find('[data-file]').attr({
                'data-path': $in.data('path') || '', 'data-size': $in.data('size') || 0, 'data-type': $in.data('type') || 'mp4,png,jpg,jpeg,gif',
            }).on('push', function (evt, src) {
                ims.push(src), $in.val(ims.join('|')).trigger('change'), showMediaContainer([src]);
            }) && (ims.length > 0 && showMediaContainer(ims));

            function showMediaContainer(srcs) {
                $(srcs).each(function (idx, src, $img) {
                    let isVideo = /\.(mp4|webm|ogg)$/i.test(src);
                    $img = $('<div class="uploadimage uploadimagemtl"><div><a data-event="left" class="layui-icon">&#xe603;</a><a data-event="preview" class="layui-icon layui-icon-search"></a><a data-event="remove" class="layui-icon">&#x1006;</a><a data-event="right" class="layui-icon">&#xe602;</a></div></div>');
                    if (isVideo) {
                        $img.addClass('uploadvideo').attr('data-media-src', encodeURI(src)).css('backgroundImage', 'none').prepend('<video width="100%" height="100%" autoplay loop muted><source src="' + encodeURI(src) + '" type="video/mp4"></video>');
                    } else {
                        $img.attr('data-media-src', encodeURI(src)).css('backgroundImage', 'url(' + encodeURI(src) + ')');
                    }
                    
                    $img.on('click', '[data-event]', function (event) {
                        event.stopPropagation();
                        let $item = $(this).parent().parent();
                        let type = this.dataset.event;
                        let src = $item.attr('data-media-src');
                        if (type === 'right' && $item.index() !== $bt.prevAll('div.uploadimage').length) $item.next().after($item);
                        else if (type === 'preview') {
                            if (/\.(mp4|webm|ogg)$/i.test(src)) {
                                $.form.iframe(src, '视频预览');
                            } else {
                                layui.layer.photos({photos: {title: "预览", data: [{src: src}]}, anim: 5});
                            }
                        }
                        else if (type === 'left' && $item.index() > 1) $item.prev().before($item);
                        else if (type === 'remove') $item.remove();
                        ims = [], $bt.prevAll('.uploadimage').map(function () {
                            ims.push($(this).attr('data-media-src'));
                        });
                        ims.reverse(), $in.val(ims.join('|')).trigger('change');
                    }), $bt.before($img);
                });
            }
        });
    };

    // 显示表格图片
    window.showTableImage = function (image, circle, size, title) {
        return $.layTable.showImage(image, circle, size, title);
    };

    // 修复 ArtPlayer 播放器容器查找失败的问题
    $.base && $.base.onEvent('click', '[data-video-player]', function () {
        let idx = $.msg.loading(), url = this.dataset.videoPlayer, name = this.dataset.title || '媒体播放器', payer;
        require(['artplayer'], () => layer.open({
            title: name, type: 1, fixed: true, maxmin: false,
            content: '<div class="data-play-video" style="width:800px;height:450px"></div>',
            end: () => payer.destroy(), success: $ele => payer = new Artplayer({
                url: url, container: $ele.find('.data-play-video').get(0), controls: [
                    {html: '全屏播放', position: 'right', click: () => payer.fullscreen = !payer.fullscreen},
                ]
            }, art => art.play(), $.msg.close(idx))
        }));
    });

    // 标签输入插件
    $.fn.initTagsInput = function (options) {
        options = $.extend({placeholder: '请输入标签，逗号分隔'}, options || {});
        return this.each(function () {
            if (this.dataset.inited) return; else this.dataset.inited = 'true';
            let $realInput = $(this);
            if ($realInput.attr('type') !== 'hidden') $realInput.hide();

            let $container = $('<div class="layui-input" style="height:auto;min-height:38px;line-height:normal;padding:5px 10px;display:flex;flex-wrap:wrap;align-items:center;"></div>');
            let $tagList = $('<div class="tag-list" style="display:contents;"></div>');
            let $tagInput = $('<input class="tag-input" style="border:none;outline:none;flex:1;min-width:150px;height:28px;line-height:28px;background:transparent;">');
            $tagInput.attr('placeholder', options.placeholder);

            $container.append($tagList).append($tagInput);
            $realInput.after($container);

            function renderTags() {
                let val = $realInput.val() || '';
                let tags = val.split(',').filter(function (t) { return t.trim() !== ''; });
                let html = '';
                tags.forEach(function (tag) {
                    html += '<span class="layui-badge layui-bg-orange" style="margin-right:5px;margin-bottom:2px;margin-top:2px;padding:5px 8px;height:auto;line-height:16px;border-radius:4px;">' + tag + ' <i class="layui-icon layui-icon-close remove-tag" style="font-size:12px;cursor:pointer;margin-left:5px;" data-tag="' + tag + '"></i></span>';
                });
                $tagList.html(html);
            }

            function addTag(val) {
                val = val.replace(/，/g, ',');
                let newTags = val.split(',').map(function (t) { return t.trim(); }).filter(function (t) { return t !== ''; });
                let currentVal = $realInput.val() || '';
                let currentTags = currentVal.split(',').filter(function (t) { return t.trim() !== ''; });
                newTags.forEach(function (t) {
                    if (currentTags.indexOf(t) === -1) currentTags.push(t);
                });
                $realInput.val(currentTags.join(',')).trigger('change');
                renderTags();
                $tagInput.val('');
            }

            $tagInput.on('keydown input', function (e) {
                let val = $(this).val();
                if (e.type === 'keydown' && (e.keyCode === 13 || e.keyCode === 188)) {
                    e.preventDefault();
                    addTag(val);
                }
                if (e.type === 'input' && (val.indexOf(',') > -1 || val.indexOf('，') > -1)) {
                    addTag(val);
                }
            });

            $tagInput.on('blur', function () {
                let val = $(this).val();
                if (val) addTag(val);
            });

            $tagList.on('click', '.remove-tag', function () {
                let tag = $(this).data('tag');
                let currentVal = $realInput.val() || '';
                let currentTags = currentVal.split(',').filter(function (t) { return t.trim() !== ''; });
                let index = currentTags.indexOf(tag.toString());
                if (index > -1) {
                    currentTags.splice(index, 1);
                    $realInput.val(currentTags.join(',')).trigger('change');
                    renderTags();
                }
            });

            $container.on('click', function(e){
                if(e.target === this) $tagInput.focus();
            });

            renderTags();
        });
    };
});