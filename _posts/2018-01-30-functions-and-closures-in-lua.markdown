---
layout:		post
title:		Lua中的函数和闭包
desc:		Lua中的函数与闭包的实现机制
date:		2018-01-30
pemalink:	/lua/how-to-write-c-functions/functions-and-closures-in-lua/
tags:		[lua, techniques]
---

当Lua**编译一个函数的时候，会产生一个prototype**，这个prototype包含了这个函数的**虚拟机器指令**、**常量值**和**一些调试信息**。

在运行的时候，任何时候，**Lua执行一个function…end表达式的时候，会为这个函数创建一个新的闭包（closure）**。

每一个闭包包含“**两个引用和一个引用数组**”：

 - 对prototype的引用
 - 对闭包环境的引用（这个环境是一个table，通过它可以查找全局变量）
 - 对upvalue的引用构成的数组，可以通过这些引用来访问外部的local变量

词法作用域和一流函数的结合为对外部的local变量的访问带来的困难。

```ruby
    add函数                     调用过程
function add (x)              add2 = add(2)
    return function (y)       print(add2(5))
        return x+y
    end
end
```
> 说明
>
> 在add2被调用的时候，函数体部分需要访问外部的local变量x。但是等到add2被调用的时候，创建add2的函数add已经返回了，如果变量x是在栈中被创建的，存储它的栈存储单元在函数返回时已经不存在了。

大多数过程式语言，通过限制词法作用域（像Python语言），或不提供一流函数（像Pascal语言），或都限制两者（像C语言），来解决上面对访问外部变量的local变量困难的问题。研究表明，非纯正的函数式语言，像Scheme和ML语言，对闭包的编译技术创建了一大堆知识。但是这些努力并没有限制编译器的复杂度。比如，仅仅Bigloo的控制流分析，一种优化的scheme编译器，是Lua的实现的10倍大。

**Lua使用一个叫做upvalue的结构来实现闭包**。对任何外部的local变量的访问都是通过upvalue来进行的。upvalue原本指向变量所在的栈的槽位。当变量离开了作用域，变量会迁移到upvalue本身的槽位中。因为变量是间接通过upvalue中的指针访问的，这个迁移对任何读写变量的代码都是透明的。不像内部函数，声明变量的函数访问变量就像访问自己的local变量一样：直接在栈中。

**可变状态可以在闭包间正确地被共享，只要为每个变量创建至多一个upvalue结构，并在需要时重新利用它就行了**。要确保这种唯一性，Lua保存了一个链表，这个链表包含了所有打开的upvalue（也就是说，这些upvalue仍然指向栈）。当Lua创建了一个闭包，会遍历所有外部的local变量。对于每个变量，如果能够在链表中找到一个打开的upvalue，就会重利用这个upvalue。否则，Lua会创建一个新的upvalue,并链接到这个upvalue。注意，搜索链表的时候，只会搜索部分节点，因为对于每个被内部函数使用的local变量，链表中至多有一个入口。一旦关闭的upvalue不再被任何闭包所使用，这个upvalue入口就会被当做垃圾回收。

函数访问不属于自己的封闭函数，而属于某个外部函数的外部local变量是可能的。在这种情况下，即使闭包已经被创建，变量可能在栈中不存在。Lua通过使用flat closure解决了这个问题。使用flat closure，任何时候某个函数访问不在自己封闭函数中的外部变量，这个变量也会进入到封闭函数的闭包中。因此，当一个函数被初始化后，这个函数用到的所有闭包中的变量，要么在该函数的栈中，要么在该函数的闭包中。

![Upvalue开启与关闭时结构]({{ "/assets/lua/how-to-write-c-functions/open-and-closed-upvalue.png" | absolute_url }})

- 相关资料

	Lua发起人向JUCS提交的论文 [[下载]]({{ "/assets/attachments/The-Implementation -of-Lua-5.0.pdf" | absolute_url }})

