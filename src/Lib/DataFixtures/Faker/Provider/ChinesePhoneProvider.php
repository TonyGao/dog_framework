<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChinesePhoneProvider extends BaseProvider
{
    // 中国移动号段
    const CHINA_MOBILE_PREFIXES = [
        '134', '135', '136', '137', '138', '139', '147', '150', '151', '152',
        '157', '158', '159', '178', '182', '183', '184', '187', '188', '198'
    ];

    // 中国联通号段
    const CHINA_UNICOM_PREFIXES = [
        '130', '131', '132', '145', '155', '156', '166', '171', '175', '176', '185', '186'
    ];

    // 中国电信号段
    const CHINA_TELECOM_PREFIXES = [
        '133', '149', '153', '173', '177', '180', '181', '189', '199'
    ];

    // 固定电话区号（主要城市）
    const LANDLINE_AREA_CODES = [
        '010', // 北京
        '021', // 上海
        '022', // 天津
        '023', // 重庆
        '024', // 沈阳
        '025', // 南京
        '027', // 武汉
        '028', // 成都
        '029', // 西安
        '0371', // 郑州
        '0431', // 长春
        '0451', // 哈尔滨
        '0531', // 济南
        '0571', // 杭州
        '0591', // 福州
        '020', // 广州
        '0755', // 深圳
        '0757', // 佛山
        '0769' // 东莞
    ];

    /**
     * 生成中国手机号码
     *
     * @return string
     */
    public function chineseMobileNumber()
    {
        // 合并所有运营商号段
        $allPrefixes = array_merge(
            self::CHINA_MOBILE_PREFIXES,
            self::CHINA_UNICOM_PREFIXES,
            self::CHINA_TELECOM_PREFIXES
        );
        
        $prefix = self::randomElement($allPrefixes);
        $suffix = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }

    /**
     * 生成中国固定电话号码
     *
     * @return string
     */
    public function chineseLandlineNumber()
    {
        $areaCode = self::randomElement(self::LANDLINE_AREA_CODES);
        
        // 根据区号长度生成相应长度的号码
        if (strlen($areaCode) === 3) {
            // 3位区号，8位号码
            $number = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } else {
            // 4位区号，7位号码
            $number = str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        }
        
        return $areaCode . '-' . $number;
    }

    /**
     * 生成电话号码（手机或固话）
     *
     * @return string
     */
    public function phoneNumber()
    {
        // 80% 概率生成手机号，20% 概率生成固话
        if (rand(1, 100) <= 80) {
            return $this->chineseMobileNumber();
        } else {
            return $this->chineseLandlineNumber();
        }
    }

    /**
     * 生成中国移动号码
     *
     * @return string
     */
    public function chinaMobileNumber()
    {
        $prefix = self::randomElement(self::CHINA_MOBILE_PREFIXES);
        $suffix = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }

    /**
     * 生成中国联通号码
     *
     * @return string
     */
    public function chinaUnicomNumber()
    {
        $prefix = self::randomElement(self::CHINA_UNICOM_PREFIXES);
        $suffix = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }

    /**
     * 生成中国电信号码
     *
     * @return string
     */
    public function chinaTelecomNumber()
    {
        $prefix = self::randomElement(self::CHINA_TELECOM_PREFIXES);
        $suffix = str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }
}