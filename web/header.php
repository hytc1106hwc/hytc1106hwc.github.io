<head>
  <meta charset="utf-8">
  <title>Zanblog,基于bootstrap3的wordpress博客主题</title>
  <!-- <title><?php bloginfo(’name’); ?></title> -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Le styles -->
  <link href="<?php bloginfo('stylesheet_url');?>" rel="stylesheet">


  <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
  <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <?php wp_enqueue_script("jquery"); ?>
  <?php wp_head(); ?>
</head>
<body>
<div id="zan-header" class="navbar navbar-inverse navbar-fixed-top bs-docs-nav">
  <div class="container">

  <a class="navbar-brand" href="<?php echo site_url(); ?>"><?php bloginfo('name'); ?></a>
  <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
    <div class="nav-collapse collapse bs-navbar-collapse">
      <?php
        $defaults = array(
          'menu_class' => 'nav navbar-nav',
        );
        wp_nav_menu( $defaults );
      ?>
          <div class="search visible-lg">
       <form method="get" id="searchform" class="form-inline" action="<?php bloginfo('url'); ?>">
            <input class="text input-small" type="text" value=" " name="s" id="s" placeholder="搜索" />
            <input type="submit" class="btn btn-default btn-small" name="submit" value="<?php _e('Search');?>" />
      </form>
    </div>
    </div>
  </div>
</div>
<!-- //zan-header -->