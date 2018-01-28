---
layout:     post
title:      数组操作-编写C函数技术(一)
date:       2018-01-27
permalink:  /lua/how-to-write-c-functions/array-operations/
tags:       [lua, how-to-write-c-functions]
---

### lua中的数组，仅仅是**一个以特殊方式使用的table**。
可以使用操作table的方法lua_settable和lua_gettable来操作数组。但是也可以使用下面的方法来访问和更新数组中的元素值。

```ruby
    void lua_seti(lua_State *L, int index, int key)
    void lua_geti(lua_State *L, int index, int key)
```
> 说明：
>1. lua5.3之前，使用的是lua_rawgeti和lua_rawseti方法，这两个方法和上面两个方法相似，但是**不访问元表方法**。**当table没有元表的时候，使用后两种方法速度更快**。
>2. 参数说明：
    index是table在栈中的索引
    key是元素的键
>3. **lua_geti(L, t, key)** 语句与下面语句等效（前提是：t是正值）

```ruby
    lua_pushnumber(L, key);
    lua_gettable(L, t);
```

>4.**lua_seti(L, t, key)** 语句与下面语句等效（前提是：t是正值）

```ruby
    lua_pushnumber(L, key)
    lua_insert(L, -2);
    lua_settable(L, t);
```
