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
