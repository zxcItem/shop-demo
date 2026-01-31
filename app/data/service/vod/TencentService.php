<?php

namespace app\data\service\vod;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\VodClient;
use TencentCloud\Vod\V20180717\Models\ApplyUploadRequest;
use TencentCloud\Vod\V20180717\Models\PullUploadRequest;
use TencentCloud\Vod\V20180717\Models\CommitUploadRequest;
use TencentCloud\Vod\V20180717\Models\DescribeMediaInfosRequest;
use think\admin\Exception;

/**
 * 腾讯云点播服务
 * @class TencentService
 * @package app\data\service\vod
 */
class TencentService extends Contract
{
    /**
     * 腾讯云点播客户端
     * @var VodClient
     */
    protected $client;

    /**
     * 签名所需配置
     */
    protected $secretId;
    protected $secretKey;
    protected $subAppId;

    /**
     * 初始化服务
     * @throws Exception
     */
    public function __construct()
    {
        $this->secretId = env('vod_tencent_secret_id', sysconf('vod_tencent_secret_id'));
        $this->secretKey = env('vod_tencent_secret_key', sysconf('vod_tencent_secret_key'));
        $this->subAppId = env('vod_tencent_sub_app_id', sysconf('vod_tencent_sub_app_id')); // 子应用ID，可选

        try {
            $cred = new Credential($this->secretId, $this->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("vod.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);

            $this->client = new VodClient($cred, "", $clientProfile);
        } catch (\Exception $e) {
            throw new Exception("腾讯云VOD初始化失败: {$e->getMessage()}");
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
            // 1. 申请上传
            $req = new ApplyUploadRequest();
            $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'mp4';
            $req->setMediaType($ext);
            
            if ($this->subAppId) {
                $req->setSubAppId(intval($this->subAppId));
            }
            
            $resp = $this->client->ApplyUpload($req);
            
            $storageBucket = $resp->getStorageBucket();
            $storageRegion = $resp->getStorageRegion();
            $vodSessionKey = $resp->getVodSessionKey();
            $tempCertificate = $resp->getTempCertificate();
            $storagePath = $resp->getStoragePath();

            // 2. 初始化 COS 客户端
            $cosClient = new \Qcloud\Cos\Client([
                'region' => $storageRegion,
                'schema' => 'https',
                'credentials' => [
                    'secretId'  => $tempCertificate->getSecretId(),
                    'secretKey' => $tempCertificate->getSecretKey(),
                    'token'     => $tempCertificate->getToken(),
                ]
            ]);

            // 3. 上传文件
            $cosClient->putObject([
                'Bucket' => $storageBucket,
                'Key'    => $storagePath,
                'Body'   => fopen($filePath, 'rb'),
            ]);

            // 4. 确认上传
            $commitReq = new CommitUploadRequest();
            $commitReq->setVodSessionKey($vodSessionKey);
            if ($this->subAppId) {
                $commitReq->setSubAppId(intval($this->subAppId));
            }
            $commitResp = $this->client->CommitUpload($commitReq);

            return $commitResp->getFileId();

        } catch (\Exception $e) {
            throw new Exception("视频上传失败: " . $e->getMessage());
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
            $req = new DescribeMediaInfosRequest();
            $req->FileIds = [$videoId];
            if ($this->subAppId) {
                $req->SubAppId = intval($this->subAppId);
            }

            $resp = $this->client->DescribeMediaInfos($req);
            
            if (empty($resp->MediaInfoSet)) {
                throw new Exception("未找到视频信息");
            }

            $info = $resp->MediaInfoSet[0];
            $basicInfo = $info->BasicInfo;
            $metaData = $info->MetaData;
            
            // 腾讯云直接返回播放地址通常需要开启 Key 防盗链或 HLS 加密
            // 这里返回基础媒体信息，播放地址通常由 fileId + 播放器自动处理
            return [
                'playInfo' => [
                    'url'      => $basicInfo->MediaUrl,
                    'type'     => $basicInfo->Type,
                    'cover'    => $basicInfo->CoverUrl,
                ],
                'base'     => [
                    'Title'    => $basicInfo->Name,
                    'Duration' => $metaData->Duration,
                    'Size'     => $metaData->Size,
                ]
            ];
        } catch (\Exception $e) {
            throw new Exception("获取播放信息失败: {$e->getMessage()}");
        }
    }
}
