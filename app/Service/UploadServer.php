<?php
/**
 * 上传图片类
 */
namespace App\Service;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Validation\ValidatorFactory;
use Hyperf\HttpMessage\Upload\UploadedFile;

class UploadServer
{

    public function __construct(RequestInterface $request, ValidatorFactory $validatorFactory)
    {
        $this->request = $request;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * User: wujiawei
     * DateTime: 2023/5/22 11:33
     * describe: 上传图片
     * @param $data name图片名称 size图片大小
     * @return array
     */
    public function uploadImage($data)
    {
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
                'message' => $validator->errors()->first(),
            ];
        }
        // 保存图片到本地存储
        // 生成唯一的文件名
        $filename = $this->getFilename($file);
        // 将上传文件移动到指定路径
        $file->moveTo( './tmp/' . $filename);
        return [
            'code' => 200,
            'message' => 'Upload success.',
            'data' => [
                'url' => "/tmp/{$filename}",
            ],
        ];
    }

    //生成唯一的文件名
    private function getFilename(UploadedFile $file)
    {
        // 使用 UUID 生成唯一文件名，防止文件名冲突
        $uuid = uniqid();
        $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        return $uuid . '.' . $ext;
    }
}
