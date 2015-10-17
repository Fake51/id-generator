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

/**
 * attempts to remove a template
 *
 * @param string $filename Filename of template to remove
 *
 * @return bool
 */
function removeTemplate($filename)
{
    return unlink(TEMPLATE_PATH . $filename);
}

/**
 * returns image info from a file upload
 *
 * @param array $file_info Info from uploaded file
 *
 * @throws Exception
 * @return array
 */
function getUploadedImageInfo(array $file_info)
{
    if (!empty($file_info['error'])) {
        throw new Exception("Error in file upload");
    }

    if (!is_uploaded_file($file_info['tmp_name'])) {
        throw new Exception("File did not get stored after upload");
    }

    if (!($image_info = getimagesize($file_info['tmp_name']))) {
        throw new Exception("Uploaded file is not an image");
    }

    if (!(in_array($image_info['mime'], array('image/jpeg', 'image/png')))) {
        throw new Exception('Only PNG and JPG images allowed as templates');
    }

    $image_info['extension'] = $image_info['mime'] == 'image/jpeg' ? 'jpg' : 'png';

    return $image_info;
}

/**
 * creates a filename for a template or photo upload
 *
 * @param string $filename      Original filename
 * @param string $filename_base Template for generating new name
 * @param string $path          Path to put file in after upload
 * @param string $extension     File type extension
 *
 * @return string
 */
function createUploadFilename($filename, $filename_base, $path, $extension)
{
    if (preg_match('/^([a-z0-9_ -]+)\\.(png|jpg|jpeg)$/i', $filename, $match)) {
        $filename = strtolower($match[1] . '.' . $extension);

        if (file_exists($path . $filename)) {
            $filename = getUnusedFilename($match[1] . '-INDEX.' . $extension, 1, $path);
        }

    } else {
        $filename = getUnusedFilename($filename_base . '-INDEX.' . $extension, 1, $path);

    }

    return $filename;
}

/**
 * handles photo upload
 *
 * @param array $file_info FILES array from upload
 *
 * @return string
 */
function handlePhotoUpload(array $file_info)
{
    $image_info = getUploadedImageInfo($file_info);

    $photo_filename = createUploadFilename($file_info['name'], 'photo', INPUT_PATH, $image_info['extension']);

    if ($image_info[0] != PHOTO_WIDTH || $image_info[1] != PHOTO_HEIGHT) {
        $photo_filename = str_replace('.' . $image_info['extension'], '.jpg', $photo_filename);
        if (file_exists(INPUT_PATH . $photo_filename)) {
            $photo_filename = getUnusedFilename(str_replace('.jpg', '-INDEX.jpg', $photo_filename), 1, INPUT_PATH);
        }
        $image_info['extension'] = 'jpg';

        $image = new SimpleImage();
        $image->load($file_info['tmp_name']);

        $height_diff = abs($image_info[1] - PHOTO_HEIGHT);
        $width_diff  = abs($image_info[0] - PHOTO_WIDTH);

        if ($height_diff < $width_diff) {
            $image->resize($image_info[0] / ($image_info[1] / PHOTO_HEIGHT), PHOTO_HEIGHT);
        } else {
            $image->resize(PHOTO_WIDTH, $image_info[1] / ($image_info[0] / PHOTO_WIDTH));
        }

        $image->save(INPUT_PATH . $photo_filename, IMAGETYPE_JPEG);

    } else {
        if (!move_uploaded_file($file_info['tmp_name'], INPUT_PATH . $photo_filename)) {
            throw new Exception('Could not move uploaded file to proper place');
        }
    }

    return array(
        'filename' => $photo_filename,
        'template' => ucfirst(basename($photo_filename, '.' . $image_info['extension'])),
    );
}

/**
 * returns a path that doesnt exist,
 * based on the given template and path
 *
 * @param string $template Filename template
 * @param int    $index    Filename index to append
 * @param string $path     Path to look in
 *
 * @return string
 */
function getUnusedFilename($template, $index, $path) {
    $filename = str_replace('-INDEX', '-' . $index, $template);
    if (file_exists($path . $filename)) {
        return getUnusedFilename($template, $index + 1, $path);
    }

    return $filename;
};

/**
 * handles template upload
 *
 * @param array $file_info FILES array from upload
 *
 * @return string
 */
function handleTemplateUpload(array $file_info)
{
    $image_info = getUploadedImageInfo($file_info);

    $template_filename = createUploadFilename($file_info['name'], 'template', TEMPLATE_PATH, $image_info['extension']);

    if ($image_info[0] != TEMPLATE_WIDTH || $image_info[0] != TEMPLATE_HEIGHT) {
        $template_filename = str_replace('.' . $image_info['extension'], '.png', $template_filename);
        if (file_exists(TEMPLATE_PATH . $template_filename)) {
            $template_filename = getUnusedFilename(str_replace('.png', '-INDEX.png', $template_filename), 1, TEMPLATE_PATH);
        }
        $image_info['extension'] = 'png';

        $image = new SimpleImage();
        $image->load($file_info['tmp_name']);
        $image->resize(TEMPLATE_WIDTH, TEMPLATE_HEIGHT);
        $image->save(TEMPLATE_PATH . $template_filename, IMAGETYPE_PNG);

    } else {
        if (!move_uploaded_file($file_info['tmp_name'], TEMPLATE_PATH . $template_filename)) {
            throw new Exception('Could not move uploaded file to proper place');
        }
    }

    return array(
        'filename' => $template_filename,
        'template' => ucfirst(basename($template_filename, '.' . $image_info['extension'])),
    );
}

/**
 * returns list of templates for use
 *
 * @return array
 */
function getTemplateList()
{
    $templates = array();

    if ($handle = opendir(TEMPLATE_PATH)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != "..") && ($file != ".") && (preg_match("@^((.*)\.(png|jpg))$@", $file, $match))) {
                $templates[ucfirst($match[2])] = $match[1];
            }
        }
    }

    ksort($templates);

    return $templates;
}

/**
 * checks if there's a cached version of the image
 *
 * @param string $filename Filename to check for cached version of
 * @param string $prefix   Optional prefix
 *
 * @return bool
 */
function has_file_cache($image_name)
{
    return file_exists(get_cache_filename($image_name));
}

/**
 * saves a cached version of an image
 *
 * @param string   $filename Filename of image to save cache for
 * @param resource $image    Image to save
 *
 * @return void
 */
function save_file_cache($image_name, $image){
    imagepng($image, get_cache_filename($image_name));
}

/**
 * returns a Simpleimage instance based on a cached image
 * for the image filename provided
 *
 * @param string $image_name Description
 *
 * @return SimpleImage
 */
function get_file_cache($image_name)
{
    
    $img = new SimpleImage();
    $img->load(get_cache_filename($image_name));
    return $img;
}

/**
 * returns the name used for cached versions of the image
 *
 * @param string $filename Name of image to create path for
 *
 * @return string
 */
function get_cache_filename($image_name)
{
    return CACHE_PATH . "keep_" . md5($image_name) . preg_replace('/^.*(\\.(png|jpg|jpeg|gif))/', '$1', $image_name);
}

/**
 * removes all temporary files from
 * the cache folder
 *
 * @return void
 */
function clearCache()
{
    if ($handle = opendir(CACHE_PATH)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != "..") && ($file != ".") && (preg_match("@^temporary.*$@", $file))) {
                unlink(CACHE_PATH . $file);
            }
        }
    }
}

/**
 * removes all files in the output folder
 *
 * @return void
 */
function clearOutput()
{
    if ($handle = opendir(OUTPUT_PATH)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != "..") && ($file != ".")) {
                unlink(OUTPUT_PATH . $file);
            }
        }
    }
}

/**
 * returns all image filenames from the input
 * path, if they are images
 *
 * @return void
 */
function loadPersons()
{
    $ret = array();
    if ($handle = opendir(INPUT_PATH)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != "..") && ($file != ".") && preg_match('/\.(jpg|jpeg|png)/i', $file)) {
                $ret[] = $file;
            }
        }
    }

    return $ret;
}

/**
 * loads the config file
 *
 * @return array
 */
function loadConfig()
{
    $config = json_decode(file_get_contents(CONFIG_PATH), true);
    return $config;
}

/**
 * clears out the config file
 *
 * @return void
 */
function saveEmptyConfig()
{
    $arr = array();
    $arr['persons'] = array();
    saveConfig($arr);
}

/**
 * saves an array to the config file
 *
 * @param array $config Config array to save
 *
 * @return void
 */
function saveConfig($config)
{
    file_put_contents(CONFIG_PATH, json_encode($config));
}

/**
 * creates an output image
 *
 * @param Person $person Person instance to create image for
 *
 * @return bool
 */
function createOutputfile(Person $person)
{
    $filename = OUTPUT_PATH . "idcard_" . $person->getFilename() . ".png";
    return createPersonImage($person, $filename, false, IMAGETYPE_PNG);
}

/**
 * creates a preview image of the final id card
 *
 * @param Person $person Person instance to create image for
 *
 * @return bool
 */
function createPreview(Person $person)
{
    $filename = CACHE_PATH . "temporary_" . rand() . ".png";
    return createPersonImage($person, $filename, true, IMAGETYPE_PNG, false, 1);
}

/**
    * writes fluffy text on the image
    *
    * @param string $text Text to write
    *
    * @return void
    */
function paintText(array $params)
{
    // WRITE FLUFFY TEXT
    $color = ImageColorAllocate($params['template']->image, $params['color_array']['red'], $params['color_array']['green'], $params['color_array']['blue']);
    $box = imagettfbbox($params['size'], $params['direction'], $params['font'], $params['text']);

    if (!empty($params['right_aligned'])) {
        $width_function = function($box) {return $box[2] - $box[0];};

    } else {
        $width_function = function($box) {return $box[2] - $box[4];};
    }

    if (!empty($params['text_max_width'])) {
        $params['size'] += 2;

        do {
            $params['size'] -= 2;
            $box   = imagettfbbox($params['size'], $params['direction'], $params['font'], $params['text']);
            $width = $width_function($box);
        } while ($width > $params['text_max_width']);

        if (isset($params['max_size']) && $params['max_size'] < $params['size']) {
            $params['size'] = $params['max_size'];
        }

    } else {
        $width = $width_function($box);
    }

    $offset_x = !empty($params['no_width_modification']) ? $params['offset_x'] : $params['offset_x'] - $width;

    imagettftext($params['layerbg']->image, $params['size'], $params['direction'], $offset_x, $params['offset_y'] + $params['text_max_height'], $color, $params['font'], $params['text']);
}

function createPersonPhoto($person, $max_width = 319, $max_height = 382)
{
    $person_img = new SimpleImage();
    $person_img->load(INPUT_PATH . $person->getFilename());
    $i_w     = $person_img->getWidth();
    $i_h     = $person_img->getHeight();
    $ratio   = (($i_h * 1.0) / $i_w);
    $ratio_r = (($i_w * 1.0) / $i_h);
    $scale   = 1.1;

    if ($ratio<=1) {
        $w = round($max_height*$ratio_r*$scale,0);
        $h = round($max_height*$scale,0);

    } else { // den er højere end den er bred
        $w = round($max_width*$scale,0);
        $h = round($max_width*$ratio*$scale,0);
    }

    $person_img->resize($w, $h);
    return $person_img;
}

function createPersonImage($person, $filename, $debug, $image_type = IMAGETYPE_PNG, $includePhoto = true, $compression = 100)
{
    $defaults = getDefaultValues();
    
    if (has_file_cache('template_ ' . $person->getTemplate())) {
        $template = get_file_cache('template_ ' . $person->getTemplate());

    } else {
        $template = new SimpleImage();
        $template->load(TEMPLATE_PATH . $person->getTemplate());
        $template->resizeToWidth(1024);

        save_file_cache('template_ ' . $person->getTemplate(), $template->image);
    }
    
    
    $layerbg = new SimpleImage();
    $layerbg->createImage($template->getWidth(), $template->getHeight());
    
    if ($includePhoto) {
        $max_width  = $defaults['photo_max_width'];
        $max_height = $defaults['photo_max_height'];
        
        if (has_file_cache($person->getFilename())) {
            $person_img = get_file_cache($person->getFilename());

        } else {
            $person_img = createPersonPhoto($person, $max_width, $max_height);
            save_file_cache($person->getFilename(), $person_img->image);
        }
        
        $pos_x = ($person_img->getWidth() - $max_width) / 2;
        $pos_y = ($person_img->getHeight() - $max_height) / 2;

        if (($person->getBgColor('red') != "") && ($person->getBgColor('green') != "") && ($person->getBgColor('blue') != "")) {
            $color = ImageColorAllocate($layerbg->image, $person->getBgColor('red'), $person->getBgColor('green'), $person->getBgColor('blue'));
            imagefilledrectangle($layerbg->image,0,0,$template->getWidth(),$template->getHeight(),$color);

        }

        $layerbg->paint($person_img->image, $person->getX(), $person->getY());
    }
    
    // DRAW TEMPLATE
    $layerbg->paint($template->image,0,0);

    // DRAW DEBUG BOX
    if (($includePhoto) && ($debug)) {
        $color_c = imagecolorallocatealpha($layerbg->image, 255, 255, 255, 90);
        $color_b = imagecolorallocatealpha($layerbg->image, 0, 0, 0, 80);
        $x       = 694-$pos_x+$person->getX();
        $y       = 8-$pos_y+$person->getY();
        imagefilledrectangle($layerbg->image, $x, $y, $x+$person_img->getWidth(), $y+$person_img->getHeight(), $color_c);
        imagerectangle($layerbg->image, $x, $y, $x+$person_img->getWidth(), $y+$person_img->getHeight(), $color_b);
        imageline($layerbg->image, $x, $y, $x+$person_img->getWidth(), $y+$person_img->getHeight(), $color_b);
        imageline($layerbg->image, $x, $person_img->getHeight()+$y, $x+$person_img->getWidth(), $y, $color_b);
    }

    paintText(array(
        'text'                  => $person->getField('navn'),
        'template'              => $template,
        'color_array'           => array('red' => 0, 'green' => 0, 'blue' => 0),
        'font'                  => './fonts/calibri.ttf',
        'size'                  => 64,
        'direction'             => -NAMEBOX_ANGLE,
        'layerbg'               => $layerbg,
        'offset_x'              => NAMEBOX_OFFSET_X,
        'offset_y'              => NAMEBOX_OFFSET_Y,
        'right_aligned'         => true,
        'text_max_width'        => NAMEBOX_WIDTH,
        'text_max_height'       => NAMEBOX_HEIGHT,
        'no_width_modification' => true,
        )
    );

    // WRITE FUNCTION
    $function_parts = explode("\n", $person->getField('funktion'));
    if (count($function_parts) > 2) {
        $function_parts = array_slice($function_parts, 0, 2);
    }

    $base_text_size = FUNCTIONBOX_HEIGHT * 1.6;
    $text_size      = $base_text_size / count($function_parts);

    $modifier += $text_size / 5;

    if (count($function_parts) < 2) {
        $modifier += FUNCTIONBOX_HEIGHT;
    }

    foreach ($function_parts as $part) {

        paintText(array(
            'text'            => $part,
            'template'        => $template,
            'color_array'     => array('red' => 0, 'green' => 0, 'blue' => 0),
            'font'            => './fonts/calibri.ttf',
            'size'            => $text_size,
            'max_size'        => 50,
            'direction'       => -FUNCTIONBOX_ANGLE,
            'layerbg'         => $layerbg,
            'offset_x'        => FUNCTIONBOX_OFFSET_X,
            'offset_y'        => FUNCTIONBOX_OFFSET_Y + $modifier,
            'text_max_width'  => FUNCTIONBOX_WIDTH,
            'text_max_height' => FUNCTIONBOX_HEIGHT,
            'right_aligned'   => true,
            )
        );

        $modifier += $text_size + 10;
    }

    paintText(array(
        'text'        => $person->getField('tekst'),
        'template'    => $template,
        'color_array' => array('red' => 150, 'green' => 150, 'blue' => 150),
        'font'        => './fonts/calibri.ttf',
        'size'        => 18,
        'direction'   => 90,
        'layerbg'     => $layerbg,
        'offset_x'    => 514,
        'offset_y'    => 380,
        )
    );

    // DRAW BARCODE
    if ($person->getField('id')) {
        
        $url = 'http://infosys.fastaval.dk/participant/ean8small/' . $person->getField('id');
        if (has_file_cache($url)){
            $barcode = get_file_cache($url);
            $barcode = $barcode->image;
        }
        else{
            $barcode = imagecreatefrompng($url);
            save_file_cache($url,$barcode);
        }
        
        if (imagecopyresampled($layerbg->image, $barcode, 42, 26, 0, 0, 82, 587, 82, 544)) {
            $barcoded = true;
        }
        
    }

    if (empty($barcoded)) {
        $blank = imagecreatetruecolor(92, 544);
        $white = imagecolorallocate($blank, 255, 255, 255);
        imagefill($blank, 0, 0, $white);
        imagecopy($layerbg->image, $blank, 932, 90, 0, 0, 82, 544);
    }

    $layerbg->save($filename,$image_type,$compression);
    
    return $filename;
}

/**
 * wipes the configuration and cleans
 * the output folder
 *
 * @return void
 */
function wipeContents()
{
    saveEmptyConfig();
    clearOutput();
    header("Location: index.php");
    exit;
}

/**
 * outputs a zipped file with all
 * the done cards in it
 *
 * @return void
 */
function outputZipFile()
{
    $zip = new ZipArchive();
    if ($zip->open('./cache/myZip.zip', ZIPARCHIVE::CREATE) !== TRUE) {
        die ("Could not open archive");
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("output/"));
    foreach ($iterator as $key => $value) {
        $zip->addFile(realpath($key), $key) or die ("ERROR: Could not add file: $key");
    }

    $zip->close();
    $filesize = filesize("./cache/myZip.zip");
    header("Content-Type: archive/zip");
    header("Content-Disposition: attachment; filename=jeg_har_fragler_i_skjorten".".zip");
    header("Content-Length: $filesize");
    readfile("./cache/myZip.zip","r");
    exit;
}

/**
 * shows the html editor for ID cards
 *
 * @param array $data_fields Dynamic data fields to use in the editor
 * @param array $defaults    Default values to use
 *
 * @return void
 */
function showIDCardEditor(array $data_fields, array $defaults)
{
    $config  = loadConfig();
    $persons = loadPersons();

    $step = !empty($_GET['step']) ? $_GET['step'] : 'first';

    if ($step == "first") {
        $step = $persons[0];
    }

    $person = new Person($step, $data_fields, $defaults);

    if (isset($config['persons'][$step])) {
        $person->setConfig($config['persons'][$step]);
    }

    $next_step = null;
    for ($i=0; $i<count($persons); $i++) {
        $navn = $persons[$i];
        $skip = false;
        foreach (array_keys($config['persons']) as $done) {
            if ($done == $navn) {
                $skip = true;
                break;
            }
        }

        if (!$skip) {
            $next_step = str_replace('&', '%26', $navn);
            break;
        }
    }

    if (!empty($_POST)) {
        $person->updateConfig($_POST);

        $config['persons'][$step] = $person->getConfig();
        saveConfig($config);
        createOutputfile($person);
    }

    clearCache();
    $preview_filename = createPreview($person);

    include VIEWS_PATH . "editor_template.php";
    exit;
}

/**
 * returns dynamic data fields
 *
 * @return array
 */
function getDynamicFields()
{
    return array(
        'navn',
        'tekst',
        'funktion',
        'id',
    );
}

/**
 * returns dynamic configuration for card
 *
 * @return array
 */
function getDynamicConfiguration()
{
}

/**
 * returns default values for Person instances
 *
 * @return array
 */
function getDefaultValues()
{
    $templates = getTemplateList();
    $template  = reset($templates) ? reset($templates) : '';

    return array(
        'x'                => PHOTO_OFFSET_X,
        'y'                => PHOTO_OFFSET_Y,
        'angle'            => PHOTO_ANGLE,
        'template'         => $template,
        'photo_max_width'  => PHOTO_WIDTH,
        'photo_max_height' => PHOTO_HEIGHT,
    );
}

/**
 * wrapper for htmlspecialchars
 *
 * @param string $string String to escape
 *
 * @return string
 */
function e($string)
{
    return htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
}

/**
 * converts a system to web path
 * which mainly consists of stripping the base path
 *
 * @param string $path System path to convert
 *
 * @return string
 */
function systemToWebPath($path)
{
    return str_replace(BASE_PATH . '/', '', $path);
}

/**
 * checks that all needed paths exist
 *
 * @return bool
 */
function checkSetup()
{
    foreach (array(TEMPLATE_PATH, CACHE_PATH, OUTPUT_PATH, INPUT_PATH, FONTS_PATH) as $path) {
        if (!is_dir($path) || !is_writable($path)) {
            return false;
        }
    }

    if ((file_exists(CONFIG_PATH) && !is_writable(CONFIG_PATH))) {
        return false;
    }

    return true;
}

/**
 * tries to create all needed paths
 *
 * @return void
 */
function doInstall()
{
    $failed_paths = false;
    foreach (array(TEMPLATE_PATH, CACHE_PATH, OUTPUT_PATH, INPUT_PATH, FONTS_PATH) as $path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0770)) {
                $failed_paths[] = $path;
            }
        }

        if (!is_writable($path)) {
            if (!chmod($path, 0770)) {
                $failed_paths[] = $path;
            }
        }
    }

    foreach (array(CONFIG_PATH, SETTINGS_PATH) as $path) {
        if ((file_exists($path) && !is_writable($path))) {
            if (!chmod($path, 0770)) {
                $failed_paths[] = $path;
            }
        }
    }

    if ($failed_paths) {
        echo '<pre>
Could not create needed paths for tool to work.
Make sure that permissions are set correctly in the folder where
this tool is installed.

Problematic paths:
' . implode("\n", $failed_paths) . '
</pre>';
        exit;
    }
}

/**
 * sets up defines based either on settings from the user
 * or on reasonable defaults
 *
 * @return void
 */
function initSettingDefines()
{
    foreach (getSettings() as $key => $value) {
        define($key, $value);
    }
}

/**
 * returns current settings
 *
 * @return array
 */
function getSettings()
{
    $defaults = defaultSettingsArray();

    if (is_file(SETTINGS_PATH) && is_readable(SETTINGS_PATH)) {
        $settings = json_decode(file_get_contents(SETTINGS_PATH), true);

        if (empty($settings)) {
            $settings = array();
        }

    }

    $proper_settings = array();
    foreach ($defaults as $key => $value) {
        if (isset($settings[$key]) && (intval($settings[$key]) || ctype_digit((string) $settings[$key]))) {
            $proper_settings[$key] = intval($settings[$key]);

        } else {
            $proper_settings[$key] = $value;
        }
    }

    return $proper_settings;
}

/**
 * returns array of config settings and default values
 *
 * @return array
 */
function defaultSettingsArray()
{
    return array(
        'TEMPLATE_WIDTH'       => TEMPLATE_DEFAULT_WIDTH,
        'TEMPLATE_HEIGHT'      => TEMPLATE_DEFAULT_HEIGHT,
        'PHOTO_WIDTH'          => PHOTO_DEFAULT_WIDTH,
        'PHOTO_HEIGHT'         => PHOTO_DEFAULT_HEIGHT,
        'PHOTO_OFFSET_X'       => PHOTO_DEFAULT_OFFSET_X,
        'PHOTO_OFFSET_Y'       => PHOTO_DEFAULT_OFFSET_Y,
        'PHOTO_ANGLE'          => PHOTO_DEFAULT_ANGLE,
        'NAMEBOX_WIDTH'        => NAMEBOX_DEFAULT_WIDTH,
        'NAMEBOX_HEIGHT'       => NAMEBOX_DEFAULT_HEIGHT,
        'NAMEBOX_OFFSET_X'     => NAMEBOX_DEFAULT_OFFSET_X,
        'NAMEBOX_OFFSET_Y'     => NAMEBOX_DEFAULT_OFFSET_Y,
        'NAMEBOX_ANGLE'        => NAMEBOX_DEFAULT_ANGLE,
        'FUNCTIONBOX_WIDTH'    => FUNCTIONBOX_DEFAULT_WIDTH,
        'FUNCTIONBOX_HEIGHT'   => FUNCTIONBOX_DEFAULT_HEIGHT,
        'FUNCTIONBOX_OFFSET_X' => FUNCTIONBOX_DEFAULT_OFFSET_X,
        'FUNCTIONBOX_OFFSET_Y' => FUNCTIONBOX_DEFAULT_OFFSET_Y,
        'FUNCTIONBOX_ANGLE'    => FUNCTIONBOX_DEFAULT_ANGLE,
    );
}

/**
 * saves the field configuration
 *
 * @param array $configuration Data to save
 *
 * @throws Exception
 * @return void
 */
function saveFieldConfiguration(array $configuration)
{
    $config = array();
    foreach (getSettings() as $key => $default) {
        $config[$key] = isset($configuration[$key]) ? $configuration[$key] : $default;
    }

    file_put_contents(SETTINGS_PATH, json_encode($config));
}

/**
 * translates configuration between frontend and backend
 *
 * @param array $post Configuration posted from JS
 *
 * @return array
 */
function translateFrontendConfiguration(array $post)
{
    $groupings = array(
        'Name'     => 'NAMEBOX',
        'Function' => 'FUNCTIONBOX',
        'Photo'    => 'PHOTO',
    );

    $configuration = array();

    foreach ($groupings as $key => $prefix) {
        if (isset($post[$key])) {
            $configuration[$prefix . '_WIDTH']    = round($post[$key]['width']);
            $configuration[$prefix . '_HEIGHT']   = round($post[$key]['height']);
            $configuration[$prefix . '_OFFSET_X'] = round($post[$key]['x']);
            $configuration[$prefix . '_OFFSET_Y'] = round($post[$key]['y']);
            $configuration[$prefix . '_ANGLE']    = round($post[$key]['angle']);
        }
    }

    // function box is right-to-left
    if (isset($configuration['FUNCTIONBOX_OFFSET_X']) && isset($configuration['FUNCTIONBOX_WIDTH'])) {
        //$configuration['FUNCTIONBOX_OFFSET_X'] += $configuration['FUNCTIONBOX_WIDTH'];
    }

    return $configuration;
}
