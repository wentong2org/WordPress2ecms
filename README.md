# WordPress转换为帝国CMS完整图文教程、技术记录

## 实现原理：利用帝国cms采集WordPress文章，采集时先不审核（比较重要），然后批量替换。

## 准备工作：

1.建议将WordPress样式设置为最有利于采集的，比如分类列表页，仅仅显示链接，并在显示9999999条，这样这样采集时直接就是内容页了。

![img](https://github.com/wentong2org/WordPress2ecms/blob/master/images/WordPress2ecms-img.jpg)

2.帝国cms设置跟Wordpress相同结果的目录。

3.帝国cms的数据表中，建立两个字段：empireselfurl  和 keywords

empireselfurl 用来储存WordPress的地址，keywords 用来储存WordPress的标签

keywords 和 empireselfurl 字段设置（按下面步骤重复操作两次）

（1）系统设置——管理数据表——管理字段——增加字段

字段名:keywords  （empireselfurl ）

字段标识:关键词   （WordPress链接）

字段类型:字符型0-255

字节长度：70

存放表:主表

前台内容显示:钩选"将回车替换成换行符"

2.系统设置——管理数据表——管理系统模型——修改——关键词（Wordpress链接） 

(钩选:录入项+投稿项+必填项+可增加+可修改+采集项+内容模板+搜索项)

采集过程

切记，仅仅入库，不审核。

![img](https://github.com/wentong2org/WordPress2ecms/blob/master/images/WordPress2ecms-2.jpg)

分目录直接，一个目录一个目录采集

采集完毕后，建议phpmyadmin进入mysql数据库，可以看到

phome_ecms_news_check

phome_ecms_news_check_data

这两个是采集WordPress文章，尚未审核的文章，在这两个表进行数据库update replace 这样的操作，不会影响到正常的数据。

数据替换更新

1.系统设置——备份/恢复数据——执行SQL语句

将WordPress的标签复制到帝国cms的关键词

update phome_ecms_news_check set keyboard = keywords

将WordPress的链接复制到帝国cms的titleurl字段

WordPress的链接结构是这样的 https://wentong.org/free/277.html

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'https://wentong.org','')

update phome_ecms_news_check set titleurl = empireselfurl

将帝国cms的filename字段改为WordPress的链接名称，实例的是post_id 先处理掉目录和html

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'.html','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/tech/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/photography/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/digest/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/job/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/life/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/free/','');

update phome_ecms_news_check set empireselfurl=replace(empireselfurl,'/reading/','');

update phome_ecms_news_check set filename = empireselfurl

将WordPress的标签复制到帝国cms的 标签 infotags

update phome_ecms_news_check_data set phome_ecms_news_check_data.infotags = (select phome_ecms_news_check.keyboard from phome_ecms_news_check where phome_ecms_news_check.id = phome_ecms_news_check_data.id)

2.系统设置——基本设置——系统参数设置——信息设置——相关链接依据

(选择:"标题包含与关键字相同")

3.系统设置——基本设置——数据更新中心

(更新"批量更新模型表单"+"批量更新相关链接")

将全部信息审核通过后，

使用代码更新帝国cms标签的数据表

在插件处文件夹 e/extend/ 建立文件夹 updatetags

目录结构

/e/extend/updatetags/index.php

/e/extend/updatetags/template/index.temp.php
