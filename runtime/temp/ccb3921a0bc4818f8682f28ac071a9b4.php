<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:76:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\show.html";i:1606900817;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<table width="100%" border="1px"  >
    <?php foreach($info as $k => $v): ?>
    <tr style="color:<?php echo $v['color']; ?>" class="span_school_num" data-id="<?php echo $v['school_num']; ?>" >
        <td >院校名称</td>
        <td ><?php echo $v['color']; ?></td>
        <td ><?php echo $v['school_name']; ?></td>
        <td >院校代码</td>
        <td ><?php echo $v['school_num']; ?></td>
        <td >公民办</td>
        <td ><?php echo !empty($v['school_type'])?$v['school_type']:''; ?></td>

    </tr>

    <?php endforeach; ?>
</table>

</body>
</html>
