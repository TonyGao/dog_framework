<?php

namespace App\Service;

use ZipArchive;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FileResolver
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function resolveFilePath(string $namespace): string
    {
        // 命名空间前缀
        $namespacePrefix = 'App';
        $path = str_replace($namespacePrefix, 'src', $namespace);
        return $this->projectDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $path) . '.php';
    }

    /**
     * 创建 Zip 文件并添加文件
     * @param string $zipFileName 要创建的 Zip 文件名
     * @param array $filesToAdd 要添加到 Zip 文件中的文件列表
     * @return bool 操作是否成功
     */
    public function createZip($zipFileName, $filesToAdd)
    {
        // 创建 ZipArchive 对象
        $zip = new ZipArchive();

        // 打开 Zip 文件，如果无法创建则返回 false
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // 遍历文件列表并将每个文件添加到 zip 文件中
            foreach ($filesToAdd as $file) {
                // 检查文件是否存在
                if (file_exists($file)) {
                    // 添加文件到 zip 文件中
                    $zip->addFile($file, basename($file));
                } else {
                    // 文件不存在，抛出异常
                    throw new FileNotFoundException("文件 $file 不存在，无法添加到 Zip 文件中。");
                }
            }

            // 关闭 zip 文件
            $zip->close();

            // 删除原文件
            foreach ($filesToAdd as $file) {
                unlink($file);
            }

            // 操作成功，返回 true
            return true;
        } else {
            // 无法创建 Zip 文件，抛出异常
            throw new \Exception("无法创建 Zip 文件 $zipFileName 。");
        }
    }
}
