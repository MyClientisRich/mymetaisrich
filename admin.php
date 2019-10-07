<?php
    if(isset($_POST['go__import'])) {
        mmir_importMetas();
    }

    if(isset($_POST['go__export'])) {
        mmir_exportMetas();
    }
?>

<section class="mmir__export mmir--section">
    <div class="title">1. Export your pages data</div>

    <div class="section__subtitle">
        Click the button, sit back and relax. You'll be getting a CSV file.
    </div>

    <form action="" method="post">
        <input type="hidden" name="import" value="0">
        <button class="btn btn--export" type="submit" name="go__export" id="action__export">
            Export ~>
        </button>
    </form>
</section>

<section class="mmir__writeyourstuff mmir--section">
    <div class="title">2. Write your own metas</div>
    <div class="section__subtitle">
        You can now open the new CSV file in Excel or any equivalent. There will be "meta title" and "meta description" fields. Fill them, and don't forget to save often.<br>
        <strong>WARNING : Do NOT change the file format. It needs to be CSV.</strong>
    </div>
</section>

<section class="mmir__import mmir--section">
    <div class="title">3. Import your updated metas</div>
    <div class="section__subtitle">
        You can now re-import your CSV file. It will be processed in order to replace every page's meta title and description.<br>
        <strong>
            WARNING : This step can take some time, do NOT close your browser, or you will have to start again.<br>
            This plugin backs up your old metas before importing the new one. This is crucial.
        </strong>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="field__import">
        <button type="submit" class="btn btn--import" name="go__import" id="action__import">
            Import <~
        </button>
    </form>
</section>

<section class="mmir__profit mmir--section">
    <div class="title">4. Profit!</div>
    <div class="section__subtitle">
        Enjoy your free time.
    </div>
</section>