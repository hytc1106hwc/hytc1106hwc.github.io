---
layout:		post
title:		使用线程来执行任务
desc:		怎样在单线程中顺序执行任务，或者为每一个任务创建一个线程
date:		2018-02-09
permalink:	/java/java-concurrency-in-practice/task-execution/executing-tasks-in-threads/
tags:		[java, jcip, task-execution]
---

围绕任务执行来组织程序的第一步是**辨识合理的任务边界**。理想情况下，任务是独立的活动：不依赖其他任务状态、结果或副作用的工作。独立性使得并发变得容易，因为在处理资源足够的前提下，独立任务可以并发执行。为了在调度上有更大的灵活度和加载平衡任务，每一个任务代表着应用程序处理能力的一个小片段。

服务端应用程序应该在正常加载情况下同时兼备好的吞吐量和好的响应能力。应用程序提供方想要让应用程序尽可能地支持更多的用户，以便减少单用户供应成本；用户希望能够得到快速的响应。而且，当应用程序过载后会变得功能退化，而不是在在重负荷情况下瘫痪。选择合适的任务边界及合理的任务执行策略可以帮助实现这些目标。

**大多数服务端应用程序为任务边界提供了一个默认选择，就是单客户端请求**。web服务器，邮件服务器，文件服务器，EJB容器和数据库服务器都接受来自远程客户端通过网络连接发来的请求。将单客户端请求作为任务边界，通常既提供了独立性，又提供了合适的任务大小。比如说，给邮件服务器发送消息，并不受其他正在编写中的消息的影响，并且处理单独的消息通常需只需要极少的服务器存储容量。

在线程中执行任务有两种方式：一种是在单线程中顺序执行任务，一种是为每一个任务创建一个线程进行处理。

### 1. 在单线程中顺序执行任务
```java
import java.util.ArrayList;
import java.util.List;

public abstract class SingleThreadRenderer {

	/** render the page */
	void renderPage(CharSequence source) {
		renderText(source);
		List<ImageData> imageData = new ArrayList<ImageData>();
		for (ImageInfo imageInfo: scanForImageInfo(source)) {
			imageData.add(imageInfo.downloadImage());
		}

		for (ImageData data: imageData) {
			renderImage(data);
		}
	}

	interface ImageData {
	}
	interface ImageInfo {
        ImageData downloadImage();
    }

	/** render the text */
	abstract void renderText(CharSequence source);
	/** extract image data from the source */
	abstract List<ImageInfo> scanForImageInfo(CharSequence s);
	/** render image */
	abstract void renderImage(ImageData i);
}
```
说明：
- 单线程执行任务说明
单线程Web服务器很简单，并且从理论上来说是正确的，但是在生产环境中，效率将会很低，因为**服务器一次只能执行一个请求。当服务器在处理一个请求的时候，新的连接必须一直等到当前请求被处理完，然后再次调用accept函数才能执行**。如果处理请求的速度很快，从而使handleRequest函数能够快速返回，那么新连接会很快得到处理，但是实际情况下，这种服务器并不存在。

- **单线程执行任务的问题**
处理Web请求的过程可能涉及到复合运算和I/O。服务器必须从Socket I/O中读取请求，并把响应写到I/O中，这个过程会由于网路拥挤或连接问题而导致线程阻塞，也可能会执行文件I/O操作或发出数据库请求而导致阻塞。在单线程服务器中，阻塞不但会使当前请求完成时间延迟，也会让后续请求不能够被处理。如果某个请求阻塞了很长时间，用户可能会认为服务器不可访问，因为请求并没有得到相应。同时，资源利用率也会变得很差，由于在单线程等待I/O完成的时候，CPU是空闲状态。
在服务端应用程序中，**顺序处理几乎不能提供好的吞吐量和好的响应**。但也有一些例外情况，比如当任务极少并长时间执行的情况下，或当服务器只服务一次只发送一个请求的客户端时。但是大多数服务端应用程序都不适用单线程来处理。

### 2.每个任务一个线程
比单线程顺序执行任务稍微好一点的方法是：为每个任务创建一个新的线程
```java
import java.io.BufferedInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.ServerSocket;
import java.net.Socket;

public class ThreadPerTaskWebServer {

	public static void main(String[] args) throws IOException {
		try (ServerSocket socket = new ServerSocket(55555);) {
			while (true) {
				final Socket connection = socket.accept();
				Runnable task = new Runnable(){
					@Override
					public void run() {
						handleRequest(connection);
					}
				};
				new Thread(task).start();
			}
		}
	}

	/**
	 * handle each request
	 */
	private static void handleRequest(Socket conn) {
		try {
			System.out.println("Connection from: " + conn.getRemoteSocketAddress());
			readMsgFromClient(conn.getInputStream());
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	/**
	 * Read message from the socket
	 * @param in
	 */
	private static void readMsgFromClient(InputStream in) {
		try (BufferedInputStream bin = new BufferedInputStream(in);) {
			int ch;
			while ((ch = bin.read()) != -1) {
				System.out.printf("%c", ch);
			}
			System.out.println();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}
```
说明：
ThreadPerTaskWebServer服务器与单线程版本的服务器很相似，主线程仍然轮流处理接受连接和转发请求。不同的是对于每个连接，主循环为其创建一个新的线程来处理请求，而不是在主线程中处理请求。这有三个优势：
1. **任务处理从主线程中剥离出来，使得主循环更快地接受下一个连接**。这使得新连接在前一个连接完成之前建立起来，加快了响应。
2. **任务可以被并行处理，使得多个请求能够被同时处理**。如果有多个处理器，或由于I/O，锁获取或资源获取等原因导致任务阻塞，这就会提高吞吐量。
3. **任务处理代码必须确保是线程安全的**，因为代码可能会被多个任务同时调用到。
**只要请求到达率不超过服务器处理请求的容量，这个方法能够收获更好的响应时间和吞吐量**。

#### 使用每个任务一个线程的不足
如果要用于生产环境中，使用每个任务一个线程的方法有一些实际的缺点，特别是当大批量线程可能被创建的时候。有下面一些方面：
**线程生命周期开销**。线程创建与线程销毁并不是不花费代价的。不同平台实际的开销情况不同，但是线程创建需要时间，在请求处理时引入了延迟，并且需要虚拟机JVM和操作系统OS做一些处理。如果请求是频繁、轻量级的，为每一个请求创建一个新线程会消耗重要的计算资源。

**资源消耗**。**运行的线程会消耗系统资源，特别是内存资源**。当可运行的线程数量超过处理器数量时，线程会进入休眠状态。**大量的休眠线程会占用大量内存，给垃圾回收带来了压力**，并且使很多线程竞争CPU资源会影响其他性能。如果有充足的线程使得CPU忙碌，创建更多的线程不会带来好处，并可能会对系统有害。

**稳定性**。对创建线程的数量应该有限制。这个限制跟平台有关，并会受到一些因素影响，包括JVM调用参数，线程构造器中请求的栈大小 和 底层操作系统对线程的限制。当线程个数达到了这个限制，最可能的结果是抛出OutOfMemoryError。尝试从这种错误中恢复很危险，要规划好自己的程序，避免达到这种限制。

**在一定范围内，多个线程能够提高吞吐量，但是超过了这个范围，创建更多的线程只会让应用程序变慢，并且多次创建一个线程会导致整个应用程序崩溃**。要避免危险，就**需要对线程创建个数做出限制，并且要仔细测试应用程序来确保即使达到了临界点，也不会耗尽资源**。
