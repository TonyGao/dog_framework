<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChineseAddressProvider extends BaseProvider
{
    // 中国主要省份
    const PROVINCES = [
        '北京市', '上海市', '天津市', '重庆市',
        '河北省', '山西省', '辽宁省', '吉林省', '黑龙江省',
        '江苏省', '浙江省', '安徽省', '福建省', '江西省', '山东省',
        '河南省', '湖北省', '湖南省', '广东省', '海南省',
        '四川省', '贵州省', '云南省', '陕西省', '甘肃省', '青海省',
        '内蒙古自治区', '广西壮族自治区', '西藏自治区', '宁夏回族自治区', '新疆维吾尔自治区'
    ];

    // 常见城市名称
    const CITIES = [
        '北京市', '上海市', '天津市', '重庆市',
        '石家庄市', '太原市', '沈阳市', '长春市', '哈尔滨市',
        '南京市', '杭州市', '合肥市', '福州市', '南昌市', '济南市',
        '郑州市', '武汉市', '长沙市', '广州市', '海口市',
        '成都市', '贵阳市', '昆明市', '西安市', '兰州市', '西宁市',
        '呼和浩特市', '南宁市', '拉萨市', '银川市', '乌鲁木齐市',
        '大连市', '青岛市', '宁波市', '厦门市', '深圳市', '苏州市',
        '无锡市', '常州市', '温州市', '嘉兴市', '金华市', '台州市'
    ];

    // 常见区县名称
    const DISTRICTS = [
        '海淀区', '朝阳区', '西城区', '东城区', '丰台区', '石景山区',
        '浦东新区', '黄浦区', '徐汇区', '长宁区', '静安区', '普陀区',
        '虹口区', '杨浦区', '闵行区', '宝山区', '嘉定区', '金山区',
        '松江区', '青浦区', '奉贤区', '崇明区',
        '天河区', '越秀区', '荔湾区', '海珠区', '白云区', '黄埔区',
        '番禺区', '花都区', '南沙区', '从化区', '增城区',
        '福田区', '罗湖区', '南山区', '盐田区', '宝安区', '龙岗区',
        '龙华区', '坪山区', '光明区', '大鹏新区'
    ];

    // 常见街道名称
    const STREETS = [
        '中山路', '人民路', '解放路', '建设路', '和平路', '友谊路',
        '文化路', '学院路', '科技路', '创新路', '发展大道', '繁华街',
        '商业街', '步行街', '金融街', '科学大道', '技术路', '工业路',
        '农业路', '教育路', '医院路', '体育路', '文艺路', '音乐路',
        '美术路', '书香路', '学府路', '智慧路', '未来路', '希望路',
        '幸福路', '安康路', '健康路', '长寿路', '吉祥路', '如意路',
        '顺利路', '成功路', '胜利路', '光明路', '阳光路', '彩虹路'
    ];

    // 建筑类型
    const BUILDING_TYPES = [
        '号', '号楼', '号院', '号大厦', '号写字楼', '号商务中心',
        '号科技园', '号工业园', '号创业园', '号孵化器'
    ];

    /**
     * 生成中文地址
     *
     * @return string
     */
    public function chineseAddress()
    {
        $province = self::randomElement(self::PROVINCES);
        $city = self::randomElement(self::CITIES);
        $district = self::randomElement(self::DISTRICTS);
        $street = self::randomElement(self::STREETS);
        $buildingNumber = rand(1, 999);
        $buildingType = self::randomElement(self::BUILDING_TYPES);
        
        // 有时添加单元和房间号
        $unit = '';
        if (rand(0, 2) === 0) {
            $unitNumber = rand(1, 20);
            $roomNumber = rand(101, 2999);
            $unit = $unitNumber . '单元' . $roomNumber . '室';
        }
        
        return $province . $city . $district . $street . $buildingNumber . $buildingType . $unit;
    }

    /**
     * 生成地址（别名方法）
     *
     * @return string
     */
    public function address()
    {
        return $this->chineseAddress();
    }

    /**
     * 生成省份
     *
     * @return string
     */
    public function province()
    {
        return self::randomElement(self::PROVINCES);
    }

    /**
     * 生成城市
     *
     * @return string
     */
    public function city()
    {
        return self::randomElement(self::CITIES);
    }

    /**
     * 生成区县
     *
     * @return string
     */
    public function district()
    {
        return self::randomElement(self::DISTRICTS);
    }

    /**
     * 生成街道
     *
     * @return string
     */
    public function street()
    {
        return self::randomElement(self::STREETS);
    }
}