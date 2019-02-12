<?= $this->call("index/index/header", array('title' => 'Главная')); ?>

test page

<form method="post">
<input type="text" name="test" value="1" />
<button>send</button>
</form>

<?= $this->call("index/index/footer"); ?>