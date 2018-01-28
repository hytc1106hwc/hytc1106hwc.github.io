---
layout:     post
title:      字符串操作-编写C函数技术(二)
desc:       在C函数中怎样把字符串压入到栈中
date:       2018-01-27
permalink:  /lua/how-to-write-c-functions/string-operations/
tags:       [lua, how-to-write-c-functions]
---

## 规则
当C函数接受Lua中的字符串作为参数时，需要遵循两条规则：

 **1.使用字符串的时候，不需要将字符串弹出栈**

 **2.不能修改字符串内容**

## 字符串操作函数

### 字符串抽取——lua_pushlstring函数
要向栈中压入字符串s从i到j(包含)的子字符串，可以使用下面语句:

```ruby
    lua_pushlstring(L, s+i, j-i+1)
```

 **例子**

```ruby
MYCLIBS_API int myclibs_split(lua_State *L)
{
  const char *s = luaL_checkstring(L, 1);       /* original string */
  const char *sep = luaL_checkstring(L, 2);     /* seperator */
  const char *e;

  int i = 1;
  lua_newtable(L);  /* result table */
  while ((e = strchr(s, *sep)) != NULL)
  {
        lua_pushlstring(L, s, e - s);
        lua_rawseti(L, -2, i++);
        s = e + 1;
  }

  lua_pushstring(L, s);
  lua_rawseti(L, -2, i);
  return 1;
}

/*
 * will print
 *    1  hello
 *    2  world
 */
local split_result = myclibs.split("hello world", " ")
for i, v in ipairs(split_result) do
    print(i, v)
end
```

>**说明**
>这是字符串分割函数。
>strchr(const char *str,  int c)
>在str指针所指向的不可变字符串中查找第一个与c相匹配的
>函数返回第一个匹配项的指针，如果找不到匹配项，返回NULL。


```ruby
MYCLIBS_API int myclibs_split2(lua_State *L)
{
  const char *s = luaL_checkstring(L, 1);
  const char *sep = luaL_checkstring(L, 2);

  lua_newtable(L);                 /* create a table for stroing results */

  size_t i = 1, len = (size_t)luaL_len(L, 1);
  //printf("**%d\n", sep);
  const char *e;
  while ((e = (const char *)memchr(s, *sep, len)) != NULL)
  {
    lua_pushlstring(L, s, e - s);  /* push substring */
    lua_rawseti(L, 3, i++);        /* insert it into the result table */
    s = e + 1;                     /* skip seperator */
  }

  /* insert last string */
  lua_pushstring(L, s);
  lua_rawseti(L, 3, i);
  return 1;  // retunrn the table
}

/*
 * will print
 *    1  hello
 *    2  world
 */
local split_result = myclibs.split2("hello world", " ")
for i, v in ipairs(split_result) do
  print(i, v)
end
```

> 说明

>这是第二种形式的字符串截取函数在这里使用了memchr函数memchr(const void* buf, int val, size_t maxcount)
> 功能：在buf中的前maxcount字节中查找val的第一个匹配项
> 参数说明：
    buf:  指向缓冲区的指针
    val:   要查找的字符
    maxcount: 要检查的字符数

### 字符串拼接——lua_concat函数

   要连接字符串，lua提供了lua_concat函数，它与..操作符相同，会将数字转成字符串，并在有必要的时候，调用元表方法。

lua_concat(L, n): 该函数将栈中从栈顶开始的n个元素弹出，并连接起来，并将连接后的结果放在栈顶。

**lua_pushfstring()函数**
  ```ruby
    const char *lua_pushfstring(lua_State *L, const char *fmt, …);
  ```

> 说明

> 这个函数与C中的sprintf函数类似，但是不需要提供buffer。Lua动态按需要创建字符串。
> 函数将结果字符串压入栈，并返回一个指向这个元素的指针。
> 格式字符串列表，**格式控制符不接受任何修饰，包括宽度和精度**。

![格式字符串列表]({{ "/assets/lua/how-to-write-c-functions/format-symbol.png" | absolute_url }})

#### **大批量字符串拼接————“String Buffer”**
  Lua辅助库中提供的buffer资源，提供了两个函数：
  一个函数用来获取一个任意大小的buffer，通过这个buffer可以来拼接字符串；
另一个函数将buffer中的内容转换成Lua字符串。
使用buffer的步骤：

 1. 声明一个luaL_Buffer变量
 2. 使用luaL_bufferinitsize获取一个指向指定大小的buffer的指针
 3. 向buffer中添加内容
 4. 使用luaL_pushresultsize将buffer中的内容转换成Lua字符串，并压入栈中

> 说明

>1.**如果事先不确定buffer的大小**，

>在初始化buffer的时候，应该使用**luaL_buffinit**函数

```ruby
    void luaL_buffinit(lua_State *L, luaL_Buffer *B);
```

>在最后转换的时候，使用**luaL_pushresult**函数

```ruby
    void luaL_pushresult(luaL_Buffer *B);
```

>2.可以**逐个地往buffer中添加内容**，使用下列函数：
```ruby
    void luaL_addvalue(luaL_Buffer *B);
    void luaL_addlstring(luaL_Buffer *B, const char *s, size_t l);
    void luaL_addstring(luaL_Buffer *B, const char *s);
    void luaL_addchar(luaL_Buffer *B, char c);
```

**例子**
```ruby
/* changer each char of a string to its upper case */
MYCLIBS_API int myclibs_upper(lua_State *L)
{
  size_t len;
  size_t i;

  /* first, declare a luaL_Buffer variable */
  luaL_Buffer b;
  const char *s = luaL_checklstring(L, 1, &len);

  /* second, get a pointer for the buffer with the given size */
  char *p = luaL_buffinitsize(L, &b, len);

  /* then, create our string using the buffer */
  for (i = 0; i < len; i++)
  {
    p[i] = toupper(UCHAR(s[i]));
  }

  /*
   * last, convert the buffer contents into a Lua string,
   * and push that string onto the stack
   */
  luaL_pushresultsize(&b, len);
  return 1;
}
```