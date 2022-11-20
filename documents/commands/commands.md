# 框架命令

### 向模型实体添加属性

例如，向集团模型Corporation添加“外文名称”的属性
步骤:
1, 向App\Entity\Organization\Corporation添加属性，询问增加的属性名称(英文)、备注、数据类型、如果是string类型，询问字符串长度、是否允许为null，默认值

(string, integer, smallint, bigint, boolean, decimal, date, time, datetime, datetimez, text, object, array, simple_array, json_array, float, guid, blob, entity)

2, 将以信息添加到类文件，并添加setter, getter方法
3, 执行 make:migration，再执行 doctrine:migrations:migrate
