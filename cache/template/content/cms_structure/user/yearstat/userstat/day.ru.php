<h1>Статистика регистрации за день</h1>
<br>
<div id="chartdiv"></div>
<script>
var xml = "<?php echo $this->vars['chart_xml']; ?>";
renderDataChart('StackedColumn3D.swf', 600, 320, 'chart', xml);
</script>

<?php echo $this->vars['cms_table']; ?>