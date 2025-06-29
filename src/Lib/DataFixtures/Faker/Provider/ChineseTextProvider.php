<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

final class ChineseTextProvider extends BaseProvider
{
    // 常见的中文词汇用于生成文本
    const WORDS = [
        '公司', '企业', '发展', '管理', '服务', '技术', '创新', '质量', '效率', '团队',
        '客户', '市场', '产品', '项目', '业务', '流程', '标准', '规范', '制度', '政策',
        '战略', '目标', '计划', '执行', '监督', '评估', '改进', '优化', '提升', '完善',
        '专业', '经验', '能力', '技能', '知识', '培训', '学习', '成长', '进步', '发展',
        '责任', '义务', '权利', '职责', '岗位', '职位', '部门', '组织', '机构', '单位',
        '协调', '沟通', '合作', '配合', '支持', '帮助', '指导', '建议', '意见', '反馈',
        '分析', '研究', '调查', '统计', '报告', '总结', '汇报', '记录', '档案', '资料',
        '安全', '保密', '风险', '控制', '预防', '处理', '解决', '应对', '措施', '方案',
        '效果', '成果', '收益', '价值', '意义', '作用', '影响', '贡献', '成就', '荣誉'
    ];

    // 常见的中文句子模板
    const SENTENCE_TEMPLATES = [
        '负责{word1}的{word2}和{word3}工作',
        '协助{word1}完成{word2}相关{word3}',
        '参与{word1}{word2}的{word3}和实施',
        '制定并执行{word1}{word2}的{word3}方案',
        '监督和{word1}{word2}的{word3}过程',
        '提供{word1}方面的{word2}和{word3}',
        '维护{word1}与{word2}的良好{word3}',
        '确保{word1}{word2}的{word3}和效率',
        '推进{word1}{word2}的{word3}和发展',
        '建立健全{word1}{word2}的{word3}体系'
    ];

    /**
     * 生成指定长度的中文文本
     *
     * @param int $maxNbChars 最大字符数
     * @return string
     */
    public function chineseText($maxNbChars = 200)
    {
        $text = '';
        $targetLength = min($maxNbChars, rand(50, $maxNbChars));
        
        while (mb_strlen($text, 'UTF-8') < $targetLength) {
            if (rand(0, 1) === 0) {
                // 使用句子模板
                $template = self::randomElement(self::SENTENCE_TEMPLATES);
                $sentence = str_replace(
                    ['{word1}', '{word2}', '{word3}'],
                    [
                        self::randomElement(self::WORDS),
                        self::randomElement(self::WORDS),
                        self::randomElement(self::WORDS)
                    ],
                    $template
                );
            } else {
                // 简单词汇组合
                $sentence = self::randomElement(self::WORDS) . self::randomElement(self::WORDS) . '的' . self::randomElement(self::WORDS);
            }
            
            $text .= $sentence;
            
            // 添加标点符号
            if (rand(0, 3) === 0) {
                $text .= '，';
            } else {
                $text .= '。';
            }
            
            // 如果文本过长，截断
            if (mb_strlen($text, 'UTF-8') > $targetLength) {
                $text = mb_substr($text, 0, $targetLength, 'UTF-8');
                // 确保以句号结尾
                if (!in_array(mb_substr($text, -1, 1, 'UTF-8'), ['。', '，', '！', '？'])) {
                    $text = mb_substr($text, 0, -1, 'UTF-8') . '。';
                }
                break;
            }
        }
        
        return $text;
    }

    /**
     * 生成中文描述文本（别名方法）
     *
     * @param int $maxNbChars
     * @return string
     */
    public function text($maxNbChars = 200)
    {
        return $this->chineseText($maxNbChars);
    }
}