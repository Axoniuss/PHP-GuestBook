<?php
  define('DIRECT_VISIT_CHECK', 'IN_GUESTBOOK');
  require_once("../../inc/core.class.php");

  session_start();
  if (isset($_SESSION['userid'])) {
    $user = new User($_SESSION['userid']);
  	if ($user->getLevel() != 1) {
  		die("您无权访问此页面。");
  	}
  } else {
    die("您未登录!! 请先登录!!");
  }

  $db = Database::getInstance();

  $comments = array();
  $comments = $db->Select("*", NULL, "comments", NULL, false);

  $PageSize = 10;
  if (count($comments) <= $PageSize) {
    $MaxPage = 1;
  } else {
    count($comments) % $PageSize > 0 ? $MaxPage = (count($comments)/$PageSize)+1 : count($comments)/$PageSize;
  }
  
  $CurrentPage = isset($_GET['page']) ? trim($_GET['page']) : '1';
  if ($CurrentPage < 1) $CurrentPage = 1;
  else if ($CurrentPage > $MaxPage) $CurrentPage = $MaxPage;
  $Begin = ($CurrentPage-1)*$PageSize;
  $End = $Begin + $PageSize;
  if ($End >= count($comments)) $End = count($comments);

  $HintType = isset($_GET['hinttype']) ? trim($_GET['hinttype']) : '';
  $HintMsg = isset($_GET['hintmsg']) ? trim($_GET['hintmsg']) : '';
?>

<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title>留言管理</title>

    <div class="row" style="padding-top: 10px;padding-left: 20px;">
   <?php if ($HintType=="succeed") { ?>
    <!--成功的提示-->
    <div class="alert alert-success col-sm-6" role="alert">
      <?php echo $HintMsg; ?>
    </div>
   <?php } else if ($HintType=="error") { ?>
    <!--失败的提示-->
    <div class="alert alert-danger col-sm-6" role="alert">
      <?php echo $HintMsg; ?>
    </div>
    <?php } ?>
  </div>
    <!-- Bootstrap -->
    <link href="../../css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim 和 Respond.js 是为了让 IE8 支持 HTML5 元素和媒体查询（media queries）功能 -->
    <!-- 警告：通过 file:// 协议（就是直接将 html 页面拖拽到浏览器中）访问页面时 Respond.js 不起作用 -->
    <!--[if lt IE 9]>
      <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
    /*表格内容上下居中*/
    .table td {
    	vertical-align: middle;
    }
	</style>
  </head>
  <body>
    
  <h2 class="row" style="padding-top: 10px;padding-left: 20px;" >留言管理</h2>

	<div class="row col-sm-6" style="padding-top: 10px;padding-left: 20px;">
      <table class="table table-hover">
		<tr>
			<th>文章编号</th>
			<th>发布者</th>
			<th>发布时间</th>
			<th>内容</th>
			<th>操作</th>
		</tr>
    <?php
      for ($i=$Begin; $i<$End; $i++) {
        $author = new User($comments[$i]['owner']);
        echo "
      <tr>
        <td>".$comments[$i]['id']."</td>
        <td>".$author->getUsername()."</td>
        <td>".$comments[$i]['date']."</td>
        <td>".strip_tags(substr($comments[$i]['text'],0,10))."...</td>
        <td>
          <a class=\"btn btn-primary\" href='comments_manage_do.php?id=".$comments[$i]['id']."'>编辑</a>
          <a class=\"btn btn-danger\" href='javascript:void(0);' data-toggle='modal' data-target='#deleteConfirmModal' onclick='ConfirmDelete(\"".$comments[$i]['id']."\");'>删除</a>
        </td>
      </tr>
        ";
      }
    ?>
		
	  </table>
    <?php if ($MaxPage >1) { ?>
    <div class="row" style="padding-top: 10px;padding-left: 20px;">
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li>
            <a href="#" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
          <?php 
            for ($i=1; $i<$MaxPage; $i++) {
              if ($i == $CurrentPage)
                echo "<li class='active'><a href='?page=".$i."'>".$i."</a></li>";
              else
                echo "<li><a href='?page=".$i."'>".$i."</a></li>";
            }
          ?>
          <li>
            <a href="#" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>
	  </div>
    <?php } ?>



    <!-- 删除确认框 -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">删除确认</h4>
          </div>
          <div class="modal-body" id="confirmMsg">
            
          </div>
          <div class="modal-footer">
            
            <form action="comments_manage_delete.php" method="get">
              <input type="hidden" id="cid" name="cid" />
              <button type="button" class="btn btn-default" data-dismiss="modal">再想想</button>
              <button type="submit" id="confirmBtn" class="btn btn-danger">确认删除！</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      function ConfirmDelete(cid) {
        $("#confirmMsg").text("您真的要删除文章 (编号: "+cid+") 吗？");
        $("#cid").attr("value", uid);
      }
    </script>


    <!-- jQuery (Bootstrap 的所有 JavaScript 插件都依赖 jQuery，所以必须放在前边) -->
    <script src="../../js/jquery.min.js"></script>
    <!-- 加载 Bootstrap 的所有 JavaScript 插件。你也可以根据需要只加载单个插件。 -->
    <script src="../../js/bootstrap.min.js"></script>
  </body>
</html>

