# CatFramework<br>
I AM CHINAESE<br>
这是闲来无事自己开发的一个PHP框架<br>
代码可能差一些， 但是还是兼容了很多的思想<br>
可扩展性<br>
CatFramework<br>
------common<br>
-----------[function.php]      框架共用方法<br>
<br>
------config<br>
-----------[conf.common.php]   框架配置文件<br>
-----------[conf.core.php]     核心配置文件<br>
<br>
------core【内核】<br>
-----------FW【内核运行文件】<br>
----------------[FW.php]       -----框架初始化<br>
----------------[FWException.php] --异常处理文件<br>
----------------[FWLibBase.php] ----框架基类<br>
----------------[FWRoute.php] ------框架路由转换<br>
-----------PACKAGE【内核扩展包：可扩展】<br>
----------------DB【数据操作包】<br>
--------------------[DB.model.php] -------包入口文件<br>
--------------------[Mysql.model.php] ----包执行文件<br>
----------------REDIS<br>
--------------------[REDIS.model.php]<br>
----------------CURD<br>
--------------------[CURD.model.php]<br>
--------------------[MysqlCURD.php]<br>
--------------------templates<br>
------------------------[CURD.js]<br>
------------------------[ITEM.html]<br>
------------------------[LIST.html]<br>
------------------------[MENU.html]<br>
...
...
...

------lib【执行代码基类】<br>
------------controller<br>
------------action<br>
------------model<br>
------------object<br>
------------function<br>
<br>
------[application.php] 框架初始化文件<br>
