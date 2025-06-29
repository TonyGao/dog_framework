<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChineseEmailProvider extends BaseProvider
{
    // 中国常用邮箱域名
    const EMAIL_DOMAINS = [
        'qq.com',
        '163.com',
        '126.com',
        'sina.com',
        'sina.cn',
        'sohu.com',
        'yeah.net',
        'foxmail.com',
        'hotmail.com',
        'gmail.com',
        'outlook.com',
        'aliyun.com',
        '139.com',
        '189.cn',
        'wo.com.cn'
    ];

    // 企业邮箱域名
    const CORPORATE_DOMAINS = [
        'company.com',
        'corp.com',
        'enterprise.com',
        'group.com',
        'tech.com',
        'business.com',
        'industry.com',
        'service.com'
    ];

    // 常用英文名字前缀
    const NAME_PREFIXES = [
        'zhang', 'wang', 'li', 'zhao', 'chen', 'yang', 'wu', 'liu', 'huang', 'zhou',
        'xu', 'sun', 'ma', 'zhu', 'hu', 'guo', 'he', 'gao', 'lin', 'luo',
        'zheng', 'liang', 'xie', 'song', 'tang', 'han', 'feng', 'yu', 'dong', 'xiao',
        'cheng', 'cao', 'yuan', 'deng', 'xu', 'fu', 'shen', 'zeng', 'peng', 'lv',
        'su', 'lu', 'jiang', 'cai', 'jia', 'ding', 'wei', 'xue', 'ye', 'yan'
    ];

    // 常用英文名字后缀
    const NAME_SUFFIXES = [
        'wei', 'fang', 'na', 'min', 'jing', 'xiu', 'li', 'qiang', 'lei', 'jun',
        'yang', 'yong', 'yan', 'jie', 'juan', 'tao', 'chao', 'ming', 'hua', 'ping',
        'gang', 'gui', 'bin', 'feng', 'lin', 'qin', 'ting', 'xin', 'yu', 'wen',
        'dong', 'hui', 'yun', 'dan', 'jun', 'rui', 'peng', 'xu', 'chen', 'kun',
        'hao', 'song', 'bo', 'nan', 'han', 'qiong', 'zhu', 'yi', 'lei', 'xiang'
    ];

    /**
     * 生成中文风格的邮箱地址
     *
     * @param bool $corporate 是否生成企业邮箱
     * @return string
     */
    public function chineseEmail($corporate = false)
    {
        $domains = $corporate ? self::CORPORATE_DOMAINS : self::EMAIL_DOMAINS;
        $domain = self::randomElement($domains);
        
        // 生成用户名部分
        $username = $this->generateUsername();
        
        return $username . '@' . $domain;
    }

    /**
     * 生成邮箱地址（别名方法）
     *
     * @return string
     */
    public function email()
    {
        return $this->chineseEmail();
    }

    /**
     * 生成企业邮箱
     *
     * @return string
     */
    public function corporateEmail()
    {
        return $this->chineseEmail(true);
    }

    /**
     * 生成用户名部分
     *
     * @return string
     */
    private function generateUsername()
    {
        $type = rand(1, 4);
        
        switch ($type) {
            case 1:
                // 拼音组合
                $prefix = self::randomElement(self::NAME_PREFIXES);
                $suffix = self::randomElement(self::NAME_SUFFIXES);
                return $prefix . $suffix;
                
            case 2:
                // 拼音 + 数字
                $name = self::randomElement(self::NAME_PREFIXES);
                $number = rand(1, 9999);
                return $name . $number;
                
            case 3:
                // 拼音 + 下划线 + 数字
                $prefix = self::randomElement(self::NAME_PREFIXES);
                $suffix = self::randomElement(self::NAME_SUFFIXES);
                $number = rand(1, 999);
                return $prefix . '_' . $suffix . $number;
                
            case 4:
                // 纯数字（QQ邮箱风格）
                return str_pad(rand(100000, 9999999999), rand(6, 10), '0', STR_PAD_LEFT);
                
            default:
                return self::randomElement(self::NAME_PREFIXES) . self::randomElement(self::NAME_SUFFIXES);
        }
    }

    /**
     * 生成QQ邮箱
     *
     * @return string
     */
    public function qqEmail()
    {
        $qqNumber = str_pad(rand(100000, 9999999999), rand(6, 10), '0', STR_PAD_LEFT);
        return $qqNumber . '@qq.com';
    }

    /**
     * 生成163邮箱
     *
     * @return string
     */
    public function email163()
    {
        $username = $this->generateUsername();
        return $username . '@163.com';
    }

    /**
     * 生成126邮箱
     *
     * @return string
     */
    public function email126()
    {
        $username = $this->generateUsername();
        return $username . '@126.com';
    }
}