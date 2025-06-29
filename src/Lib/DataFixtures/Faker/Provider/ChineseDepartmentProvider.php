<?php

namespace App\Lib\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\Cache\CacheItemPoolInterface;

final class ChineseDepartmentProvider extends BaseProvider
{
    // 一级部门名称（只包含在DEPARTMENT_TREE中定义的部门）
    const PRIMARY_DEPARTMENTS = [
        '人力资源部', '财务部', '技术部', '市场部', '销售部',
        '运营部', '法务部', '行政部', '采购部', '物流部'
    ];

    // 部门树状结构数据
    const DEPARTMENT_TREE = [
        '人力资源部' => [
            '招聘中心' => ['校园招聘组', '社会招聘组', '猎头合作组', '内推管理组', '招聘流程组'],
            '培训发展部' => ['新员工培训组', '管理培训组', '技能培训组', '在线学习组', '培训评估组'],
            '薪酬福利部' => ['薪酬设计组', '福利管理组', '绩效薪酬组', '股权激励组', '薪酬分析组'],
            '绩效管理部' => ['绩效考核组', '绩效改进组', '目标管理组', '绩效沟通组', '绩效数据组'],
            '员工关系部' => ['员工满意度组', '劳动关系组', '员工活动组', '申诉处理组', '离职管理组']
        ],
        '财务部' => [
            '会计核算部' => ['总账核算组', '成本核算组', '往来核算组', '固定资产组', '税务核算组'],
            '预算管理部' => ['预算编制组', '预算执行组', '预算分析组', '预算控制组', '预算考核组'],
            '内部审计部' => ['财务审计组', '经营审计组', '内控审计组', '专项审计组', '审计整改组'],
            '税务筹划部' => ['税务申报组', '税务筹划组', '税务风险组', '税务咨询组', '税务培训组'],
            '资金管理部' => ['资金计划组', '资金调度组', '银行关系组', '投资管理组', '风险控制组']
        ],
        '技术部' => [
            '研发中心' => ['前端开发组', '后端开发组', '移动开发组', '算法研究组', '技术架构组'],
            '质量保证部' => ['测试管理组', '自动化测试组', '性能测试组', '安全测试组', '质量分析组'],
            '运维部' => ['系统运维组', '网络运维组', '数据库运维组', '安全运维组', '监控告警组'],
            '产品部' => ['产品规划组', '用户体验组', '产品运营组', '数据分析组', '产品设计组'],
            '架构部' => ['系统架构组', '技术标准组', '技术评审组', '架构治理组', '技术创新组']
        ],
        '市场部' => [
            '品牌推广部' => ['品牌策划组', '广告投放组', '公关传播组', '活动策划组', '内容营销组'],
            '数字营销部' => ['线上推广组', '社交媒体组', 'SEO优化组', '数据营销组', '营销自动化组'],
            '市场调研部' => ['用户研究组', '竞品分析组', '市场分析组', '调研执行组', '数据洞察组'],
            '客户服务部' => ['客服热线组', '在线客服组', '客户成功组', '投诉处理组', '客户关怀组'],
            '商务拓展部' => ['渠道拓展组', '合作伙伴组', '商务谈判组', '项目对接组', '关系维护组']
        ],
        '销售部' => [
            '直销部' => ['企业直销组', '个人直销组', '电话销售组', '网络销售组', '门店销售组'],
            '渠道销售部' => ['代理商管理组', '经销商管理组', '渠道拓展组', '渠道培训组', '渠道支持组'],
            '销售支持部' => ['销售工具组', '销售培训组', '销售数据组', '合同管理组', '售前支持组'],
            '大客户部' => ['大客户开发组', '大客户维护组', '解决方案组', '项目交付组', '客户成功组'],
            '区域销售部' => ['华北销售组', '华东销售组', '华南销售组', '华中销售组', '西部销售组']
        ],
        '运营部' => [
            '业务运营部' => ['运营策划组', '运营执行组', '运营分析组', '用户运营组', '内容运营组'],
            '流程优化部' => ['流程设计组', '流程改进组', '流程监控组', '效率提升组', '标准化组'],
            '数据分析部' => ['业务分析组', '用户分析组', '产品分析组', '运营分析组', '数据挖掘组'],
            '运营支持部' => ['工具支持组', '培训支持组', '文档管理组', '知识管理组', '运营服务组'],
            '项目管理部' => ['项目规划组', '项目执行组', '项目监控组', '资源协调组', '风险管理组']
        ],
        '法务部' => [
            '合同管理部' => ['合同审核组', '合同谈判组', '合同执行组', '合同归档组', '风险评估组'],
            '知识产权部' => ['专利申请组', '商标管理组', '版权保护组', '知产维权组', '知产运营组'],
            '诉讼事务部' => ['民事诉讼组', '商事仲裁组', '行政诉讼组', '执行事务组', '案件管理组'],
            '法律咨询部' => ['业务咨询组', '合规咨询组', '风险咨询组', '培训咨询组', '外部律师组'],
            '合规监察部' => ['合规检查组', '内控监察组', '风险识别组', '合规培训组', '举报处理组']
        ],
        '行政部' => [
            '办公管理部' => ['办公用品组', '办公环境组', '设备管理组', '安全管理组', '接待服务组'],
            '后勤保障部' => ['餐饮服务组', '清洁服务组', '维修服务组', '安保服务组', '绿化管理组'],
            '车辆管理部' => ['车辆调度组', '车辆维护组', '司机管理组', '油卡管理组', '违章处理组'],
            '会议服务部' => ['会议预订组', '会议设备组', '会议接待组', '会议记录组', '会议跟进组'],
            '文档管理部' => ['文档归档组', '文档检索组', '文档安全组', '电子化组', '档案管理组']
        ],
        '采购部' => [
            '供应商管理部' => ['供应商开发组', '供应商评估组', '供应商关系组', '供应商审计组', '供应商培训组'],
            '采购执行部' => ['战略采购组', '日常采购组', '紧急采购组', '集中采购组', '分散采购组'],
            '成本控制部' => ['成本分析组', '价格谈判组', '成本优化组', '预算控制组', '节约管理组'],
            '质量检验部' => ['来料检验组', '过程检验组', '成品检验组', '供应商质量组', '质量改进组'],
            '仓储管理部' => ['入库管理组', '出库管理组', '库存管理组', '盘点管理组', '仓储优化组']
        ],
        '物流部' => [
            '运输管理部' => ['运输计划组', '运输执行组', '运输监控组', '运输成本组', '运输安全组'],
            '仓储配送部' => ['仓储作业组', '配送计划组', '配送执行组', '退货处理组', '包装管理组'],
            '供应链部' => ['供应链规划组', '供应链执行组', '供应链优化组', '供应链风险组', '供应链协同组'],
            '物流信息部' => ['系统开发组', '数据分析组', '信息维护组', '技术支持组', '创新研发组'],
            '客户服务部' => ['订单跟踪组', '客户咨询组', '异常处理组', '客户反馈组', '服务改进组']
        ]
    ];

    // 部门类型
    const DEPARTMENT_TYPES = [
        'department', 'center', 'office', 'division', 'section'
    ];

    // 部门代码前缀
    const DEPARTMENT_CODE_PREFIXES = [
        '人力资源部' => 'HR',
        '财务部' => 'FIN',
        '技术部' => 'TECH',
        '市场部' => 'MKT',
        '销售部' => 'SALES',
        '运营部' => 'OPS',
        '法务部' => 'LEGAL',
        '行政部' => 'ADMIN',
        '采购部' => 'PROC',
        '物流部' => 'LOG',
        '客服部' => 'CS',
        '质量部' => 'QC',
        '安全部' => 'SEC',
        '企划部' => 'PLAN',
        '公关部' => 'PR',
        '研发部' => 'RD',
        '设计部' => 'DESIGN',
        '产品部' => 'PROD',
        '测试部' => 'TEST',
        '架构部' => 'ARCH',
        '投资部' => 'INV',
        '战略部' => 'STRAT',
        '审计部' => 'AUDIT',
        '风控部' => 'RISK',
        '合规部' => 'COMP'
    ];

    /**
     * 生成一级部门名称
     */
    public function primaryDepartmentName()
    {
        return self::randomElement(self::PRIMARY_DEPARTMENTS);
    }

    /**
     * 根据一级部门生成对应的二级部门名称
     */
    public function secondaryDepartmentName($primaryDepartment = null)
    {
        if ($primaryDepartment && isset(self::DEPARTMENT_TREE[$primaryDepartment])) {
            $secondaryDepts = array_keys(self::DEPARTMENT_TREE[$primaryDepartment]);
            return self::randomElement($secondaryDepts);
        }
        
        // 如果没有指定一级部门或找不到对应关系，随机选择一个二级部门
        $allSecondary = [];
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            $allSecondary = array_merge($allSecondary, array_keys($secondaryDepts));
        }
        return self::randomElement($allSecondary);
    }

    /**
     * 根据二级部门生成对应的三级部门名称
     */
    public function tertiaryDepartmentName($secondaryDepartment = null)
    {
        if ($secondaryDepartment) {
            // 在树状结构中查找该二级部门
            foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
                if (isset($secondaryDepts[$secondaryDepartment])) {
                    return self::randomElement($secondaryDepts[$secondaryDepartment]);
                }
            }
        }
        
        // 如果没有指定二级部门或找不到对应关系，随机选择一个三级部门
        $allTertiary = [];
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            foreach ($secondaryDepts as $secondaryDept => $tertiaryDepts) {
                $allTertiary = array_merge($allTertiary, $tertiaryDepts);
            }
        }
        return self::randomElement($allTertiary);
    }

    /**
     * 根据部门索引生成合理的二级部门名称
     * 基于当前部门的索引来确定应该属于哪个一级部门
     */
    public function smartSecondaryDepartmentName($index = null)
    {
        // 如果没有传入索引，随机生成一个
        if ($index === null) {
            $index = $this->numberBetween(1, 15);
        }
        
        // 将索引转换为数字（如果是字符串）
        if (is_string($index)) {
            preg_match('/\d+/', $index, $matches);
            $index = isset($matches[0]) ? (int)$matches[0] : 1;
        }
        
        // 根据索引确定对应的一级部门
        $primaryDepts = array_values(self::PRIMARY_DEPARTMENTS);
        $primaryIndex = ($index - 1) % count($primaryDepts);
        $primaryDept = $primaryDepts[$primaryIndex];
        
        return $this->secondaryDepartmentName($primaryDept);
    }

    /**
     * 根据部门索引生成合理的三级部门名称
     * 基于当前部门的索引来确定应该属于哪个二级部门
     */
    public function smartTertiaryDepartmentName($index = null)
    {
        // 如果没有传入索引，随机生成一个
        if ($index === null) {
            $index = $this->numberBetween(1, 20);
        }
        
        // 将索引转换为数字（如果是字符串）
        if (is_string($index)) {
            preg_match('/\d+/', $index, $matches);
            $index = isset($matches[0]) ? (int)$matches[0] : 1;
        }
        
        // 根据索引确定对应的二级部门
        $allSecondary = [];
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            $allSecondary = array_merge($allSecondary, array_keys($secondaryDepts));
        }
        $secondaryIndex = ($index - 1) % count($allSecondary);
        $secondaryDept = $allSecondary[$secondaryIndex];
        
        return $this->tertiaryDepartmentName($secondaryDept);
    }

    /**
     * 缓存实例
     */
    private static $cache = null;
    
    /**
     * 获取缓存实例
     */
    private function getCache(): CacheItemPoolInterface
    {
        if (self::$cache === null) {
            self::$cache = new FilesystemAdapter('department_provider', 3600, __DIR__ . '/../../../../../var/cache');
        }
        return self::$cache;
    }
    
    /**
     * 初始化部门生成队列
     */
    private function initializeDepartmentQueue()
    {
        $cache = $this->getCache();
        
        // 检查是否已经初始化
        $initItem = $cache->getItem('queue_initialized');
        $isInitialized = $initItem->isHit() ? $initItem->get() : false;
        
        if ($isInitialized) {
            return;
        }
        
        // 构建预定义的部门生成队列
        $departmentQueue = [];
        $hierarchyMap = [];
        
        foreach (self::DEPARTMENT_TREE as $primaryName => $secondaryDepts) {
            // 添加一级部门到队列
            $departmentQueue[] = [
                'level' => 1,
                'name' => $primaryName,
                'parent' => null,
                'id' => 'primary_' . md5($primaryName)
            ];
            
            foreach ($secondaryDepts as $secondaryName => $tertiaryDepts) {
                // 添加二级部门到队列
                $secondaryId = 'secondary_' . md5($secondaryName);
                $departmentQueue[] = [
                    'level' => 2,
                    'name' => $secondaryName,
                    'parent' => $primaryName,
                    'id' => $secondaryId
                ];
                
                // 记录层级关系
                $hierarchyMap[$secondaryName] = $primaryName;
                
                foreach ($tertiaryDepts as $tertiaryName) {
                    // 添加三级部门到队列
                    $departmentQueue[] = [
                        'level' => 3,
                        'name' => $tertiaryName,
                        'parent' => $secondaryName,
                        'id' => 'tertiary_' . md5($tertiaryName)
                    ];
                    
                    // 记录层级关系
                    $hierarchyMap[$tertiaryName] = $secondaryName;
                }
            }
        }
        
        // 打乱队列顺序以避免过于规律的生成
        shuffle($departmentQueue);
        
        // 缓存队列和层级关系
        $item = $cache->getItem('department_queue');
        $item->set($departmentQueue);
        $cache->save($item);
        
        $item = $cache->getItem('department_hierarchy_map');
        $item->set($hierarchyMap);
        $cache->save($item);
        
        $item = $cache->getItem('current_queue_index');
        $item->set(0);
        $cache->save($item);
        
        // 标记为已初始化
        $item = $cache->getItem('queue_initialized');
        $item->set(true);
        $cache->save($item);
    }
    
    /**
     * 从队列中获取下一个部门
     */
    public function getNextDepartmentFromQueue($level)
    {
        $this->initializeDepartmentQueue();
        $cache = $this->getCache();
        
        $queueItem = $cache->getItem('department_queue');
        $queue = $queueItem->isHit() ? $queueItem->get() : [];
        
        $indexItem = $cache->getItem('current_queue_index');
        $currentIndex = $indexItem->isHit() ? $indexItem->get() : 0;
        
        // 从当前索引开始查找指定级别的部门
        for ($i = $currentIndex; $i < count($queue); $i++) {
            if ($queue[$i]['level'] === $level) {
                // 更新索引
                $item = $cache->getItem('current_queue_index');
                $item->set($i + 1);
                $cache->save($item);
                
                return $queue[$i];
            }
        }
        
        // 如果没有找到，从头开始查找
        for ($i = 0; $i < $currentIndex; $i++) {
            if ($queue[$i]['level'] === $level) {
                // 更新索引
                $item = $cache->getItem('current_queue_index');
                $item->set($i + 1);
                $cache->save($item);
                
                return $queue[$i];
            }
        }
        
        // 如果队列中没有该级别的部门，回退到随机生成
        return null;
    }
    
    /**
     * 根据当前实体的索引生成合理的二级部门名称
     * 这个方法会被Alice在生成dept_2_X时自动调用
     * @param string|null $parentDepartmentName 一级部门名称
     */
    public function contextualSecondaryDepartmentName($parentDepartmentName = null)
    {
        $cache = $this->getCache();
        
        // 如果传入了父级部门名称，直接使用
        if ($parentDepartmentName && isset(self::DEPARTMENT_TREE[$parentDepartmentName])) {
            $secondaryName = $this->secondaryDepartmentName($parentDepartmentName);
            // 缓存二级部门和其父级的关系
            $item = $cache->getItem('secondary_parent_' . md5($secondaryName));
        if (!$item->isHit()) {
            $item->set($parentDepartmentName);
            $cache->save($item);
        }
            return $secondaryName;
        }
        
        // 尝试从队列中获取二级部门
        $deptInfo = $this->getNextDepartmentFromQueue(2);
        if ($deptInfo) {
            $secondaryName = $deptInfo['name'];
            $parentName = $deptInfo['parent'];
            
            // 缓存二级部门和其父级的关系
            $item = $cache->getItem('secondary_parent_' . md5($secondaryName));
        if (!$item->isHit()) {
            $item->set($parentName);
            $cache->save($item);
        }
            
            // 记录已生成的二级部门
            $this->recordGeneratedDepartment($secondaryName, 2);
            
            return $secondaryName;
        }
        
        // 回退到原有逻辑（如果队列为空）
        $item = $cache->getItem('generated_primary_depts');
        $primaryDepts = $item->isHit() ? $item->get() : [];
        
        if (empty($primaryDepts)) {
            $rand = $this->numberBetween(1, 100);
            if ($rand <= 20) {
                $selectedPrimary = '人力资源部';
            } elseif ($rand <= 40) {
                $selectedPrimary = '财务部';
            } elseif ($rand <= 60) {
                $selectedPrimary = '技术部';
            } elseif ($rand <= 80) {
                $selectedPrimary = '市场部';
            } else {
                $selectedPrimary = '销售部';
            }
        } else {
            $selectedPrimary = self::randomElement($primaryDepts);
        }
        
        $secondaryName = $this->secondaryDepartmentName($selectedPrimary);
        
        $item = $cache->getItem('secondary_parent_' . md5($secondaryName));
        if (!$item->isHit()) {
            $item->set($selectedPrimary);
            $cache->save($item);
        }
        
        return $secondaryName;
    }

    /**
     * 根据当前实体的索引生成合理的三级部门名称
     * @param string|null $parentDepartmentName 二级部门名称
     */
    public function contextualTertiaryDepartmentName($parentDepartmentName = null)
    {
        $cache = $this->getCache();
        
        // 如果传入了父级部门名称，直接使用
        if ($parentDepartmentName) {
            // 检查是否为有效的二级部门
            foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
                if (array_key_exists($parentDepartmentName, $secondaryDepts)) {
                    return $this->tertiaryDepartmentName($parentDepartmentName);
                }
            }
        }
        
        // 尝试从队列中获取三级部门
        $deptInfo = $this->getNextDepartmentFromQueue(3);
        if ($deptInfo) {
            $tertiaryName = $deptInfo['name'];
            $parentName = $deptInfo['parent'];
            
            // 缓存三级部门和其父级的关系
            $cache->deleteItem('tertiary_parent_' . md5($tertiaryName));
            $item = $cache->getItem('tertiary_parent_' . md5($tertiaryName));
            $item->set($parentName);
            $cache->save($item);
            
            // 记录已生成的三级部门
            $this->recordGeneratedDepartment($tertiaryName, 3);
            
            // 建立父子关系映射
            $item = $cache->getItem('parent_child_map');
            $parentChildMap = $item->isHit() ? $item->get() : [];
            $parentChildMap[$parentName][] = $tertiaryName;
            $cache->deleteItem('parent_child_map');
            $item = $cache->getItem('parent_child_map');
            $item->set($parentChildMap);
            $cache->save($item);
            
            return $tertiaryName;
        }
        
        // 回退到原有逻辑（如果队列为空）
        // 但要确保三级部门与其正确的二级部门匹配
        $item = $cache->getItem('generated_secondary_depts');
        $secondaryDepts = $item->isHit() ? $item->get() : [];
        
        // 构建可用的三级部门列表，只包含已生成二级部门下的三级部门
        $availableTertiary = [];
        if (!empty($secondaryDepts)) {
            foreach ($secondaryDepts as $secondaryDept) {
                foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDeptTree) {
                    if (isset($secondaryDeptTree[$secondaryDept])) {
                        foreach ($secondaryDeptTree[$secondaryDept] as $tertiaryDept) {
                            $availableTertiary[$tertiaryDept] = $secondaryDept;
                        }
                    }
                }
            }
        }
        
        if (!empty($availableTertiary)) {
            $selectedTertiary = self::randomElement(array_keys($availableTertiary));
            $parentSecondary = $availableTertiary[$selectedTertiary];
            
            // 缓存三级部门和其父级的关系
            $cache->deleteItem('tertiary_parent_' . md5($selectedTertiary));
            $item = $cache->getItem('tertiary_parent_' . md5($selectedTertiary));
            $item->set($parentSecondary);
            $cache->save($item);
            
            return $selectedTertiary;
        }
        
        // 如果没有已生成的二级部门，则从所有部门中随机选择
        return $this->tertiaryDepartmentName();
    }
    
    /**
     * 生成并记录一级部门（供其他方法使用）
     */
    public function recordPrimaryDepartment()
    {
        static $deptCounter = 1;
        
        // 尝试从队列中获取一级部门
        $deptInfo = $this->getNextDepartmentFromQueue(1);
        if ($deptInfo) {
            $deptName = $deptInfo['name'];
            
            // 记录已生成的一级部门
            $this->recordGeneratedDepartment($deptName, 1);
            
            // 建立部门名称与引用的映射
            $this->recordPrimaryDepartmentReference($deptName, '@dept_' . $deptCounter);
            $deptCounter++;
            
            return $deptName;
        }
        
        // 回退到原有逻辑（如果队列为空）
        $deptName = $this->primaryDepartmentName();
        
        $cache = $this->getCache();
        $item = $cache->getItem('generated_primary_depts');
        $primaryDepts = $item->isHit() ? $item->get() : [];
        
        if (!in_array($deptName, $primaryDepts)) {
            $primaryDepts[] = $deptName;
            $cache->deleteItem('generated_primary_depts');
            $item = $cache->getItem('generated_primary_depts');
            $item->set($primaryDepts);
            $cache->save($item);
            
            // 建立部门名称与引用的映射
            $this->recordPrimaryDepartmentReference($deptName, '@dept_' . $deptCounter);
            $deptCounter++;
        }
        
        return $deptName;
    }
    
    /**
     * 生成并记录二级部门（供三级部门生成使用）
     */
    public function recordSecondaryDepartment()
    {
        $cache = $this->getCache();
        
        // 尝试从队列中获取二级部门
        $deptInfo = $this->getNextDepartmentFromQueue(2);
        
        if ($deptInfo) {
            $deptName = $deptInfo['name'];
            $parentName = $deptInfo['parent'];
            
            // 缓存父子关系
            $cache->deleteItem('secondary_parent_' . md5($deptName));
            $item = $cache->getItem('secondary_parent_' . md5($deptName));
            $item->set($parentName);
            $cache->save($item);
            
            // 记录已生成的二级部门
            $this->recordGeneratedDepartment($deptName, 2);
            
            // 建立父子关系映射，用于后续查找
            $item = $cache->getItem('parent_child_map');
            $parentChildMap = $item->isHit() ? $item->get() : [];
            $parentChildMap[$parentName][] = $deptName;
            $cache->deleteItem('parent_child_map');
            $item = $cache->getItem('parent_child_map');
            $item->set($parentChildMap);
            $cache->save($item);
            
            return $deptName;
        }
        
        // 回退到原有逻辑（如果队列为空）
        $deptName = $this->contextualSecondaryDepartmentName();
        
        $item = $cache->getItem('generated_secondary_depts');
        $secondaryDepts = $item->isHit() ? $item->get() : [];
        
        if (!in_array($deptName, $secondaryDepts)) {
            $secondaryDepts[] = $deptName;
            $cache->deleteItem('generated_secondary_depts');
            $item = $cache->getItem('generated_secondary_depts');
            $item->set($secondaryDepts);
            $cache->save($item);
        }
        
        return $deptName;
    }
    
    /**
     * 记录已生成的部门
     */
    private function recordGeneratedDepartment($deptName, $level)
    {
        $cache = $this->getCache();
        
        switch ($level) {
            case 1:
                $item = $cache->getItem('generated_primary_depts');
                $primaryDepts = $item->isHit() ? $item->get() : [];
                if (!in_array($deptName, $primaryDepts)) {
                    $primaryDepts[] = $deptName;
                    $cache->deleteItem('generated_primary_depts');
                    $item = $cache->getItem('generated_primary_depts');
                    $item->set($primaryDepts);
                    $cache->save($item);
                }
                break;
                
            case 2:
                $item = $cache->getItem('generated_secondary_depts');
                $secondaryDepts = $item->isHit() ? $item->get() : [];
                if (!in_array($deptName, $secondaryDepts)) {
                    $secondaryDepts[] = $deptName;
                    $cache->deleteItem('generated_secondary_depts');
                    $item = $cache->getItem('generated_secondary_depts');
                    $item->set($secondaryDepts);
                    $cache->save($item);
                }
                break;
                
            case 3:
                $item = $cache->getItem('generated_tertiary_depts');
                $tertiaryDepts = $item->isHit() ? $item->get() : [];
                if (!in_array($deptName, $tertiaryDepts)) {
                    $tertiaryDepts[] = $deptName;
                    $cache->deleteItem('generated_tertiary_depts');
                    $item = $cache->getItem('generated_tertiary_depts');
                    $item->set($tertiaryDepts);
                    $cache->save($item);
                }
                break;
        }
    }
    
    /**
     * 当前正在处理的二级部门的父级引用
     */
    private static $currentSecondaryParent = null;
    
    /**
     * 生成并记录二级部门，同时设置其父级引用
     */
    public function recordSecondaryDepartmentWithParent()
    {
        $cache = $this->getCache();
        
        // 尝试从队列中获取二级部门
        $deptInfo = $this->getNextDepartmentFromQueue(2);
        
        if ($deptInfo) {
            $deptName = $deptInfo['name'];
            $parentName = $deptInfo['parent'];
            
            // 查找对应的一级部门引用
            $item = $cache->getItem('primary_dept_references');
            $primaryRefs = $item->isHit() ? $item->get() : [];
            
            if (isset($primaryRefs[$parentName])) {
                self::$currentSecondaryParent = $primaryRefs[$parentName];
            } else {
                self::$currentSecondaryParent = '@dept_' . $this->numberBetween(1, 5);
            }
            
            // 缓存父子关系
            $cache->deleteItem('secondary_parent_' . md5($deptName));
            $item = $cache->getItem('secondary_parent_' . md5($deptName));
            $item->set($parentName);
            $cache->save($item);
            
            // 记录已生成的二级部门
            $this->recordGeneratedDepartment($deptName, 2);
            
            return $deptName;
        }
        
        // 回退到原有逻辑（如果队列为空）
        $deptName = $this->contextualSecondaryDepartmentName();
        
        // 查找对应的一级部门引用
        $item = $cache->getItem('secondary_parent_' . md5($deptName));
        if ($item->isHit()) {
            $parentName = $item->get();
            $item = $cache->getItem('primary_dept_references');
            $primaryRefs = $item->isHit() ? $item->get() : [];
            
            if (isset($primaryRefs[$parentName])) {
                self::$currentSecondaryParent = $primaryRefs[$parentName];
            } else {
                self::$currentSecondaryParent = '@dept_' . $this->numberBetween(1, 5);
            }
        } else {
            self::$currentSecondaryParent = '@dept_' . $this->numberBetween(1, 5);
        }
        
        $item = $cache->getItem('generated_secondary_depts');
        $secondaryDepts = $item->isHit() ? $item->get() : [];
        
        if (!in_array($deptName, $secondaryDepts)) {
            $secondaryDepts[] = $deptName;
            $cache->deleteItem('generated_secondary_depts');
            $item = $cache->getItem('generated_secondary_depts');
            $item->set($secondaryDepts);
            $cache->save($item);
        }
        
        return $deptName;
    }
    
    /**
     * 获取当前二级部门的父级引用
     */
    public function getCurrentSecondaryParent()
    {
        return self::$currentSecondaryParent ?: '@dept_' . $this->numberBetween(1, 5);
    }
    
    /**
     * 记录一级部门名称与引用的映射关系
     * @param string $deptName 部门名称
     * @param string $reference 部门引用
     */
    private function recordPrimaryDepartmentReference($deptName, $reference)
    {
        $cache = $this->getCache();
        
        $item = $cache->getItem('primary_dept_references');
        $primaryRefs = $item->isHit() ? $item->get() : [];
        
        $primaryRefs[$deptName] = $reference;
        
        $cache->deleteItem('primary_dept_references');
        $item = $cache->getItem('primary_dept_references');
        $item->set($primaryRefs);
        $cache->save($item);
    }
    
    /**
     * 获取二级部门的正确父级部门引用
     * @param string $secondaryDeptName 二级部门名称
     * @return string 父级部门的引用
     */
    public function getSecondaryDepartmentParentRef($secondaryDeptName)
    {
        $cache = $this->getCache();
        
        // 首先尝试从缓存中获取父级关系
        $item = $cache->getItem('secondary_parent_' . md5($secondaryDeptName));
        if ($item->isHit()) {
            $parentName = $item->get();
            
            // 查找对应的一级部门引用
            $item = $cache->getItem('primary_dept_references');
            $primaryRefs = $item->isHit() ? $item->get() : [];
            
            if (isset($primaryRefs[$parentName])) {
                return $primaryRefs[$parentName];
            }
        }
        
        // 如果缓存中没有，直接在DEPARTMENT_TREE中查找
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            if (array_key_exists($secondaryDeptName, $secondaryDepts)) {
                // 查找对应的一级部门引用
                $item = $cache->getItem('primary_dept_references');
                $primaryRefs = $item->isHit() ? $item->get() : [];
                
                if (isset($primaryRefs[$primaryDept])) {
                    return $primaryRefs[$primaryDept];
                }
                
                // 如果没有找到引用，返回默认的随机引用
                return '@dept_' . $this->numberBetween(1, 5);
            }
        }
        
        // 如果都没找到，返回随机引用
        return '@dept_' . $this->numberBetween(1, 5);
    }
    
    /**
     * 清理缓存（在fixtures加载开始时调用）
     */
    public function clearDepartmentCache()
    {
        $cache = $this->getCache();
        // 删除所有相关的缓存键
        $cache->deleteItem('generated_primary_depts');
        $cache->deleteItem('generated_secondary_depts');
        $cache->deleteItem('generated_tertiary_depts');
        $cache->deleteItem('department_queue');
        $cache->deleteItem('department_hierarchy_map');
        $cache->deleteItem('current_queue_index');
        $cache->deleteItem('queue_initialized');
        $cache->deleteItem('primary_dept_references');
        $cache->deleteItem('parent_child_map');
        
        // 清理所有以 secondary_parent_ 和 tertiary_parent_ 开头的缓存项
        $cacheKeys = ['secondary_parent_', 'tertiary_parent_'];
        foreach ($cacheKeys as $keyPrefix) {
            // 由于无法直接遍历所有缓存键，我们清理一些常见的部门名称对应的缓存
            foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
                foreach ($secondaryDepts as $secondaryDept => $tertiaryDepts) {
                    $cache->deleteItem($keyPrefix . md5($secondaryDept));
                    foreach ($tertiaryDepts as $tertiaryDept) {
                        $cache->deleteItem($keyPrefix . md5($tertiaryDept));
                    }
                }
            }
        }
        return 'cache_cleared';
    }

    /**
     * 根据二级部门名称找到对应的一级部门名称
     */
    public function findParentDepartment($departmentName)
    {
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            if (array_key_exists($departmentName, $secondaryDepts)) {
                return $primaryDept;
            }
        }
        
        // 如果找不到对应的一级部门，返回随机一级部门
        return self::randomElement(self::PRIMARY_DEPARTMENTS);
    }

    /**
     * 根据三级部门名称找到对应的二级部门名称
     */
    public function findSecondaryParent($departmentName)
    {
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            foreach ($secondaryDepts as $secondaryDept => $tertiaryDepts) {
                if (in_array($departmentName, $tertiaryDepts)) {
                    return $secondaryDept;
                }
            }
        }
        
        // 如果找不到对应的二级部门，返回随机二级部门
        $allSecondary = [];
        foreach (self::DEPARTMENT_TREE as $primaryDept => $secondaryDepts) {
            $allSecondary = array_merge($allSecondary, array_keys($secondaryDepts));
        }
        return self::randomElement($allSecondary);
    }

    /**
     * 生成部门代码
     */
    public function departmentCode($departmentName = null, $level = 1)
    {
        $prefix = 'DEPT';
        
        if ($departmentName && isset(self::DEPARTMENT_CODE_PREFIXES[$departmentName])) {
            $prefix = self::DEPARTMENT_CODE_PREFIXES[$departmentName];
        }
        
        $number = $this->numberBetween(100 * $level, 100 * $level + 99);
        return $prefix . $number;
    }

    /**
     * 生成部门别名
     */
    public function departmentAlias($departmentName)
    {
        // 移除"部"、"中心"、"组"等后缀
        $alias = str_replace(['部', '中心', '组', '办公室', '处'], '', $departmentName);
        return $alias;
    }

    /**
     * 生成部门类型
     */
    public function departmentType()
    {
        return self::randomElement(self::DEPARTMENT_TYPES);
    }

    /**
     * 生成排序号
     */
    public function departmentSortOrder($level = 1)
    {
        $base = $level * 100;
        return $this->numberBetween($base, $base + 99);
    }

    /**
     * 获取部门层级关系
     */
    public function getDepartmentHierarchy()
    {
        $primary = $this->primaryDepartmentName();
        $secondary = $this->secondaryDepartmentName($primary);
        $tertiary = $this->tertiaryDepartmentName($secondary);
        
        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'tertiary' => $tertiary
        ];
    }
    
    /**
     * 根据部门名称获取对应的父部门引用
     * 用于fixtures中动态设置parent关系
     */
    public function getParentReference($departmentName)
    {
        $cache = $this->getCache();
        
        // 从层级映射中查找父部门
        $item = $cache->getItem('department_hierarchy_map');
        $hierarchyMap = $item->isHit() ? $item->get() : [];
        
        if (isset($hierarchyMap[$departmentName])) {
            $parentName = $hierarchyMap[$departmentName];
            
            // 查找父部门的fixture引用
            $item = $cache->getItem('generated_primary_depts');
            $generatedPrimary = $item->isHit() ? $item->get() : [];
            
            $item = $cache->getItem('generated_secondary_depts');
            $generatedSecondary = $item->isHit() ? $item->get() : [];
            
            // 如果父部门是一级部门
            if (in_array($parentName, $generatedPrimary)) {
                $index = array_search($parentName, $generatedPrimary) + 1;
                return "@dept_{$index}";
            }
            
            // 如果父部门是二级部门
            if (in_array($parentName, $generatedSecondary)) {
                $index = array_search($parentName, $generatedSecondary) + 1;
                return "@dept_2_{$index}";
            }
        }
        
        // 默认返回随机父部门引用
        return '@dept_' . rand(1, 5);
    }
    
    /**
     * 获取二级部门的父部门引用
     */
    public function getSecondaryParentReference($departmentName)
    {
        $cache = $this->getCache();
        
        // 从缓存中获取该二级部门的父部门
        $item = $cache->getItem('secondary_parent_' . md5($departmentName));
        $parentName = $item->isHit() ? $item->get() : null;
        
        if ($parentName) {
            // 查找父部门在一级部门列表中的位置
            $item = $cache->getItem('generated_primary_depts');
            $generatedPrimary = $item->isHit() ? $item->get() : [];
            
            $index = array_search($parentName, $generatedPrimary);
            if ($index !== false) {
                return '@dept_' . ($index + 1);
            }
        }
        
        // 默认返回随机父部门引用
        return '@dept_' . rand(1, 5);
    }
    
    /**
     * 获取三级部门的父部门引用
     */
    public function getTertiaryParentReference($departmentName)
    {
        $cache = $this->getCache();
        
        // 从缓存中获取该三级部门的父部门
        $item = $cache->getItem('tertiary_parent_' . md5($departmentName));
        $parentName = $item->isHit() ? $item->get() : null;
        
        if ($parentName) {
            // 查找父部门在二级部门列表中的位置
            $item = $cache->getItem('generated_secondary_depts');
            $generatedSecondary = $item->isHit() ? $item->get() : [];
            
            $index = array_search($parentName, $generatedSecondary);
            if ($index !== false) {
                return '@dept_2_' . ($index + 1);
            }
        }
        
        // 默认返回随机父部门引用
        return '@dept_2_' . rand(1, 15);
    }
}