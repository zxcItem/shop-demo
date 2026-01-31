<?php

namespace app\data\service\vod;

/**
 * 点播服务驱动接口
 * @class Contract
 * @package app\data\service\vod
 */
abstract class Contract
{
    /**
     * 上传视频 (服务端直传)
     * @param string $filePath 本地文件路径
     * @param string $title 视频标题
     * @return string 视频ID
     */
    abstract public function uploadVideo(string $filePath, string $title): string;

    /**
     * 通过 URL 拉取视频 (适用于已在 OSS/COS 的文件)
     * @param string $url 视频 URL
     * @param string $title 视频标题
     * @param string $ext 扩展名 (如 mp4)
     * @return string 视频ID (或任务ID)
     */
    abstract public function uploadVideoByUrl(string $url, string $title, string $ext = 'mp4'): string;

    /**
     * 获取播放地址
     * @param string $videoId 视频ID
     * @return array [playAuth, playInfo]
     */
    abstract public function getPlayInfo(string $videoId): array;
}
