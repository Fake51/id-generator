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
 * @license   https://github.com/Fake51/id-generator/blob/master/LICENSE link FreeBSD license
 * @link      https://github.com/Fake51/id-generator
 * @version   0.1
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */
require 'code/defines.php';
require 'code/functions.php';
require 'code/person.php';
require 'code/SimpleImage.php';

initSettingDefines();

if (empty($_REQUEST['action'])) {
    header('HTTP/1.1 400 No action specified');
    exit;
}

switch (strtolower($_REQUEST['action'])) {
case 'delete-template':
    if (empty($_GET['filename'])) {
        header('HTTP/1.1 400 Lacking filename');
        break;
    }

    if (!removeTemplate($_GET['filename'])) {
        header('HTTP/1.1 500 Failed to remove template');
        break;
    }

    header('HTTP/1.1 200 Template removed');
    break;

case 'upload-template':
    if (empty($_FILES['template-file'])) {
        header('HTTP/1.1 400 No file uploaded');
        break;
    }

    try {
        $info = handleTemplateUpload($_FILES['template-file']);

        if (!empty($_POST['ajax-call'])) {
            header('HTTP/1.1 200 Success');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($info);

        } else {
            header('HTTP/1.1 303 Finished');
            header('Location: ' . $_SERVER['HTTP_REFERER']);

        }

    } catch (Exception $e) {

        header('HTTP/1.1 500 Template upload failed');
        header('Content-Type: text/plain; charset=UTF-8');
        echo $e->getMessage();
    }

    break;

case 'upload-photo':
    if (empty($_FILES['photo'])) {
        header('HTTP/1.1 400 No file uploaded');
        break;
    }

    try {
        $info = handlePhotoUpload($_FILES['photo']);

        if (!empty($_POST['ajax-call'])) {
            header('HTTP/1.1 200 Success');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode($info);

        } else {
            header('HTTP/1.1 303 Finished');
            header('Location: ' . $_SERVER['HTTP_REFERER']);

        }

    } catch (Exception $e) {

        header('HTTP/1.1 500 Template upload failed');
        header('Content-Type: text/plain; charset=UTF-8');
        echo $e->getMessage();
    }

    break;

case 'save-field-configuration':
    if (empty($_POST['configuration'])) {
        header('HTTP/1.1 400 Missing data');
        break;
    }

    try {
        $config = json_decode($_POST['configuration'], true);
        if (!$config) {
            throw new Exception('Could not decode configuration data');
        }

        saveFieldConfiguration(translateFrontendConfiguration($config));

        header('HTTP/1.1 200 Saved');

    } catch (Exception $e) {
        header('HTTP/1.1 500 Failed');
    }

    break;

default:
    header('HTTP/1.1 400 Action not recognized');
}
