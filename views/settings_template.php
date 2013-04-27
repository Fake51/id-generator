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
 * @link      link to source code
 * @version   version number
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Generator - settings</title>
    <link rel="stylesheet" href="style.css"/>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="functions.js"></script>
</head>
<body>
    <h1 class='title'>ID Generator</h1>
    <section class="templates">
        <header>Templates</header>
        <ul>
            <?php foreach (getTemplateList() as $name => $filename) : ?>
            <li><img alt="Remove template" src="images/erase.png" class="remove-template" data-filename="<?= e($filename);?>"/> <?= e($name);?></li>
            <?php endforeach;?>
        </ul>
        <script type="text/template" id="template-list-item">
            <li><img alt="Remove template" src="images/erase.png" class="remove-template" data-filename="!filename!"/> !name!</li>
        </script>
        <form action="ajax.php" method="POST" enctype="multipart/form-data">
            <p>Templates should be dimensioned at <?= TEMPLATE_WIDTH;?>px by <?= TEMPLATE_HEIGHT;?>px or they will be scaled and resized to those dimensions.</p>
            <span class="template-upload-wrapper">
                <input type="hidden" name="action" value="upload-template"/>
                <input type="file" name="template-file"/> <button class="upload-template">Upload new template</button>
            </span>
        </form>
    </section>

    <script>
        settings_init(jQuery);
    </script>
</body>
</html>
