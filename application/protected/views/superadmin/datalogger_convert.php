<style>
    textarea {
        width: 100%;
        height: 200px;

    }
    h2 {
        margin: 20px 0;
    }
</style>
<div class="middlenarrow">
    <h1>Convert</h1>

    <form id="form" method="post">
        <h2>Station</h2>
        <?php echo CHtml::dropDownList('station',current($stations),$stations)?>
        <h2>Source</h2>
        <textarea name="source" form="form" style="width: 100%"><?=$source ?></textarea><br>
        <h2>Convert</h2>
        <textarea name="convert" form="form" style="width: 100%" disabled><?=$convert ?></textarea><br>
        <input type="submit" value="Convert">
    </form>

</div>