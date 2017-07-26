# CatFramework
I AM CHINAESE
这是闲来无事自己开发的一个PHP框架
代码可能差一些， 但是还是兼容了很多的思想
可扩展性
CatFramework
------common
	-------[function.php]      框架共用方法

------config
	-------[conf.common.php]   框架配置文件
	-------[conf.core.php]     核心配置文件

------core【内核】
	-------FW【内核运行文件】
		--------[FW.php]       		框架初始化
		--------[FWException.php] 	异常处理文件
		--------[FWLibBase.php] 	框架基类
		--------[FWRoute.php] 		框架路由转换
	-------PACKAGE【内核扩展包：可扩展】
		--------DB【数据操作包】
			--------[DB.model.php] 		包入口文件
			--------[Mysql.model.php] 	包执行文件
		--------REDIS
			--------[REDIS.model.php]
		--------CURD
			--------[CURD.model.php]
			--------[MysqlCURD.php]
			--------templates
				--------[CURD.js]
				--------[ITEM.html]
				--------[LIST.html]
				--------[MENU.html]
	.
	.
	.

------lib【执行代码基类】
	--------controller
	--------action
	--------model
	--------object
	--------function
	
------[application.php] 框架初始化文件