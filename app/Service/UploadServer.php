<?php
/**
 * 上传图片类
 */
namespace App\Service;

use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\ValidatorFactory;
use GuzzleHttp\Client;

class UploadServer
{
    protected $client;
    public function __construct(RequestInterface $request, ValidatorFactory $validatorFactory)
    {
        $this->request = $request;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 11:33
     * describe: 上传图片,最后返回路径，所有图片都存到tmp下
     * @param $data name图片名称 size图片大小
     * @return array
     * 访问图片示例：http://localhost:9501/tmp/646b0171c962b.jpg
     */
    public function uploadImage($data)
    {
        try {
            // 获取客户端提交的图片文件
            $file = $this->request->file($data['name']);

            // 验证图片格式和大小是否符合要求
            $validator = $this->validatorFactory->make([
                'image' => $file,
            ], [
                'image' => 'required|image|mimes:jpg,jpeg,png,gif|max:'.$data['size'], // 限制图片大小为 2MB，类型为 JPEG、PNG 或 GIF
            ]);

            if ($validator->fails()) {
                return [
                    'code' => 400,
                    'message' => '文件上传失败，请重试',
                ];
            }
            // 保存图片到本地存储
            // 生成唯一的文件名
            $filename = $this->getFilename($file);
            // 将上传文件移动到指定路径
            $file->moveTo( BASE_PATH . '/public/images/' . $filename);
            // 通过 isMoved(): bool 方法判断方法是否已移动
            if ($file->isMoved()) {
                //上传到oss
                @$this->aliyunUpload(BASE_PATH . '/public/images/' . $filename,$data['ossurl'].'/'.$filename);
                return [
                    'code'=>200,
                    'url'=>$data['ossurl'].'/'.$filename,
                    'filename'=>$filename,
                    'localurl'=>'/public/images/'.$filename
                ];
            }else{
                return [
                    'code'=>400,
                    'message' => '文件上传失败，请重试',
                ];
            }
        }catch (\Exception $exception){
            return [
                'code'=>400,
                'message' => '文件上传失败，请重试',
            ];
        }
    }

    //生成唯一的文件名
    private function getFilename(UploadedFile $file)
    {
        // 使用 UUID 生成唯一文件名，防止文件名冲突
        $uuid = uniqid();
        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        return $uuid . '.' . $ext;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 16:17
     * describe: 上传文件到oss
     * @param $file
     * @param $keypath
     * @return bool
     */
    private function aliyunUpload($file, $keypath)
    {
        $client = new Client();
        $url = 'http://lhygtest.linggongbang.cn/lgb/upload';
        $response = $client->post($url, [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen(realpath($file), 'r'),
                ],
                [
                    'name' => 'key',
                    'contents' => $keypath,
                ],
            ],
        ]);
        $result = json_decode($response->getBody(), true);
        return $result;
    }
}
