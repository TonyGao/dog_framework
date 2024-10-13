<?php

namespace App\Service;

use ZipArchive;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FileResolver extends BaseService
{
    private $projectDir;
    private $fs;
    private $finder;
    private array $allowedExtensions;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        $this->fs = new Filesystem();
        $this->finder = new Finder();
        $this->allowedExtensions = ['txt', 'php', 'twig', 'js', 'css','html', 'json', 'md'];
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

    public function createFolder(string $path, int $permissions = 0755, bool $recursive = true): void
    {
        try {
            if (!$this->fs->exists($path)) {
                $this->fs->mkdir($path, $permissions, $recursive);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('无法创建目录：%s，错误信息：%s', $path, $e->getMessage()));
        }
    }

    /**
     * 根据路径创建文件或目录
     *
     * @param string $path 路径，可以是文件路径或目录路径
     * @param int $permissions 目录的权限
     * @param bool $recursive 是否递归创建目录
     * @throws \RuntimeException
     */
    public function createPath(string $path, int $permissions = 0755, bool $recursive = true): void
    {
        try {
            // 去除路径两端的空格，并标准化路径分隔符
            $path = trim($path);
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path); // 使用 DIRECTORY_SEPARATOR 统一路径格式

            // 使用 pathinfo 来解析路径信息
            $pathInfo = pathinfo($path);

            // 检查路径是否已经存在
            if (!$this->fs->exists($path)) {
                // 判断是否为文件路径（根据是否存在扩展名来区分）
                if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) {
                    // 如果是文件路径，检查扩展名是否在允许列表中
                    if (!in_array(strtolower($pathInfo['extension']), $this->allowedExtensions)) {
                        throw new \InvalidArgumentException(sprintf('文件扩展名 "%s" 不被允许', $pathInfo['extension']));
                    }

                    // 获取文件所在目录的路径
                    $directoryPath = $pathInfo['dirname'];

                    // 先创建文件所在的目录
                    if (!$this->fs->exists($directoryPath)) {
                        $this->fs->mkdir($directoryPath, $permissions, $recursive);
                    }

                    // 然后创建空文件（如果文件不存在）
                    $this->fs->dumpFile($path, ''); // 创建一个空文件
                } else {
                    // 如果没有扩展名，则认为是目录路径
                    $this->fs->mkdir($path, $permissions, $recursive);
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('无法创建路径：%s，错误信息：%s', $path, $e->getMessage()));
        }
    }
    
    

    /**
     * 判断文件夹是否存在
     *
     * @param string $path
     * @return boolean
     */
    public function directoryExists(string $path): bool
    {
        return $this->fs->exists($path) && is_dir($path);
    }
    
}
