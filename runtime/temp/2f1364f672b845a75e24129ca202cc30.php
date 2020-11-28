<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:77:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\index.html";i:1606441358;s:67:"E:\phpstudy_pro\WWW\fw366.cn\application\index\view\lay\layout.html";i:1606441358;}*/ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
aaa
</body>
</html>

<form action="index/Check/get_info" method="post" >
    <?php @csrf ?>
    请输入分数：
    <input type="text" name="score">
    <select name="year" >
        <option value="2017" selected>2017</option>
        <option value="2018">2018</option>
        <option value="2019">2019</option>
        <option value="2020">2020</option>
    </select>
<!--    <select name="status">-->
<!--        <option value="1" selected>冲刺</option>-->
<!--        <option value="2">保守</option>-->
<!--        <option value="3">保底</option>-->
<!--    </select>-->
        <select name="batch">
            <option value="1" selected>一批</option>
            <option value="2">二批</option>
            <option value="3">三批</option>
            <option value="4">专科</option>
        </select>
    <select name="type">
        <option value="reason" selected>理科</option>
        <option value="culture">文科</option>
    </select>
    <br>
    <input type="submit" value="确认">
</form>
<!--
<script src="/jquery-3.5.1.min.js"></script>
<script>
   $(document).ready(function () {
            $("#chong").click(function (){
                    alert("chong");
                });
    });
</script>
-->


