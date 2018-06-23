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

index.php代码如下：

<?php

require('../../class/connect.php'); //引入数据库配置文件和公共函数文件

require('../../class/db_sql.php'); //引入数据库操作文件

require('../../data/dbcache/class.php'); //引入栏目缓存文件

$link=db_connect(); //连接MYSQL

$empire=new mysqlquery(); //声明数据库操作类

$editor=1; //声明目录层次

$sql=$empire->query("select * from phome_ecms_news where classid in ('10','11','12','13','14','15','16') order by newstime limit 99999");

require('template/index.temp.php'); //导入模板文件

db_close(); //关闭MYSQL链接

$empire=null; //注消操作类变量

?>

/e/extend/updatetags/template/index.temp.php的代码如下：

<?php

if(!defined('InEmpireCMS'))

{

exit();

}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>操作结果</title>

</head>

<body>

<br>

<br>

<br>

<table width="500" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#CCCCCC">

<tr> 

<td height="25"><strong>操作结果</strong></td>

</tr>

<tr> 

<td height="100%" bgcolor="#FFFFFF"> <div align="center"><strong><font color="#FF0000" size="5">

<?php

while($rrrr=$empire->fetch($sql))        //循环获取查询记录

{

?>

<?php

$classid = $rrrr[classid];

$id = $rrrr[id];

$tags = $rrrr[keyboard];

$newstime = $rrrr[newstime];

//加入TAG表

$tags = RepPostVar($tags);

//$tag = explode(",", $tags);

$count = count($id); //统计ID数量

if (empty($count))

{//如果id没选中

printerror("无信息ID", "", 1, 0, 1);

}

$classid=(int)$classid;

$mid=9;//取modid值

for($i=0;$i<$count;$i++)

{

$id = (int)$id;

$t = $empire->fetch1("select infotags from phome_ecms_news_data_1 where id='$id'");//从信息表中取infotags和keyboard值

$tagb[$i] = explode(",",$tags); //设置数组:用,分割tag

$tagc=array_values(array_unique($tagb[$i])); //数组排重:排除重复?

for($t=0;$t<count($tagb[$i]);$t++)

{//二级子循环TAGS数组输出

$newtags[$i].= ",".$tagc[$t];

$r=$empire->fetch1("select tagid from phome_enewstags where tagname='$tagc[$t]' limit 1");//查询有无同名的tag

if($r[tagid])

{//如果有tagid,即enewstags表中有相同tag

$datar=$empire->fetch1("select tagid,classid,newstime from phome_enewstagsdata where tagid='$r[tagid]' and id='$id' and mid='$mid' limit 1");//用tagid,id和mid对enewstagsdata进行查询

if($datar[tagid])

{//如果有数据

if($datar[classid]!=$classid||$datar[newstime]!=$newstime)

{//如果classid和newstime不相同

$empire->query("update phome_enewstagsdata set classid='$classid',newstime='$newstime' where tagid='$r[tagid]' and id='$id' and mid='$mid' limit 1");//则开始更新

}

}

else

{//查询后没有此数据,则先更新enewstags表,在数量上加1

$empire->query("update phome_enewstags set num=num+1 where tagid='$r[tagid]'");

$empire->query("insert into phome_enewstagsdata(tagid,classid,id,newstime,mid) values('$r[tagid]','$classid','$id','$newstime','$mid');");//然后在enewstagsdata表中插入这些数据

}

else

{//如果没有此tag

$empire->query("insert into phome_enewstags(tagname,num,isgood,cid) values('$tagc[$t]',1,0,0);");//在enewstags表中插入新值

$tagid=$empire->lastid();//把这个tagid给取出来

$empire->query("insert into phome_enewstagsdata(tagid,classid,id,newstime,mid) values('$tagid','$classid','$id','$newstime','$mid');");//既然是没有tagid的,那就在enewstagsdata也得插入新值(不用再查询)

}

}            

}

}

?>

<?php

}

?> 

</font></strong></div></td>

</tr>  

<tr> 

<td height="60" bgcolor="#FFFFFF"> <div align="center"><strong><font color="#FF0000" size="5">看到这句话表示成功了。</font></strong></div></td>

</tr>  

</table>

</body>

</html>
