<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:75:"E:\phpstudy_pro\WWW\fw366.cn\public/../application/index\view\test\lay.html";i:1606820723;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <!-- import CSS -->
    <link rel="stylesheet" href="/static/css/index.css">
</head>
<style>
    .el-header, .el-footer {
        background-color: #B3C0D1;
        color: #333;
        text-align: center;
        line-height: 100px;
    }
    .el-main {
        background-color: #E9EEF3;
        color: #333;
        text-align: center;
        line-height: 500px;
    }
</style>
<body>
<div id="app">
    <el-container>
        __HEADER__
        <el-main>Main</el-main>
        <el-footer>Footer</el-footer>
    </el-container>
</div>
</body>
<!-- import Vue before Element -->
<script src="/static/js/vue.js"></script>
<!-- import JavaScript -->
<script src="/static/js/index.js"></script>
<script>
    new Vue({
        el: '#app',
        data: function() {
            return { visible: false }
        }
    })
</script>
</html>