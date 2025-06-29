<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChineseUrlProvider extends BaseProvider
{
    // 中国常用顶级域名
    const CHINESE_TLDS = [
        '.com.cn',
        '.cn',
        '.com',
        '.net',
        '.org',
        '.gov.cn',
        '.edu.cn',
        '.ac.cn'
    ];

    // 企业网站常用词汇
    const COMPANY_WORDS = [
        'huaxia', 'zhonghua', 'dongfang', 'xifang', 'nanfang', 'beifang',
        'jinshan', 'yinshan', 'qingshan', 'hongshan', 'lanshan', 'lvshan',
        'dadi', 'tianyu', 'haitian', 'xinyu', 'yuanda', 'guangda',
        'weida', 'qiangda', 'mingda', 'shengda', 'fada', 'keda',
        'tech', 'group', 'corp', 'company', 'enterprise', 'industry',
        'business', 'service', 'solution', 'innovation', 'future', 'smart'
    ];

    // 行业相关词汇
    const INDUSTRY_WORDS = [
        'tech', 'finance', 'education', 'health', 'energy', 'auto',
        'real-estate', 'retail', 'logistics', 'manufacturing',
        'agriculture', 'tourism', 'media', 'consulting', 'legal',
        'construction', 'mining', 'chemical', 'pharmaceutical', 'food'
    ];

    // 常用协议
    const PROTOCOLS = ['http', 'https'];

    // 常用子域名
    const SUBDOMAINS = ['www', 'portal', 'admin', 'api', 'mobile', 'app', 'cloud', 'service'];

    /**
     * 生成中文风格的网站URL
     *
     * @param bool $withProtocol 是否包含协议
     * @param bool $withSubdomain 是否包含子域名
     * @return string
     */
    public function chineseUrl($withProtocol = true, $withSubdomain = true)
    {
        $url = '';
        
        // 添加协议
        if ($withProtocol) {
            $protocol = self::randomElement(self::PROTOCOLS);
            $url .= $protocol . '://';
        }
        
        // 添加子域名
        if ($withSubdomain && rand(0, 2) === 0) {
            $subdomain = self::randomElement(self::SUBDOMAINS);
            $url .= $subdomain . '.';
        }
        
        // 生成主域名
        $domain = $this->generateDomainName();
        $url .= $domain;
        
        // 添加顶级域名
        $tld = self::randomElement(self::CHINESE_TLDS);
        $url .= $tld;
        
        return $url;
    }

    /**
     * 生成URL（别名方法）
     *
     * @return string
     */
    public function url()
    {
        return $this->chineseUrl();
    }

    /**
     * 生成企业网站URL
     *
     * @return string
     */
    public function corporateUrl()
    {
        $protocol = self::randomElement(self::PROTOCOLS);
        $subdomain = rand(0, 1) === 0 ? 'www.' : '';
        $domain = $this->generateCorporateDomainName();
        $tld = self::randomElement(['.com.cn', '.cn', '.com']);
        
        return $protocol . '://' . $subdomain . $domain . $tld;
    }

    /**
     * 生成政府网站URL
     *
     * @return string
     */
    public function governmentUrl()
    {
        $cities = ['beijing', 'shanghai', 'guangzhou', 'shenzhen', 'hangzhou', 'nanjing', 'wuhan', 'chengdu'];
        $departments = ['gov', 'finance', 'education', 'health', 'transport', 'planning', 'culture', 'sports'];
        
        $city = self::randomElement($cities);
        $dept = self::randomElement($departments);
        
        return 'http://www.' . $dept . '.' . $city . '.gov.cn';
    }

    /**
     * 生成教育网站URL
     *
     * @return string
     */
    public function educationUrl()
    {
        $schools = ['tsinghua', 'pku', 'fudan', 'sjtu', 'zju', 'nju', 'whu', 'scu'];
        $school = self::randomElement($schools);
        
        return 'https://www.' . $school . '.edu.cn';
    }

    /**
     * 生成域名部分
     *
     * @return string
     */
    private function generateDomainName()
    {
        $type = rand(1, 3);
        
        switch ($type) {
            case 1:
                // 单个词汇
                return self::randomElement(self::COMPANY_WORDS);
                
            case 2:
                // 两个词汇组合
                $word1 = self::randomElement(self::COMPANY_WORDS);
                $word2 = self::randomElement(self::COMPANY_WORDS);
                return $word1 . $word2;
                
            case 3:
                // 行业词汇 + 公司词汇
                $industry = self::randomElement(self::INDUSTRY_WORDS);
                $company = self::randomElement(self::COMPANY_WORDS);
                return $industry . $company;
                
            default:
                return self::randomElement(self::COMPANY_WORDS);
        }
    }

    /**
     * 生成企业域名
     *
     * @return string
     */
    private function generateCorporateDomainName()
    {
        $prefixes = ['huaxia', 'zhonghua', 'dongfang', 'tianyu', 'yuanda', 'weida'];
        $suffixes = ['group', 'corp', 'company', 'tech', 'industry'];
        
        $prefix = self::randomElement($prefixes);
        $suffix = self::randomElement($suffixes);
        
        return $prefix . $suffix;
    }

    /**
     * 生成域名（不含协议）
     *
     * @return string
     */
    public function domainName()
    {
        $domain = $this->generateDomainName();
        $tld = self::randomElement(self::CHINESE_TLDS);
        
        return $domain . $tld;
    }

    /**
     * 生成IP地址风格的URL（用于内网测试）
     *
     * @return string
     */
    public function internalUrl()
    {
        $ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
        $port = rand(8000, 9999);
        
        return 'http://' . $ip . ':' . $port;
    }
}