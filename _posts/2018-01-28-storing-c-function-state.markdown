---
layout:		post
title:		存储C函数中的状态
desc:		C函数中有些状态是怎么存储的
date:		2018-01-28
permalink:	/lua/how-to-write-c-functions/storing-c-function-state/
tags:		[lua, how-to-write-c-functions]
---

在C语言中，我们经常使用全局（extern）或静态变量来保存非本地变量，以便这些变量可以在函数外被使用。

当我们在编写给Lua调用的C库函数时，**定义全局或静态变量是不起作用的**。

- 首先因为不能将Lua值保存在C变量中，
- 其次，使用这些变量的库函数对多个Lua状态不支持

Lua函数有两个地方可以存储非本地变量：全局变量和非本地变量。

CAPI提供了提供了两种相似的地方，用来存储非本地数据：**Registry**和**Upvalues**。

- [Registry]({{site.baseurl}}/lua/how-to-write-c-functions/registry-usage/)

- [Upvalues]({{site.baseurl}}/lua/how-to-write-c-functions/upvalue-usage/)
