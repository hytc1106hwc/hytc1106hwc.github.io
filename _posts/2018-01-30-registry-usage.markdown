---
layout:		post
title:		Registry的使用
desc:		怎样使用Lua中的Registry
date:		2018-01-30
permalink:	/lua/how-to-write-c-functions/registry-usage/
tags:		[lua,how-to-write-c-functions,registry]
---
### 介绍
Registry是一个位于伪索引LUA_REGISTRYINDEX处的全局table，确切的说，是一个常规table（也就是说，它没有元表）
伪索引和栈中的索引相似，不同的是，与伪索引关联的值并不在栈中。
Lua API中大多数接受索引作为参数的函数，也接受伪索引作为参数。
> 说明：
>
> 1.由于所有的C模块共享相同的registry，所以在挑选key的时候要特别小心，避免名称冲突。
>
> 使用字符串作为键值，在我们希望其他独立库访问我们数据的时候很有用。**在选择字符串作为键的时候，建议不要使用通用名称，且不要在名称前加上库的名字**。
>
> 不要使用数字作为注册表的键，因为Lua会为其引用系统保留数字键。这个系统是有辅助库中的一对函数组成，允许我们在不需要担心怎样创建键的前提下存储值。注意，**只有在我们需要将引用存储到C结构中的Lua值中时候，才会考虑使用引用**。
>```ruby
> int ref  = luaL_ref(L, LUA_REGISTRYINDEX);
>```
> 由于Lua没有提供任何指向table或function的指针类型，所以在需要访问这些对象的时候，可以使用引用，并将引用保存在C代码中。
>
> 2.要将与引用关联的值压入栈中，使用
>```ruby
> lua_rawgeti(L, LUA_REGISTRYINDEX, ref)
>```
>
> 3.要释放引用及其关联的值，调用
>```ruby
> lua_unref(L, LUA_REGISTRYINDEX)
>```
>
> 4.引用系统对nil值有特殊作用，当使用nil值调用lua_ref函数时，不会得到一个新引用，而是返回常量引用值LUA_REFNIL
>```ruby
> luaL_unref(L, LUA_REGISTRYINDEX, LUA_REFNIL)   /*不执行任何操作*/
>```
>
>```ruby
> luaL_rawgeti(L, LUA_REGISTRYINDEX, LUA_REFNIL) /*向栈中压入nil值*/
>```

### 使用方法
- 存储数据的标准步骤

1. 定义一个字符串键，可以使用宏来定义
2. 将地址和值分别入栈：使用lua_pushlightuserdata方法将键压入栈，然后将值压入栈
3. 使用lua_settable方法将值保存到Registry中与键所对应地方（注：设置好之后，栈中的键与值会被弹出栈）

- 获取数据的标准步骤

1. 将地址入栈
2. 使用lua_gettable方法获取值
3. 对值进行适当的转换

- 存储数据的快捷方法

1. 先将值入栈
2. 使用**lua_rawsetp(L, LUA_REGISTRYINDEX, (void *)&key)**将值保存到Registry table中(此时会将值从栈中弹出)

- 获取数据的快捷方法

1. 使用**lua_rawgetp(L, LUA_REGISTRYINDEX, (void *)&key)**将获取到的值压入栈
2. 对值进行适当的转换

#### 例子
- 将地址定义成宏
```ruby
#define TRANS_KEY ((char)'t')
```
- 定义主要方法
```ruby
static int myclibs_transliterate_arr(lua_State *L, int count)
{
	size_t i, len;
	const char *str = luaL_checklstring(L, -2, &len);  /* first argument should be a string */
	luaL_checktype(L, -1, LUA_TTABLE);    /* second argument should be a table */
	// string buffer
	luaL_Buffer b;	/* declare a luaL_Buffer variable */
	luaL_buffinitsize(L, &b, len);    /* initialize the Buffer */

	for (i = 0; i < len; i++)
	{
		char ch[] = "x";
		ch[0] = str[i];
		lua_getfield(L, -1, ch);
		/*
		 *  lua_pushfstring(L, "%c", ch);		/* push key */
		 *  lua_rawget(L, -2);			/* get table[key] */
		 */
		int nil = lua_isnil(L, -1);
		if (nil) {
			lua_pop(L, 1);
		}
		else {
			const char *result = lua_tostring(L, -1);
			luaL_addvalue(&b);    /* add result to buffer */
		}
	}

	if (count == NULL) {
		lua_pushfstring(L, "%s\n", "calling with one argument");
	}
	else {
		lua_pushfstring(L, "%s\n", "calling with two argument");
	}
	luaL_pushresult(&b);		/* push the result string onto the stack */
	return 2;
}
```

- 保存数据的方法

	```ruby
	/* set the transliteration table using registry */
	MYCLIBS_API int myclibs_settrans(lua_State *L)
	{
		luaL_checktype(L, 1, LUA_TTABLE);			/* first argument should be a table */
		lua_pushlightuserdata(L, (void *)TRANS_KEY);	/* push address */
		lua_pushvalue(L, 1);					/* push value */
		lua_settable(L, LUA_REGISTRYINDEX);		      /* registry[&key] = table */
		return 0;
	}
	```

- 获取数据的方法

	```ruby
	/* get the transliteration table from the registry table */
	MYCLIBS_API int myclibs_gettrans(lua_State *L)
	{
		lua_pushlightuserdata(L, (void *)TRANS_KEY);	/* push address */
		lua_gettable(L, LUA_REGISTRYINDEX);	       /* retrieve the table and push it onto the stack */
		return 1;
	}
	```

- 定义给外部访问的函数

	```ruby
	/* transliterate function */
	MYCLIBS_API int myclibs_transliterate(lua_State *L)
	{
		// get and push table onto the stack
		int top = lua_gettop(L), nres = 0;

		//printf("%d\n", top);
		if (top == 1)
		{
			myclibs_gettrans(L);
			nres = myclibs_transliterate_arr(L, NULL);
		}
		else if (top == 2) {
			nres = myclibs_transliterate_arr(L, 2);
		}
		else
		{
			luaL_error(L, "bad argument size");
		}
		return nres;
	}
	```