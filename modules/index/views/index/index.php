<?= $this->call("index/index/header", array('title' => 'Главная')); ?>

<canvas id="area" width="200" height="200" style="border:1px solid #000000;">
</canvas>
<script>
var c = document.getElementById("area");
var ctx = c.getContext("2d");
ctx.moveTo(0, 0);
ctx.lineTo(200, 200);
ctx.moveTo(200, 0);
ctx.lineTo(0, 200);
ctx.moveTo(100, 0);
ctx.lineTo(100, 200);
ctx.stroke();
</script>

<?= $this->call("index/index/footer"); ?>