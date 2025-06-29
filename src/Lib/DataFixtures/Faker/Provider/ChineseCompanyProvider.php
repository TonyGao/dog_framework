<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChineseCompanyProvider extends BaseProvider
{
    // 公司名称前缀
    const COMPANY_PREFIXES = [
        '华夏', '中华', '东方', '西方', '南方', '北方',
        '金山', '银山', '青山', '红山', '蓝山', '绿山',
        '大地', '天宇', '海天', '新宇', '远大', '广大',
        '伟大', '强大', '明大', '盛大', '发达', '科达',
        '创新', '未来', '智慧', '数字', '云端', '星空',
        '阳光', '彩虹', '飞翔', '腾飞', '奔腾', '迅捷'
    ];

    // 公司名称后缀
    const COMPANY_SUFFIXES = [
        '科技有限公司', '技术有限公司', '信息技术有限公司',
        '网络科技有限公司', '软件科技有限公司', '数据科技有限公司',
        '实业有限公司', '贸易有限公司', '商贸有限公司',
        '投资有限公司', '控股有限公司', '集团有限公司',
        '咨询有限公司', '服务有限公司', '管理有限公司',
        '建设有限公司', '工程有限公司', '制造有限公司',
        '电子有限公司', '通信有限公司', '传媒有限公司',
        '文化有限公司', '教育有限公司', '医疗有限公司'
    ];

    // 职位名称
    const POSITION_NAMES = [
        // 高级管理层
        '董事长', '总裁', '首席执行官', '总经理', '副总经理',
        '首席技术官', '首席财务官', '首席运营官', '首席信息官',
        
        // 部门经理
        '人事经理', '财务经理', '技术经理', '产品经理', '项目经理',
        '销售经理', '市场经理', '运营经理', '客服经理', '质量经理',
        '采购经理', '行政经理', '法务经理', '审计经理', '风控经理',
        
        // 总监级别
        '技术总监', '产品总监', '销售总监', '市场总监', '运营总监',
        '人力资源总监', '财务总监', '战略总监', '投资总监',
        
        // 专员/工程师
        '软件工程师', '前端工程师', '后端工程师', '全栈工程师',
        '测试工程师', '运维工程师', '算法工程师', '数据工程师',
        'UI设计师', 'UX设计师', '产品设计师', '视觉设计师',
        '数据分析师', '业务分析师', '系统分析师', '需求分析师',
        '人事专员', '财务专员', '行政专员', '法务专员',
        '销售专员', '市场专员', '客服专员', '采购专员',
        
        // 主管/组长
        '技术主管', '开发主管', '测试主管', '运维主管',
        '销售主管', '客服主管', '财务主管', '人事主管',
        '项目组长', '开发组长', '测试组长', '设计组长',
        
        // 助理/秘书
        '总经理助理', '董事长助理', '行政助理', '人事助理',
        '财务助理', '销售助理', '市场助理', '技术助理',
        '执行秘书', '行政秘书', '董事会秘书'
    ];

    // 部门名称
    const DEPARTMENT_NAMES = [
        '人力资源部', '财务部', '技术部', '研发部', '产品部',
        '销售部', '市场部', '运营部', '客服部', '质量部',
        '采购部', '行政部', '法务部', '审计部', '风控部',
        '战略部', '投资部', '企划部', '公关部', '品牌部',
        '设计部', '测试部', '运维部', '安全部', '合规部',
        '培训部', '招聘部', '薪酬部', '绩效部', '员工关系部'
    ];

    // 行业类型
    const INDUSTRY_TYPES = [
        '信息技术', '金融服务', '制造业', '房地产', '教育培训',
        '医疗健康', '零售贸易', '物流运输', '能源化工', '农业食品',
        '文化传媒', '旅游酒店', '建筑工程', '汽车工业', '电子商务',
        '咨询服务', '法律服务', '广告营销', '环保科技', '生物医药'
    ];

    /**
     * 生成中文公司名称
     *
     * @return string
     */
    public function chineseCompanyName()
    {
        $prefix = self::randomElement(self::COMPANY_PREFIXES);
        $suffix = self::randomElement(self::COMPANY_SUFFIXES);
        
        return $prefix . $suffix;
    }

    /**
     * 生成公司名称（别名方法）
     *
     * @return string
     */
    public function company()
    {
        return $this->chineseCompanyName();
    }

    /**
     * 生成职位名称
     *
     * @return string
     */
    public function jobTitle()
    {
        return self::randomElement(self::POSITION_NAMES);
    }

    /**
     * 生成部门名称
     *
     * @return string
     */
    public function department()
    {
        return self::randomElement(self::DEPARTMENT_NAMES);
    }

    /**
     * 生成行业类型
     *
     * @return string
     */
    public function industry()
    {
        return self::randomElement(self::INDUSTRY_TYPES);
    }

    /**
     * 生成公司简称
     *
     * @return string
     */
    public function companyAlias()
    {
        $prefix = self::randomElement(self::COMPANY_PREFIXES);
        $suffixes = ['科技', '集团', '公司', '企业', '实业', '控股'];
        $suffix = self::randomElement($suffixes);
        
        return $prefix . $suffix;
    }

    /**
     * 生成公司代码
     *
     * @return string
     */
    public function companyCode()
    {
        $letters = ['HX', 'ZH', 'DF', 'TY', 'YD', 'WD', 'KD', 'CX'];
        $letter = self::randomElement($letters);
        $number = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $letter . $number;
    }

    /**
     * 生成统一社会信用代码
     *
     * @return string
     */
    public function socialCreditCode()
    {
        // 简化版本的统一社会信用代码生成
        $prefix = '91';
        $areaCode = str_pad(rand(110000, 659000), 6, '0', STR_PAD_LEFT);
        $orgCode = strtoupper(substr(md5(rand()), 0, 8));
        $checkCode = rand(0, 9);
        
        return $prefix . $areaCode . $orgCode . $checkCode;
    }

    /**
     * 生成营业执照注册号
     *
     * @return string
     */
    public function businessLicenseNumber()
    {
        $areaCode = str_pad(rand(110000, 659000), 6, '0', STR_PAD_LEFT);
        $serialNumber = str_pad(rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        $checkDigit = rand(0, 9);
        
        return $areaCode . $serialNumber . $checkDigit;
    }

    /**
     * 生成公司规模描述
     *
     * @return string
     */
    public function companySize()
    {
        $sizes = [
            '微型企业（1-9人）',
            '小型企业（10-49人）',
            '中型企业（50-299人）',
            '大型企业（300-999人）',
            '超大型企业（1000人以上）'
        ];
        
        return self::randomElement($sizes);
    }
}