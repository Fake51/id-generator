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
 * returns list of templates for use
 *
 * @return array
 */
function getTemplateList()
{
    $templates = array();

    if ($handle = opendir(TEMPLATE_PATH)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != "..") && ($file != ".") && (preg_match("@^(.*)\.(png|jpg)$@", $file, $match))) {
                $templates[ucfirst($match[1])] = $match[1];
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
    return CACHE_PATH . "keep_" . md5($image_name) . ".png";
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
        } while ($width > 650);

    } else {
        $width = $width_function($box);
    }

    $offset_x = !empty($params['no_width_modification']) ? $params['offset_x'] : $params['offset_x'] - $width;

    imagettftext($params['layerbg']->image, $params['size'], $params['direction'], $offset_x, $params['offset_y'], $color, $params['font'], $params['text']);
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
    
    /*
    300 DPI = 300 pixels pr. INCH.
    kortet er 800 BREDT, så det er DPI på 330.
    
    hvis teksten skal være 15 PT stor, og én PT er 1/72 inch,
    
    Så er 17 PT = 15/72 INCHES, 15*330/72 PIXELS = 80
    
    */

    $defaults = getDefaultValues();
    
    if (has_file_cache('template_ ' . $person->getTemplate())) {
        $template = get_file_cache('template_ ' . $person->getTemplate());

    } else {
        $template = new SimpleImage();
        $template->load(TEMPLATE_PATH . $person->getTemplate() . ".png");
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
        
        $pos_x = ($person_img->getWidth()-$max_width)/2;
        $pos_y = ($person_img->getHeight()-$max_height)/2;

        if (($person->getBgColor('red') != "") && ($person->getBgColor('green') != "") && ($person->getBgColor('blue') != "")) {
            $color = ImageColorAllocate($layerbg->image, $person->getBgColor('red'), $person->getBgColor('green'), $person->getBgColor('blue'));
            imagefilledrectangle($layerbg->image,0,0,$template->getWidth(),$template->getHeight(),$color);

        }

        $layerbg->paint($person_img->image, 684 + $person->getX(), 8 + ($person->getY() - 70));
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
        'direction'             => 0,
        'layerbg'               => $layerbg,
        'offset_x'              => 175,
        'offset_y'              => 589,
        'right_aligned'         => true,
        'text_max_width'        => 650,
        'no_width_modification' => true,
        )
    );


    // WRITE FUNCTION
    $function_parts = explode("\n", $person->getField('funktion'));
    if (count($function_parts) > 2) {
        $function_parts = array_slice($function_parts, 0, 2);
    }

    $modifier = 0;
    foreach ($function_parts as $part) {

        paintText(array(
            'text'          => $part,
            'template'      => $template,
            'color_array'   => array('red' => 255, 'green' => 255, 'blue' => 255),
            'font'          => './fonts/calibri.ttf',
            'size'          => 46,
            'direction'     => 0,
            'layerbg'       => $layerbg,
            'offset_x'      => 844,
            'offset_y'      => 469 + $modifier,
            'right_aligned' => true,
            )
        );

        $modifier += 55;
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
        
        if (imagecopyresampled($layerbg->image, $barcode, 902, 26, 0, 0, 82, 587, 82, 544)) {
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

    $step = $_GET['step'];
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
        'x'                => -170,
        'y'                => 60,
        'template'         => $template,
        'photo_max_width'  => 319,
        'photo_max_height' => 382,
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
    return str_replace(BASE_PATH, '', $path);
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

    if ((file_exists(CONFIG_PATH) && !is_writable(CONFIG_PATH)) || !is_writable(__DIR__)) {
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
    $failed = false;
    foreach (array(TEMPLATE_PATH, CACHE_PATH, OUTPUT_PATH, INPUT_PATH, FONTS_PATH) as $path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0770)) {
                $failed = true;
            }
        }
    }

    if ((file_exists(CONFIG_PATH) && !is_writable(CONFIG_PATH))) {
        if (!chmod(CONFIG_PATH, 0770)) {
            $failed = true;
        }
    }

    if ($failed) {
        echo "<pre>
Could not create needed paths for tool to work.
Make sure that permissions are set correctly in the folder where
this tool is installed.
</pre>";
        exit;
    }
}
