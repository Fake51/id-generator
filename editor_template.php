<?php
	header('Content-Type: text/html; charset=UTF-8');

?><html>
<head>
    <link rel="stylesheet" href="style.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        <?php
        if ($next_step!=null) : ?>
            var next_location = 'index.php?view=card&step=<?= urlencode($next_step)?>';
        <?php else : ?>
            var next_location = 'index.php?view=card&step=first';
        <?php endif; ?>
    </script>
    <script src="functions.js"></script>
</head>
<body><h1 class='title'>MEGA SYMPOSIUM DELUXE GENERATOR</h1>

    <div class='main'>
        <form action='<?= $_SERVER['REQUEST_URI']?>' id='myform' method='post'>
        <input type='hidden' value='<?php echo $person->getX();?>' name='x' id='x'>
        <input type='hidden' value='<?php echo $person->getY();?>' name='y' id='y'>

        <div id='workarea'>
            <img id='photo' src='<?= get_cache_filename($person->getFilename());?>'>
            <img id='template' src='<?=$preview_filename?>'>
        </div>
        <div class='control'>
            <h1>Filename: <?=$step?></h1>
            <table>
                <tr>
                    <td>
                            X: <span id='text_x'><?= $person->getX();?></span><br>
                            Y: <span id='text_y'><?= $person->getY();?></span>
                    </td>
                    <td><button class="move-up">^</button></td>
                    <td></td>
                    <td rowspan='4' valign='top'>
                        Navn:<br>
                        <input type='text' name='navn' value='<?= e($person->getField('navn')) ? e($person->getField('navn')) : e($person->turnFilenameToName()); ?>'><br>
                        ID:<br>
                        <input type='text' name='id' value='<?= e($person->getField('id')); ?>'><br>
                        Fluff-tekst:<br>
                        <textarea name='tekst'><?= e($person->getField('tekst')); ?></textarea><br/>
                        Funktion:<br>
                        <textarea name='funktion'><?= e($person->getField('funktion')) ? e($person->getField('funktion')) : e($person->turnFilenameToFunction()); ?></textarea><br>
                        Template:<br>
                        <select name='template'>
                            <?php foreach (getTemplateList() as $name => $file) :?>
                                <option value='<?= e($file);?>'<?= $person->getTemplate() == $file ? " selected":""?>><?= e($name);?></option>
                            <?php endforeach;?>
                        </select>
                        <br>
                        <br>
                        <input type='submit' value='Save'>
                    </td>
                </tr>
                <tr>
                    <td><button class="move-left">&lt;</button></td>
                    <td>X/Y</td>
                    <td><button class="move-right">&gt;</button></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td><button class="move-down">v</button></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                </tr>
                <tr><td colspan='4'>
                    Baggrundsfarve (fx 255 255 255), hvis der er fnidder pga. alpha: 
                    #<input type='text' name='bg_r' value='<?= e($person->getBgColor('red')); ?>' class="color-input"><?php
                    ?><input type='text' name='bg_g' value='<?= e($person->getBgColor('red')); ?>' class="color-input"><?php
                    ?><input type='text' name='bg_b' value='<?= e($person->getBgColor('red')); ?>' class="color-input">
                </td></tr>
            </table>

            <button class="next">Næste -&gt;</button>
        </div>
        </form>
    </div>

    <div class='list'>
        <h1>Rediger gamle:</h1>
        <ul>
        <?php
        foreach($config['persons'] as $person){
            ?>
            <li><a href='index.php?view=card&step=<?= urlencode($person['filename']); ?>'><?= e($person['filename']);?> / <?= e($person['navn']);?></a> &rarr; <a href="./output/idcard_<?= rawurlencode($person['filename']);?>.png" target="_blank">download</a></li>
            <?php
        }
        ?>
        </ul>
        <br><br>
        <a href='index.php?view=zip'>Download</a> alle dem jeg har lavet og som er færdige (det er dem på listen lige ovenfor).
        <br><br>
        <div class="align-right"><button class="wipe-all">Clean alt - begynd forfra</button></div>
        <br><br>
    </div>
</body>
</html>
