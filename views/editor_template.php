<?php
/**
 * Copyright (c) 2013, Peter Lind & Kristoffer Mads Sørensen
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 *
 * PHP version 5.3+
 *
 * @copyright Copyright (c) 2013 Peter Lind & Kristoffer Mads Sørensen
 * @license   https://github.com/Fake51/id-generator/blob/master/LICENSE FreeBSD license
 * @link      https://github.com/Fake51/id-generator
 * @version   0.1
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */
	header('Content-Type: text/html; charset=UTF-8');

?><html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
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
<body><h1 class='title'>ID Generator</h1>

    <div class='main'>
        <form action='<?= $_SERVER['REQUEST_URI']?>' id='myform' method='post'>
        <input type='hidden' value='<?php echo $person->getX();?>' name='x' id='x'>
        <input type='hidden' value='<?php echo $person->getY();?>' name='y' id='y'>

        <div id='workarea'>
            <img id='photo' src='<?= systemToWebPath(get_cache_filename($person->getFilename()));?>'>
            <img id='template' src='<?= systemToWebPath($preview_filename); ?>'>
        </div>
        <div class='control'>
            <h2>Filename: <?=$step?></h2>
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
        <h2>Rediger gamle:</h2>
        <ul class="uploaded-photos">
        <?php
        foreach($config['persons'] as $person){
            ?>
            <li><a href='index.php?view=card&step=<?= urlencode($person['filename']); ?>'><?= e($person['filename']);?> / <?= e($person['navn']);?> (<?= e(basename($person['template'], '.png'));?>)</a> &rarr; <a href="./output/idcard_<?= rawurlencode($person['filename']);?>.png" target="_blank">download</a></li>
            <?php
        }
        ?>
        </ul>
        <p>I alt: <?= count($config['persons']);?></p>
        <p>
            <a href='index.php?view=zip'>Download</a> alle dem jeg har lavet og som er færdige (det er dem på listen lige ovenfor).
        </p>
        <div class="align-right"><button class="wipe-all">Clean alt - begynd forfra</button></div>
        <h2>Upload photo</h2>
        <form action="ajax.php" method="POST" enctype="multipart/form-data">
            <p>Photos should be dimensioned <?= PHOTO_WIDTH;?>px by <?= PHOTO_HEIGHT;?>px or they will be scaled and resized to those dimensions.</p>
            <span class="template-upload-wrapper">
                <input type="hidden" name="action" value="upload-photo"/>
                <input type="file" name="photo"/> <button class="upload-photo">Upload new photo</button>
            </span>
        </form>
        <p>
            <a href="settings.php" title="Edit settings">Settings</a>
        </p>
    </div>
    <script>
    editor_init(jQuery);
    </script>
</body>
</html>
