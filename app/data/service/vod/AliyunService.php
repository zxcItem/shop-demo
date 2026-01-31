<?php

namespace app\data\service\vod;

use AlibabaCloud\SDK\Vod\V20170321\Vod as AlibabaVod;
use AlibabaCloud\SDK\Vod\V20170321\Models\CreateUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\RefreshUploadVideoRequest;
use AlibabaCloud\SDK\Vod\V20170321\Models\GetPlayInfoRequest;
use Darabonba\OpenApi\Models\Config;
use think\admin\Exception;

/**
 * 阿里云点播服务
 * @class AliyunService
 * @package app\data\service\vod
 */
class AliyunService extends Contract
{
    /**
     * 阿里云点播客户端
     * @var AlibabaVod
     */
    protected $client;

    /**
     * 初始化服务
     * @throws Exception
     */
    public function __construct()
    {
        $config = new Config([
            'accessKeyId'     => env('vod_aliyun_access_key', sysconf('vod_aliyun_access_key')),
            'accessKeySecret' => env('vod_aliyun_secret_key', sysconf('vod_aliyun_secret_key')),
            'endpoint'        => env('vod_aliyun_endpoint', 'vod.cn-shanghai.aliyuncs.com'),
        ]);
        try {
            $this->client = new AlibabaVod($config);
        } catch (\Exception $e) {
            throw new Exception("阿里云VOD初始化失败: {$e->getMessage()}");
        }
    }

    /**
     * 创建上传凭证 (内部使用)
     * @param string $title 视频标题
     * @param string $filename 文件名称
     * @return array
     * @throws Exception
     */
    protected function createUploadVideo(string $title, string $filename): array
    {
        try {
            $request = new CreateUploadVideoRequest([
                'title'    => $title,
                'fileName' => $filename,
            ]);
            $response = $this->client->createUploadVideo($request);
            $body = $response->body;
            return [
                'uploadAuth'    => $body->uploadAuth,
                'uploadAddress' => $body->uploadAddress,
                'videoId'       => $body->videoId,
            ];
        } catch (\Exception $e) {
            throw new Exception("创建上传凭证失败: {$e->getMessage()}");
        }
    }

    /**
     * 上传视频 (服务端直传)
     * @param string $filePath 本地文件路径
     * @param string $title 视频标题
     * @return string 视频ID
     * @throws Exception
     */
    public function uploadVideo(string $filePath, string $title): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("文件不存在: {$filePath}");
        }

        try {
            // 1. 获取上传凭证和地址
            $createRes = $this->createUploadVideo($title, basename($filePath));
            
            // 2. 解析凭证
            $uploadAddress = json_decode(base64_decode($createRes['UploadAddress']), true);
            $uploadAuth = json_decode(base64_decode($createRes['UploadAuth']), true);
            
            if (!$uploadAddress || !$uploadAuth) {
                throw new Exception("凭证解析失败");
            }

            // 3. 初始化 OSS 客户端
            // 注意: Endpoint 需要处理一下，有时候返回的带 https:// 有时候不带
            $endpoint = $uploadAddress['Endpoint'];
            if (strpos($endpoint, 'http') !== 0) {
                $endpoint = "https://{$endpoint}";
            }

            $ossClient = new OssClient(
                $uploadAuth['AccessKeyId'],
                $uploadAuth['AccessKeySecret'],
                $endpoint,
                false,
                $uploadAuth['SecurityToken']
            );

            // 4. 上传文件
            $ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $filePath);

            return $createRes['VideoId'];
        } catch (OssException $e) {
            throw new Exception("OSS上传失败: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new Exception("视频上传失败: " . $e->getMessage());
        }
    }

    /**
     * 通过 URL 拉取视频
     * @param string $url 视频 URL
     * @param string $title 视频标题
     * @param string $ext 扩展名
     * @return string 视频ID
     * @throws Exception
     */
    public function uploadVideoByUrl(string $url, string $title, string $ext = 'mp4'): string
    {
        try {
            $request = new UploadMediaByURLRequest();
            $request->setUploadURLs($url);
            
            // 构建元数据
            $metadata = [
                'Title' => $title,
            ];
            $request->setUploadMetadatas(json_encode([$metadata]));

            $response = $this->client->uploadMediaByURL($request);
            $body = $response->body;

            // 阿里云会立即返回 Job 信息，其中包含 VideoId
            if (!empty($body->uploadJobs) && count($body->uploadJobs) > 0) {
                return $body->uploadJobs[0]->videoId;
            }
            
            throw new Exception("未获取到 VideoId");

        } catch (\Exception $e) {
            throw new Exception("URL 拉取失败: " . $e->getMessage());
        }
    }

    /**
     * 获取播放地址
     * @param string $videoId 视频ID
     * @return array
     * @throws Exception
     */
    public function getPlayInfo(string $videoId): array
    {
        try {
            $request = new GetPlayInfoRequest([
                'videoId' => $videoId,
            ]);
            $response = $this->client->getPlayInfo($request);
            $body = $response->body;
            
            $playList = [];
            foreach ($body->playInfoList->playInfo as $info) {
                $playList[] = [
                    'url'        => $info->playURL,
                    'format'     => $info->format,
                    'definition' => $info->definition,
                    'duration'   => $info->duration,
                    'size'       => $info->size,
                ];
            }

            return [
                'playInfo' => $playList,
                'base'     => $body->videoBase->toMap(),
            ];
        } catch (\Exception $e) {
            throw new Exception("获取播放地址失败: {$e->getMessage()}");
        }
    }
}
